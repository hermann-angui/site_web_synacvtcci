<?php

namespace App\Service\Member;

use App\Entity\Member;
use App\Entity\Payment;
use App\Helper\ActivityLogger;
use App\Helper\CsvReaderHelper;
use App\Helper\MemberAssetHelper;
use App\Helper\PasswordHelper;
use App\Helper\PdfGenerator;
use App\Repository\ChildRepository;
use App\Repository\MemberRepository;
use Clegginabox\PDFMerger\PDFMerger;
use DateTime;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

/**
 *
 */
class MemberService
{

    private const WEBSITE_URL = "https://synacvtcci.org";
    private const MEDIA_DIR = "/var/www/html/public/members/";
    private const MONTANT = 10100;
    public function __construct(
        private ContainerInterface             $container,
        private MemberCardGeneratorService     $memberCardGeneratorService,
        private MemberAssetHelper              $memberAssetHelper,
        private MemberRepository               $memberRepository,
        private ChildRepository                $childRepository,
        private UserPasswordHasherInterface    $userPasswordHasher,
        private PdfGenerator                   $pdfGenerator,
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

            $sexCode = null;
            if($member->getSex() === "H") $sexCode = "SY1";
            elseif($member->getSex() === "F") $sexCode = "SY2";
            if($sexCode){
                $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $member->getId());
                $member->setMatricule($matricule);
            }

            $expiredDate = $date->format('Y-12-31');
            $member->setSubscriptionExpireDate(new \DateTime($expiredDate));

            $member->setPassword($this->userPasswordHasher->hashPassword($member, PasswordHelper::generate()));

            if(!empty($images)) $this->storeMemberImages($member, $images);

            $member->setStatus("PENDING");
            $member->setTitre("CHAUFFEUR");

            $this->memberRepository->add($member, true);
            $member->setCountry($member->getBirthCountry());

//            foreach($member->getChildren() as $child){
//                $this->childRepository->add($child, true);
//            }

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

