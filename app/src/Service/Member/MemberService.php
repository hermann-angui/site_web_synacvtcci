<?php

namespace App\Service\Member;

use App\Entity\Member;
use App\Entity\Payment;
use App\Helper\ActivityLogger;
use App\Helper\CsvReaderHelper;
use App\Helper\FileHelper;
use App\Helper\MemberAssetHelper;
use App\Helper\PasswordHelper;
use App\Helper\PdfGenerator;
use App\Repository\ChildRepository;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use App\Service\ConfigurationService\ConfigurationService;
use App\Service\Payment\PaymentService;
use Clegginabox\PDFMerger\PDFMerger;
use DateTime;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

/**
 *
 */
class MemberService
{
    /**
     * @param ContainerInterface $container
     * @param MemberCardGeneratorService $memberCardGeneratorService
     * @param MemberAssetHelper $memberAssetHelper
     * @param ChildRepository $childRepository
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param PdfGenerator $pdfGenerator
     * @param ConfigurationService $configurationService
     * @param ActivityLogger $activityLogger
     * @param CsvReaderHelper $csvReaderHelper
     */
    public function __construct(
        private ContainerInterface             $container,
        private MemberCardGeneratorService     $memberCardGeneratorService,
        private MemberAssetHelper              $memberAssetHelper,
        private MemberRepository               $memberRepository,
        private PaymentService                 $paymentService,
        private ChildRepository                $childRepository,
        private UserPasswordHasherInterface    $userPasswordHasher,
        private PdfGenerator                   $pdfGenerator,
        private ConfigurationService           $configurationService,
        private ActivityLogger                 $activityLogger,
        private CsvReaderHelper                $csvReaderHelper)
    {
    }

    /**
     * @param Member $member
     * @return void
     * @throws \Exception
     */
    public function createMember(Member $member, array $images): ?Member
    {
        try {
            date_default_timezone_set("Africa/Abidjan");

            $this->memberRepository->add($member, true);
            $member->setRoles(['ROLE_USER']);
            $date = new DateTime('now');

            $member->setSubscriptionDate($date);
            if (!$member->getReference()) {
                $member->setReference(
                    str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18))
                );
            }

            $expiredDate = $date->format('Y-12-31');
            $member->setSubscriptionExpireDate(new \DateTime($expiredDate));

            $member->setPassword($this->userPasswordHasher->hashPassword($member, PasswordHelper::generate()));

            if(!empty($images)) $this->storeMemberImages($member, $images);

            $this->memberRepository->add($member, true);
            $member->setCountry($member->getBirthCountry());