            if (!$member->getMatricule()) {
                $date = new DateTime('now');
                $sexCode = null;
                if($member->getSex() === "H") $sexCode = "SY1";
                elseif($member->getSex() === "F") $sexCode = "SY2";
                if($sexCode){
                    $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $member->getId());
                    $member->setMatricule($matricule);
                }
            }

            $member->setCountry($member->getBirthCountry());

            if(!empty($images)) $this->storeMemberImages($member, $images);
            $member->setTitre("CHAUFFEUR");
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
     * @param $data
     * @return Member
     */
    public function createCnmiOrUpdate(?Member $member, $data, bool $update = false)
    {
        if(!$update && !$member) $member =  new Member();

        $member->setCodeSticker(!empty($data["CodeSticker"]) ? $data["CodeSticker"] : null);
        if (!$member->getReference()) $member->setReference(str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18)));

        if (!$member->getMatricule()) {
            $member->setRoles(['ROLE_USER']);
            $date = new \DateTime('now');
            $member->setSubscriptionDate($date);

            if (!$member->getMatricule()) {
                $date = new DateTime('now');
                $sexCode = null;
                if($member->getSex() === "H") $sexCode = "SY1";
                elseif($member->getSex() === "F") $sexCode = "SY2";
                if($sexCode){
                    $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $member->getId());
                    $member->setMatricule($matricule);
                }
            }
            $expiredDate = $date->format('Y-12-31');
            $member->setSubscriptionExpireDate(new \DateTime($expiredDate));
            $member->setPassword($this->userPasswordHasher->hashPassword($member, PasswordHelper::generate()));
        }

        if (!empty($data["member_registration_photo"])) {
            $fileName = $this->memberAssetHelper->uploadAsset($data["member_registration_photo"], $member->getReference());
            if ($fileName) $member->setPhoto($fileName);
        }
        if (!empty($data["lastName"]) && $member->getLastName() !== $data["lastName"]) {
            $member->setLastName($data["lastName"]);
        }
        if (!empty($data["firstName"]) && $member->getFirstName() !== $data["firstName"]) {
            $member->setFirstName($data["firstName"]);
        }
        if (!empty($data["birthCity"]) && $member->getBirthCity() !== $data["birthCity"]) {
            $member->setBirthCity($data["birthCity"]);
        }
        if (!empty($data["nationality"]) && $member->getNationality() !== $data["nationality"]) {
            $member->setNationality($data["nationality"]);
        }
        if (!empty($data["sex"]) && $member->getSex() !== $data["sex"]) {
            $member->setSex($data["sex"]);
        }
        if (!empty($data["commune"]) && $member->getCity() !== $data["commune"]) {
            $member->setCommune($data["commune"]);
        }
        if (!empty($data["idType"]) && $member->getIdType() !== strtoupper($data["idType"])) {
            $member->setIdType($data["idType"]);
        }
        if (!empty($data["dateOfBirth"]) && $member->getDateOfBirth()->format("d/m/Y") != $data["dateOfBirth"]) {
            $member->setDateOfBirth(DateTime::createFromFormat('d/m/Y', $data["dateOfBirth"]));
        }
        if (!empty($data["idNumber"]) && $member->getIdNumber() !== $data["idNumber"]) {
            $member->setIdNumber($data["idNumber"]);
        }
        if (!empty($data["idDeliveryPlace"]) && $member->getIdDeliveryPlace() !== $data["idDeliveryPlace"]) {
            $member->setIdDeliveryPlace($data["idDeliveryPlace"]);
        }
        if (!empty($data["idDeliveryDate"]) && $member->getIdDeliveryDate()->format("d/m/Y") !== $data["idDeliveryDate"]) {
            $member->setIdDeliveryDate(DateTime::createFromFormat('d/m/Y', $data["idDeliveryDate"]));
        }
        if (!empty($data["etatCivil"]) && $member->getEtatCivil() !== $data["etatCivil"]) {
            $member->setEtatCivil($data["etatCivil"]);
        }
        if (!empty($data["mobile"]) && $member->getMobile() !== $data["mobile"]) {
            $member->setMobile($data["mobile"]);
        }
        if (!empty($data["email"]) && $member->getEmail() !== $data["email"]) {
            $member->setEmail($data["email"]);
        }

       // $this->saveMember($member);

        /*************STORE IMAGE FILES*********************/
        $this->storeMemberImages($member, $data);

        $this->saveMember($member);

        return $member;
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
                $file = $this->getMemberDir($member) . $member->getCardPhoto();
                if(file_exists($file))  unlink($file);
            }
            $cardImage = $this->memberCardGeneratorService->generate($member);
            $member->setCardPhoto($cardImage->getFilename());
            $member->setModifiedAt(new DateTime());
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
                $file = $this->getMemberDir($member) . $member->getPhoto();
                if(is_file($file)) {
                    $zipArchive->addFile($file, $member->getReference() . '_photo.png');
                }
                $file = $this->getMemberDir($member) . $member->getCardPhoto();
                if(is_file($file)) {
                    $zipArchive->addFile($file, $member->getReference() . '_card.png');
                }

                $barCodePhotoRealPath = $this->getMemberDir($member) . $member->getReference() . "_barcode.png";
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
            "TITRE",
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
                    if (isset($row["TITRE"])) $member->setTitre(mb_strtoupper(trim($row["TITRE"])));

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
            if ($fileName) $member->setPhoto($fileName->getFilename());
        }

        if (isset($images['photoPieceFront'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['photoPieceFront'], $member->getReference());
            if ($fileName) $member->setPhotoPieceFront($fileName->getFilename());
        }

        if (isset($images['photoPieceBack'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['photoPieceBack'], $member->getReference());
            if ($fileName) $member->setPhotoPieceBack($fileName->getFilename());
        }

        if (isset($images['photoPermisFront'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['photoPermisFront'], $member->getReference());
            if ($fileName) $member->setPhotoPermisFront($fileName->getFilename());
        }

        if (isset($images['photoPermisBack'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['photoPermisBack'], $member->getReference());
            if ($fileName) $member->setPhotoPermisBack($fileName->getFilename());
        }

        if (isset($images['paymentReceiptCnmciPdf'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['paymentReceiptCnmciPdf'], $member->getReference());
            if ($fileName) $member->setPaymentReceiptCnmciPdf($fileName->getFilename());
        }
        if (isset($images['paymentReceiptSynacvtcciPdf'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['paymentReceiptSynacvtcciPdf'], $member->getReference());
            if ($fileName) $member->setPaymentReceiptCnmciPdf($fileName->getFilename());
        }

        if (isset($images['scanDocumentIdentitePdf'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['scanDocumentIdentitePdf'], $member->getReference());
            if ($fileName) $member->setScanDocumentIdentitePdf($fileName->getFilename());
        }

        if (isset($images['mergedDocumentsPdf'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['mergedDocumentsPdf'], $member->getReference());
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
     * @param string $viewTemplate
     * @return PdfResponse
     */
    public function downloadCNMCIPdf(?Member $member, string $viewTemplate){
        set_time_limit(0);
        $content = $this->generateCNMCIPdf($member, $viewTemplate);
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
            $file = $this->getMemberDir($member) . time() . uniqid() . ".pdf";
            $member->setFormulaireCnmciPdf(basename($file));
            $this->saveMember($member);
            file_put_contents($file, $content);
            return $content ?? null;
        }catch(\Exception $e){
            return null;
        }
    }

    /**
     * @param File|null $file
     * @param Member $member
     * @param $width
     * @param $height
     * @return void
     */
    public function createThumbnail(?File $file, Member $member, $width, $height){
        $this->memberAssetHelper->createThumbnail($file,  $member->getReference(), $width, $height);
    }


    /**
     * @param Member $member
     * @return string
     */
    public function getMemberDir(Member $member){
        return $this->container->getParameter('kernel.project_dir') . "/public/members/" . $member->getReference() . "/";
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
    public function generateRegistrationReceipt(?Member $member)
    {
        try {
           // $qrCodeData = self::WEBSITE_URL . "/admin/member/" . $member->getId();
            $qrCodeData = self::WEBSITE_URL . "/profile/" . $member->getMatricule();
            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            $folder = self::MEDIA_DIR . $member->getReference() . '/';
            if(!file_exists($folder)) mkdir($folder, 0777, true);

            $barcode_file = $folder . "_barcode.png";
            file_put_contents($barcode_file, $content);

            $viewTemplate = 'admin/member/receipt-pdf.html.twig';
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

    public function combinePdfsForPrint(Member $member, $excludeReceipt = false, $outputmode = 'browser'){
        $pdf = new PDFMerger;

        $folder = $this->getMemberDir($member);

        if(!$member->getFormulaireCnmciPdf()) {
            $this->generateCNMCIPdf($member, "admin/pdf/cnmci.html.twig");
        }
        $pdf->addPDF($folder . $member->getFormulaireCnmciPdf());

        if($member->getPaymentReceiptCnmciPdf()) {
            $pdf->addPDF($folder . $member->getPaymentReceiptCnmciPdf());
        }

        if(!$excludeReceipt){
            if($member->getPaymentReceiptSynacvtcciPdf()) {
                $pdf->addPDF($folder . $member->getPaymentReceiptSynacvtcciPdf());
            }
        }


        if($member->getScanDocumentIdentitePdf()) {
            $pdf->addPDF($folder . $member->getScanDocumentIdentitePdf());
        }
//        if($member->getOnlineRegistrationReceiptPdf()) {
//            $pdf->addPDF($folder . $member->getOnlineRegistrationReceiptPdf());
//        }

        $output = $folder . time() . uniqid() . ".pdf";
        $member->setMergedDocumentsPdf(basename($output));
        $member->setStatus("COMPLETED");
        $this->saveMember($member);
        $res = $pdf->merge($outputmode, uniqid() . '.pdf');
        return $output;
    }


    public function archiveMemberDocuments(array $members): ?string
    {
        $zipArchive = new \ZipArchive();
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/cnmci/download.zip';;
        if(file_exists($zipFile)) \unlink($zipFile);
        $isOpen = $zipArchive->open($zipFile, \ZipArchive::CREATE);

        if($isOpen === true)
        {
            $fileInscrits = $this->container->getParameter('kernel.project_dir') . '/public/cnmci/inscrits.xls';
            if(file_exists($fileInscrits)) {
                $zipArchive->addFile($fileInscrits, 'inscrits.xls');
            }
            /** @var Member $member **/
            foreach($members as $member)
            {
                if(is_file($this->getMemberDir($member) . $member->getPhoto())) {
                    $info = new SplFileInfo($this->getMemberDir($member) . $member->getPhoto());
                    $outputFile = $member->getReference() . '_'  . $member->getLastName() . ' ' . $member->getFirstName() . '.' . $info->getExtension();
                    $zipArchive->addFile($this->getMemberDir($member) . $member->getPhoto(), $outputFile);
                }

                if(is_file($this->getMemberDir($member) . $member->getMergedDocumentsPdf())) {
                    $info = new SplFileInfo($this->getMemberDir($member) . $member->getMergedDocumentsPdf());
                    $outputFile = $member->getReference() . '_'  . $member->getLastName() . ' ' . $member->getFirstName() . '.' . $info->getExtension();
                    $zipArchive->addFile($this->getMemberDir($member) . $member->getMergedDocumentsPdf(), $outputFile);
                }
            }
            $zipArchive->close();
            return $zipFile;
        }
        return null;
    }


}