            $this->memberRepository->add($member, true);
            return $member;

        }catch(\Exception $e){
            return null;
        }
    }

    /**
     * @param Member $member
     * @return void
     * @throws \Exception
     */
    public function updateMember(Member $member, array $images): ?Member
    {
        try {
            date_default_timezone_set("Africa/Abidjan");
            if (!$member->getReference()) {
                $member->setReference(
                    str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18))
                );
            }

            $member->setCountry($member->getBirthCountry());

            if(!empty($images)) $this->storeMemberImages($member, $images);
            $this->saveMember($member);

            if($children = $member->getChildren()){
                foreach($children as $child){
                    $this->childRepository->add($child, true);
                }
            }

            $this->saveMember($member);
            return $member;

        }catch(\Exception $e){
            return $member;
        }
    }

    /**
     * @param Member|null $member
     * @return void
     */
    public function deleteMember(?Member $member): void
    {
          $this->memberRepository->remove($member, true);
    }

    /**
     * @return Member[]
     */
    public function getAllMembers(){
        return $this->memberRepository->findAll();
    }

    /**
     * @param Member|null $member
     * @return Member|null
     */
    public function generateSingleMemberCard(?Member $member): ?Member
    {
        date_default_timezone_set("Africa/Abidjan");
        if ($member) {
            if(empty($member->getPhoto())) return null;
            if($member->getCardPhoto()){
                $file = $this->memberAssetHelper->getMemberDir($member) . $member->getCardPhoto();
                if(file_exists($file))  unlink($file);
            }
            $cardImage = $this->memberCardGeneratorService->generateCardSynacvtcci($member);
            $member->setCardPhoto($cardImage->getFilename());
            $member->setModifiedAt(new DateTime());
            $this->memberRepository->add($member, true);
            return $member;
        }
        return null;
    }

    /**
     * @param Member|null $member
     * @return Member|null
     */
    public function generateSingleCnmciCard(?Member $member): ?Member
    {
        date_default_timezone_set("Africa/Abidjan");
        if ($member) {
            $member = $this->memberCardGeneratorService->generateCardCnmci($member);
            $this->memberRepository->add($member, true);
            return $member;
        }
        return null;
    }

    /**
     * @return array
     */
    public function generateMultipleMemberCards(array $matricules = []): array
    {
        date_default_timezone_set("Africa/Abidjan");
        $members = [];
        if(empty($matricules)){
            $members = $this->memberRepository->findBy(['has_paid_for_syndicat' => 1]);
        }else{
            $members = $this->memberRepository->findBy(["matricule" => $matricules, 'has_paid_for_syndicat' => 1]);
        }

        foreach ($members as $member) {
            $this->generateSingleMemberCard($member);
        }
        return $members;
    }

    /**
     * @param array $members
     * @return string|null
     */
    public function archiveMemberCards(array $members): ?string
    {
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(0);
        $zipArchive = new \ZipArchive();
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/members/tmp_members.zip';
        if(file_exists($zipFile)) unlink($zipFile);
        if($zipArchive->open($zipFile, \ZipArchive::CREATE) === true)
        {
            /**@var Member $member **/
            foreach($members as $member)
            {
                $file = $this->memberAssetHelper->getMemberDir($member) . $member->getPhoto();
                if(is_file($file)) {
                    $zipArchive->addFile($file, $member->getReference() . '_photo.png');
                }
                $file = $this->memberAssetHelper->getMemberDir($member) . $member->getCardPhoto();
                if(is_file($file)) {
                    $zipArchive->addFile($file, $member->getReference() . '_card.png');
                }

                $barCodePhotoRealPath = $this->memberAssetHelper->getMemberDir($member) . $member->getReference() . "_barcode.png";
                if(is_file($barCodePhotoRealPath)) {
                    $zipArchive->addFile($barCodePhotoRealPath, $member->getReference() . '_barcode.png');
                }
            }
            $zipArchive->close();
            return $zipFile;
        }
        return null;
    }

    /**
     * @return void
     */
    public function getMemberCardsList(){
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/members/tmp/members.zip';
         if(!file_exists($zipFile)){
             $this->generateMultipleMemberCards();
         }
    }

    /**
     * @return string
     */
    public function generateSampleCsvFile()
    {
        date_default_timezone_set("Africa/Abidjan");
        $sampleRealPath = $this->container->getParameter('kernel.project_dir') . "/public/assets/files/sample.csv";
        $columns = [
            "MATRICULE",
            "NOM",
            "PRENOMS",
            "PHOTO",
            "SEXE",
            "EMAIL",
            "WHATSAPP",
            "COMPAGNIE",
            "DATE_NAISSANCE",
            "LIEU_NAISSANCE",
            "NUMERO_PERMIS",
            "NUMERO_PIECE",
            "TYPE_PIECE",
            "PAYS",
            "VILLE",
            "COMMUNE",
            "MOBILE",
            "FIXE",
            "QUARTIER",
            "DATE_SOUSCRIPTION",
            "DATE_EXPIRATION_SOUSCRIPTION",
            "PHOTO_PIECE_RECTO",
            "PHOTO_PIECE_VERSO",
            "PHOTO_PERMIS_RECTO",
            "PHOTO_PERMIS_VERSO"
        ];
        $fp = fopen($sampleRealPath, "w+");
        fputcsv($fp, $columns);
        fputcsv($fp, []);
        fclose($fp);
        return $sampleRealPath;
    }

    /**
     * @return void
     */
    public function createMemberFromFile(): void
    {
        set_time_limit(3600);
        $finder = new Finder();
        $uploadDir = $this->container->getParameter('kernel.project_dir') . '/public/uploads/';
        $csvFiles = $finder->in($uploadDir)->name(['*.csv','*.jpg', '*.jpeg','*.png','*.gif']);
        $fs = new Filesystem();
        // remove file after import
        foreach($csvFiles as $file) {
            $rows =  $this->csvReaderHelper->read($file);
            $this->memberRepository->setAutoIncrementToLast($this->memberRepository->getLastRowId());
            foreach ($rows as $row){
                try{
                    date_default_timezone_set("Africa/Abidjan");
                    $date = new DateTime('now');

                    $sexCode = "SY1";
                    if (!empty($row["SEXE"])) {
                        if ($row["SEXE"] === "M") $sexCode = "SY1";
                        if ($row["SEXE"] === "F") $sexCode = "SY2";
                    } else {
                        throw new \Exception("Skip"); // Unable to determine sex so skip because it is not possible to generate matricule
                    }

                    $member = new Member();

                    $member->setRoles(['ROLE_USER']);
                    if (isset($row["SEXE"])) $member->setSex(mb_strtoupper($row["SEXE"], 'UTF-8'));
                    if (isset($row["EMAIL"])) $member->setEmail(trim($row["EMAIL"]));
                    if (isset($row["NOM"])) $member->setLastName(mb_strtoupper(trim($row["NOM"]), 'UTF-8'));
                    if (isset($row["COMPAGNIE"])) $member->setCompany($row["COMPAGNIE"]);
                    if (isset($row["NATIONALITE"])) $member->setLastName(mb_strtoupper(trim($row["NATIONALITE"]), 'UTF-8'));
                    if (isset($row["PRENOMS"])) $member->setFirstName(mb_strtoupper(trim($row["PRENOMS"]), 'UTF-8'));
                    if (isset($row["DATE_NAISSANCE"])) $member->setDateOfBirth(new DateTime($row["DATE_NAISSANCE"]));
                    if (isset($row["LIEU_NAISSANCE"])) $member->setBirthCity(mb_strtoupper(trim($row["LIEU_NAISSANCE"])));
                    if (isset($row["NUMERO_PERMIS"])) $member->setDrivingLicenseNumber($row["NUMERO_PERMIS"]);
                    if (isset($row["NUMERO_PIECE"])) $member->setIdNumber($row["NUMERO_PIECE"]);
                    if (isset($row["TYPE_PIECE"])) $member->setIdType(mb_strtoupper(trim($row["TYPE_PIECE"])));
                    if (isset($row["PAYS"])) $member->setCountry(mb_strtoupper(trim($row["PAYS"])));
                    if (isset($row["VILLE"])) $member->setCity(mb_strtoupper($row["VILLE"], 'UTF-8'));
                    if (isset($row["COMMUNE"])) $member->setCommune(mb_strtoupper($row["COMMUNE"], 'UTF-8'));
                    if (isset($row["MOBILE"])) $member->setMobile($row["MOBILE"]);
                    if (isset($row["FIXE"])) $member->setPhone($row["FIXE"]);

                    $member->setPassword($this->userPasswordHasher->hashPassword($member, PasswordHelper::generate()));

                    if (array_key_exists("DATE_SOUSCRIPTION", $row)) {
                        if (empty($row["DATE_SOUSCRIPTION"])) $member->setSubscriptionDate($date);
                        else $member->setSubscriptionDate(new DateTime($row["DATE_SOUSCRIPTION"]));
                    }

                    if (array_key_exists("DATE_EXPIRATION_SOUSCRIPTION", $row)) {
                        $expiredDate = new DateTime($row["DATE_SOUSCRIPTION"]);
                        //   $expiredDate = $expiredDate->add(new \DateInterval("P1Y"));
                        $expiredDate = $expiredDate->format('Y-12-31');
                        if (!empty($row["DATE_EXPIRATION_SOUSCRIPTION"])) $member->setSubscriptionExpireDate(new DateTime($row["DATE_EXPIRATION_SOUSCRIPTION"]));
                        else $member->setSubscriptionExpireDate(new DateTime($expiredDate));
                    }

                    $this->memberRepository->add($member, true);

                    $exist = null;
                    if (array_key_exists("MATRICULE", $row)) {
                        $matricule = $row["MATRICULE"];
                        if (!empty($matricule)) $member->setMatricule($matricule);
                        else {
                            $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $member->getId());
                            $member->setMatricule($matricule);
                        }
                        $exist = $this->memberRepository->findOneBy(['matricule' => $matricule]);
                    }

                    if (!$exist) {
                        $this->storeAsset($row["PHOTO"], $uploadDir, $member);
                        $this->storeAsset($row["PHOTO_PIECE_RECTO"], $uploadDir, $member);
                        $this->storeAsset($row["PHOTO_PIECE_VERSO"], $uploadDir, $member);
                        $this->storeAsset($row["PHOTO_PERMIS_RECTO"], $uploadDir, $member);
                        $this->storeAsset($row["PHOTO_PERMIS_VERSO"], $uploadDir, $member);
                        $this->memberRepository->add($member, true);
                    } else {
                        $this->memberRepository->remove($member, true);
                    }
                }
                catch(\Exception $e){
                    continue;
                }
            }
        }
        $fs->remove($csvFiles);
    }

    /**
     * @param Member $member
     * @return void
     */
    public function storeMemberImages(Member $member, $images): Member
    {
        if (isset($images['photo'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['photo'], $member->getReference());
            $existingFile = $member->getPhoto() ? $this->getMemberDir($member) . $member->getPhoto(): null;
            FileHelper::deleteExistingFile($existingFile);
            if ($fileName) $member->setPhoto($fileName->getFilename());
        }

        if (isset($images['photoPieceFront'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['photoPieceFront'], $member->getReference());
            $existingFile = $member->getPhotoPieceFront() ? $this->getMemberDir($member) . $member->getPhotoPieceFront(): null;
            if(file_exists($existingFile)) unlink($existingFile);
            if ($fileName) $member->setPhotoPieceFront($fileName->getFilename());
        }

        if (isset($images['photoPieceBack'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['photoPieceBack'], $member->getReference());
            $existingFile = $member->getPhotoPieceBack() ? $this->getMemberDir($member) . $member->getPhotoPermisFront(): null;
            if(file_exists($existingFile)) unlink($existingFile);
            if ($fileName) $member->setPhotoPieceBack($fileName->getFilename());
        }

        if (isset($images['photoPermisFront'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['photoPermisFront'], $member->getReference());
            $existingFile = $member->getPhotoPermisFront() ? $this->getMemberDir($member) . $member->getPhotoPermisFront(): null;
            if(file_exists($existingFile)) unlink($existingFile);
            if ($fileName) $member->setPhotoPermisFront($fileName->getFilename());
        }

        if (isset($images['photoPermisBack'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['photoPermisBack'], $member->getReference());
            $existingFile = $member->getPhotoPermisBack() ? $this->getMemberDir($member) . $member->getPhotoPermisBack(): null;
            if(file_exists($existingFile)) unlink($existingFile);
            if ($fileName) $member->setPhotoPermisBack($fileName->getFilename());
        }

        if (isset($images['paymentReceiptCnmciPdf'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['paymentReceiptCnmciPdf'], $member->getReference());
            if ($fileName) {
                $existingFile = $member->getPaymentReceiptCnmciPdf() ? $this->getMemberDir($member) . $member->getPaymentReceiptCnmciPdf(): null;
                if(file_exists($existingFile)) unlink($existingFile);
                $member->setPaymentReceiptCnmciPdf($fileName->getFilename());
            }
        }
        if (isset($images['paymentReceiptSyndicatPdf'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['paymentReceiptSyndicatPdf'], $member->getReference());
            $existingFile = $member->getPaymentReceiptCnmciPdf() ? $this->getMemberDir($member) . $member->getPaymentReceiptCnmciPdf(): null;
            if(file_exists($existingFile)) unlink($existingFile);
            if ($fileName) $member->setPaymentReceiptCnmciPdf($fileName->getFilename());
        }

        if (isset($images['scanDocumentIdentitePdf'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['scanDocumentIdentitePdf'], $member->getReference());
            $existingFile = $member->getScanDocumentIdentitePdf() ? $this->getMemberDir($member) . $member->getScanDocumentIdentitePdf(): null;
            if(file_exists($existingFile)) unlink($existingFile);
            if ($fileName) $member->setScanDocumentIdentitePdf($fileName->getFilename());
        }

        if (isset($images['mergedDocumentsPdf'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['mergedDocumentsPdf'], $member->getReference());
            $existingFile = $member->getMergedDocumentsPdf() ? $this->getMemberDir($member) . $member->getMergedDocumentsPdf(): null;
            if(file_exists($existingFile)) unlink($existingFile);
            if ($fileName) $member->setMergedDocumentsPdf($fileName->getFilename());
        }

        return $member;
    }

    /**
     * @param $row
     * @param string $uploadDir
     * @param Member $member
     * @return void
     */
    public function storeAsset($row, string $uploadDir, Member $member): void
    {
        if (isset($row) && !empty($row)) {
            $photo = new File($uploadDir . $row, false);
            if (file_exists($photo->getPathname())) {
                $fileName = $this->memberAssetHelper->uploadAsset($photo, $member->getReference());
                if ($fileName) $member->setPhoto($fileName);
            }
        }
    }

    /**
     * @param Member|null $payment
     * @return PdfResponse
     */
    public function downloadCNMCIPdf(?Member $member){
        set_time_limit(0);
       // $content = $this->generateCNMCIPdf($member, "admin/pdf/cnmci.html.twig");
        $content = $this->generateCNMCIPdf($member, "pdf/cnmci.html.twig");
        return new PdfResponse($content, 'fiche_cnmci.pdf');
    }

    /**
     * @param Member|null $member
     * @param string $viewTemplate
     * @return string|null
     */
    public function generateCNMCIPdf(?Member $member, string $viewTemplate)
    {
        try {
            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['member' => $member]);
            $file = $this->memberAssetHelper->getMemberDir($member) . time() . uniqid() . ".pdf";
            $member->setFormulaireCnmciPdf(basename($file));
            $this->saveMember($member);
            file_put_contents($file, $content);
            return $content ?? null;
        }catch(\Exception $e){
            return null;
        }
    }

    /**
     * @param Member $member
     * @param $width
     * @param $height
     * @return void
     */
    public function createThumbnail(Member $member, $width, $height){
        $this->memberAssetHelper->createThumbnail($member->getPhoto(),  $member->getReference(), $width, $height);
    }

    /**
     * @return void
     */
    public function generateAllPhotoThumbnails(){
        $members = $this->memberRepository->findAll();
        foreach($members as $member){
            FileHelper::deleteExistingFile($this->memberAssetHelper->getMemberDir($member) . $member->getPhoto());
            $this->createThumbnail($member, 128, 128);
        }
    }

    /**
     * @param Member $member
     * @return void
     */
    public function saveMember(?Member $member){
        if(!$member)  return;
        $this->memberRepository->add($member, true);
    }

    /**
     * @param Payment|null $payment
     * @param string $viewTemplate
     * @return string|null
     */
    public function generateOnlineRegistrationReceipt(?Member $member)
    {
        try {
            $qrCodeData = $this->configurationService->getParameter('app.base_url') . "/profile/" . $member->getMatricule();
            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            $folder = $this->memberAssetHelper->getMemberDir();
            if(!file_exists($folder)) mkdir($folder, 0777, true);

            $barcode_file = $folder . "_barcode.png";
            file_put_contents($barcode_file, $content);

            $viewTemplate = 'admin/artisan/online-receipt-pdf.html.twig';
            $receipt_file = $folder . time() . uniqid() . ".pdf";
            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['member' => $member]);
            file_put_contents($receipt_file, $content);

            if(file_exists($barcode_file)) \unlink($barcode_file);

            $member->setOnlineRegistrationReceiptPdf(basename($receipt_file));
            $this->memberRepository->add($member, true);

            return $content ?? null;

        }catch(\Exception $e){
            if(file_exists($folder . "_barcode.png")) \unlink($folder . "_barcode.png");
            if(file_exists($folder . "_receipt.pdf")) \unlink($folder . "_receipt.pdf");
        }
    }

    /**
     * @param Member $member
     * @param $excludeReceipt
     * @param $outputmode
     * @return string
     * @throws \Exception
     */
    public function combinePdfsForPrint(Member $member, $excludeReceipt = false, $outputmode = 'browser'){
        $pdf = new PDFMerger;
        $folder = $this->memberAssetHelper->getMemberDir($member);

        if(!$member->getFormulaireCnmciPdf()) {
           // $this->generateCNMCIPdf($member, "admin/pdf/cnmci.html.twig");
            $this->generateCNMCIPdf($member, "pdf/cnmci.html.twig");
        }
        if(is_file($folder . $member->getFormulaireCnmciPdf())) $pdf->addPDF($folder . $member->getFormulaireCnmciPdf());

        if($member->getPaymentReceiptCnmciPdf() && is_file($folder . $member->getPaymentReceiptCnmciPdf())) {
            $pdf->addPDF($folder . $member->getPaymentReceiptCnmciPdf());
        }

        if(!$excludeReceipt) {
            $filePath = $folder . $member->getPaymentReceiptServiceTechniquePdf();
            if(!is_file($filePath)){
                $payment = $this->paymentService->findMemberPaymentByTarget($member, "FRAIS_SERVICE_TECHNIQUE");
                $this->paymentService->generatePaymentReceipt($payment);
            }else{
                $pdf->addPDF($filePath);
            }
        }

        if($member->getScanDocumentIdentitePdf() && is_file($folder . $member->getScanDocumentIdentitePdf())) {
            $pdf->addPDF($folder . $member->getScanDocumentIdentitePdf());
        }
//        if($member->getOnlineRegistrationReceiptPdf()) {
//            $pdf->addPDF($folder . $member->getOnlineRegistrationReceiptPdf());
//        }

        $output = $folder . time() . uniqid() . ".pdf";
        $member->setMergedDocumentsPdf(basename($output));
        $member->setEtape(4);
        $this->saveMember($member);
        $res = $pdf->merge($outputmode, uniqid() . '.pdf');
        return $output;
    }

    /**
     * @param array $members
     * @param string $file
     * @return string|null
     */
    public function archiveMemberDocuments(array $members, string $file): ?string
    {
        $zipArchive = new \ZipArchive();

        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/cnmci/download.zip';;
        if(file_exists($zipFile)) \unlink($zipFile);

        if($zipArchive->open($zipFile, \ZipArchive::CREATE)) {

            if(file_exists($file)) $zipArchive->addFile($file, 'inscrits.xls');

            /** @var Member $member **/
            foreach($members as $member) {
                $dir = $this->memberAssetHelper->getMemberDir($member);
                $outputFile = $member->getReference() . '_'  . $member->getLastName() . ' ' . $member->getFirstName() . '.' ;
                if(is_file( $dir . $member->getPhoto())) {
                    $info = new SplFileInfo($dir . $member->getPhoto());
                    $zipArchive->addFile($dir. $member->getPhoto(), $outputFile . $info->getExtension());
                }

                if(is_file($dir . $member->getMergedDocumentsPdf())) {
                    $info = new SplFileInfo($dir . $member->getMergedDocumentsPdf());
                    $zipArchive->addFile($dir . $member->getMergedDocumentsPdf(), $outputFile . $info->getExtension());
                }
            }
            $zipArchive->close();
            return $zipFile;
        }
        return null;
    }

    /**
     * @param string $file
     * @return string|null
     */
    public function archiveMatriceEncaissement(string $file): ?string
    {
        $zipArchive = new \ZipArchive();
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/cnmci/' . uniqid() . '.zip';
        if(file_exists($zipFile)) \unlink($zipFile);
        if($zipArchive->open($zipFile, \ZipArchive::CREATE)) {
            if(file_exists($file)) $zipArchive->addFile($file, 'matrice_encaissement.xls');
            $zipArchive->close();
            return $zipFile;
        }
        return null;
    }

    /**
     * @param Member|null $member
     * @return string|null
     */
    public static function generateMatricule(?Member $member): ?string {
        if(!$member->getActivity()) return null;
        $matricule = match($member->getActivity()){
            "CHAUFFEUR VTC" => self::createVtcMatricule($member),
            "CHAUFFEUR TAXI COMPTEUR" => self::createTaxiCompteurMatricule($member),
            "CHAUFFEUR COMMUNAL" => self::createTaxiCommunalMatricule($member),
            "CHAUFFEUR LIVREUR" => self::createLivreurMatricule($member),
            "CHAUFFEUR TRICYCLE" => self::createTricycleMatricule($member)
        };

        return $matricule;
    }

    /**
     * @param int $member_id
     * @param string $cnmci_numero_rm
     * @param string $cnmci_carte_professionelle
     * @return bool
     */
    public function validateSouscription(int $member_id, string $cnmci_numero_rm, string $cnmci_carte_professionelle)
    {
        try {
            $member = $this->memberRepository->find($member_id);
            if(!$member?->getIsPaymentValidated()) return false;
            $member->setCnmciNumeroRm($cnmci_numero_rm);
            $member->setCnmciNumeroCarteProfessionelle($cnmci_carte_professionelle);
            $member->setIsInscriptionValidated(true);
            $member->setStatus("VALIDER");
            $this->generateSingleCnmciCard($member);
            $this->memberRepository->add($member, true);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    /**
     * @param Member $member
     * @return void
     */
    public function validatePayment(Member $member) {
        $member->setIsPaymentValidated(true);
        $this->memberRepository->add($member, true);
    }

    /**
     * @param array $ids
     * @return void
     */
    public function validatePaymentBatch(array $ids) {
        $this->memberRepository->validatePaymentByIds($ids);
    }

    /**
     * @param array $ids
     * @return void
     */
    public function validateSouscriptionBatch(array $ids) {
        $this->memberRepository->validateSouscriptionByIds($ids);
    }

    /**
     * @param $members
     * @return string|null
     */
    public function generateMatriceEncaissementXlsxFile($members): ?string
    {
        try {

            $dir = $this->getCnmciDir();
            if (!file_exists($dir)) mkdir($dir, 0777, true);

            $inputFileName = $dir . "CNMCI-Matrice des encaissements.xls";
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            $spreadsheet = $reader->load($inputFileName);
            $worksheet = $spreadsheet->getSheet(0);

            $count = 1;
            $cel = 3;
            /** @var Member $member */
            foreach ($members as $member) {
                $d = [
                    $count++,
                    "Registre des metiers et Carte d Artisans",
                    '15000',
                    $member->getPaymentReceiptCnmciCode(), //$row['payment_receipt_cnmci_code']  $member->get,
                    $member->getSubscriptionDate()->format('d/m/Y'), //$row['subscription_date']->format('d/m/Y'),
                    $member->getLastName() . '' . $member->getFirstName(),
                    $member->getActivity(),
                    $member->getActivityGeoLocation(),
                    $member->getMobile(),
                    '',
                ];

                $worksheet->fromArray(
                    $d,             // The data to set
                    NULL,        // Array values with this value will not be set
                    "A" . $cel++         // Top left coordinate of the worksheet range where we want to set these values (default is A1)
                );
            }

            $spreadsheet->getActiveSheet()->setAutoFilter(
                $spreadsheet->getActiveSheet()->calculateWorksheetDimension()
            );

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $outputFileName = $dir . time() . uniqid() . ".xls";

            if (file_exists($outputFileName)) \unlink($outputFileName);
            $writer->save($outputFileName);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return $outputFileName;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return null;
    }

    /**
     * @param $members
     * @return string|null
     */
    public function generateAdherentListXlsxFile($members): ?string
    {
        try {
            $dir = $this->getCnmciDir();
            if (!file_exists($dir)) mkdir($dir, 0777, true);

            $inputFileName = $dir . "CNMCI-Matrice des inscrits.xls";

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            $spreadsheet = $reader->load($inputFileName);
            $worksheet = $spreadsheet->getSheet(0);

            $count = 1;
            $cel = 3;
            /** @var Member $member * */
            foreach ($members as $member) {
                try {
                    $d = [
                        "N°" => $count++,
                        "EMAIL" => $member->getEmail(),
                        "NOM" => $member->getLastName(),
                        "PRENOMS" => $member->getFirstName(),
                        "ACTIVITE" => $member->getActivity(),
                        "DATE SOUSCRIPTION" => $member->getSubscriptionDate()?->format('d/m/Y'),
                        "SEXE" => $member->getSex(),
                        "PHOTO" => $member->getPhoto(),
                        "DATE DE NAISSANCE" => $member->getDateOfBirth()?->format('d/m/Y'),
                        "VILLE NAISSANCE" => $member->getBirthLocality(),
                        "N° PERMIS DE CONDUIRE" => $member->getDrivingLicenseNumber(),
                        "NUMERO PIECE D'IDENTITE" => $member->getIdNumber(),
                        "TYPE DE PIECE" => $member->getIdType(),
                        "PAYS" => $member->getCountry(),
                        "VILLE" => $member->getCity(),
                        "COMMUNE" => $member->getCommune(),
                        "MOBILE" => $member->getMobile(),
                        "TEL" => $member->getPhone(),
                        "PHOTO PIECE RECTO" => $member->getPhotoPieceFront(),
                        "PHOTO PIECE VERSO" => $member->getPhotoPieceBack(),
                        "PHOTO PERMIS RECTO" => $member->getPhotoPermisFront(),
                        "PHOTO PERMIS VERSO" => $member->getPhotoPermisBack(),
                        "NATIONALITE" => $member->getNationality(),
                        "QUARTIER DE RESIDENCE" => $member->getQuartier(),
                        "WHATSAPP" => $member->getWhatsapp(),
                        "ENTREPRISES" => !empty($member->getCompany()) ? implode("|", $member->getCompany()): '',
                        "NOM CONJOINT" => $member->getPartnerLastName(),
                        "PRENOMS CONJOINT" => $member->getFirstName(),
                        "LIEU DE DELIVRANCE PIECE" => $member->getIdDeliveryPlace(),
                        "DATE DE DELIVRANCE PIECE" => $member->getIdDeliveryDate()?->format('d/m/Y'),
                        "ETAT CIVIL" => $member->getEtatCivil(),
                        "REFERENCE" => $member->getReference(),
                        "PAYS DE NAISSANCE" => $member->getIdDeliveryPlace(),
                        "LOCALITE NAISSANCE" => $member->getBirthLocality(),
                        "AUTORITE DE DELIVRANCE PIECE" => $member->getIdDeliveryAuthority(),
                        "BOITE POSTALE" => $member->getPostalCode(),
                        "PAIEMENT ORANGE MONEY" => $member->getPaymentReceiptCnmciCode(),
                        "LOCALISATION GEOGRAPHIQUE DE L'ACTIVITE" => $member->getIdDeliveryPlace(),
                        "PAYS DE L'ACTIVITE" => $member->getActivityCountryLocation(),
                        "VILLE DE L'ACTIVITE" => $member->getActivityCityLocation(),
                        "QUARTIER DE L'ACTIVITE" => $member->getActivityQuartierLocation(),
                        "CATEGORIE SOCIOPROFESSIONNELLE" => $member->getSocioprofessionnelleCategory(),
                        "DATE DEBUT ACTIVITE " => $member->getActivityDateDebut()?->format('d/m/Y'),
                        "PRENOMS PERSONNE A CONTACTER" => $member->getPartnerFirstName(),
                        "NOM PERSONNE A CONTACTER" => $member->getPartnerLastName(),
                        //    "TELEPHONE PERSONNE A CONTACTER" => "",
                        //    "RECU ORANGE MONEY" => "",
                        //    "FORMULAIRE CNMCI" => "",
                        //    "DOCUMENTS" => "",
                        //    "DOCUMENTS IDENTITE" => ""
                    ];
                    $r = array_values($d);
                    $worksheet->fromArray(
                        $r,     // The data to set
                        NULL,               // Array values with this value will not be set
                        "A" . $cel++     // Top left coordinate of the worksheet range where we want to set these values (default is A1)
                    );

                } catch (\Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }
            }

            $spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $outputFileName = $dir . uniqid(). ".xls";
            if (file_exists($outputFileName)) \unlink($outputFileName);
            $writer->save($outputFileName);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return $outputFileName;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return null;
    }

    /**
     * @return string
     */
    public function getCnmciDir()
    {
        return $this->getParameter("kernel.project_dir") . "/public/cnmci/";
    }

    /**
     * @param Member|null $member
     * @return string
     */
    public function getMemberDir(?Member $member): string
    {
        return $this->memberAssetHelper->getMemberDir($member);
    }

}

