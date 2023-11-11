<?php

namespace App\Service\Member;

use App\Entity\Member;
use App\Entity\Payment;
use App\Helper\CsvReaderHelper;
use App\Helper\MemberAssetHelper;
use App\Helper\PasswordHelper;
use App\Helper\PdfGenerator;
use App\Repository\ChildRepository;
use App\Repository\MemberRepository;
use DateTime;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
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

    private const WEBSITE_URL = "https://synacvtcci.org";
    private const MEDIA_DIR = "/var/www/html/public/frontend/media/";
    private const MONTANT = 10100;
    public function __construct(
        private ContainerInterface             $container,
        private MemberCardGeneratorService     $memberCardGeneratorService,
        private MemberAssetHelper              $memberAssetHelper,
        private MemberRepository               $memberRepository,
        private ChildRepository                $childRepository,
        private UserPasswordHasherInterface    $userPasswordHasher,
        private PdfGenerator                   $pdfGenerator,
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
            $this->memberRepository->setAutoIncrementToLast($this->memberRepository->getLastRowId());
            $lastRowId = $this->memberRepository->getLastRowId();
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
            $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $lastRowId+1);
            $member->setMatricule($matricule);

            $expiredDate = $date->format('Y-12-31');
            $member->setSubscriptionExpireDate(new \DateTime($expiredDate));

            $member->setPassword($this->userPasswordHasher->hashPassword($member, PasswordHelper::generate()));

            if(!empty($images)) $this->storeMemberImages($member, $images);

            $member->setStatus("PENDING");
            $member->setTitre("Chauffeur");

            $this->memberRepository->add($member, true);

            foreach($member->getChildren() as $child){
                $this->childRepository->add($child, true);
            }

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

            if(!empty($images)) $this->storeMemberImages($member, $images);


            $member->setTitre("Chauffeur");

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

            $sexCode = null;
            if ($member->getSex() === "H") $sexCode = "SY1";
            elseif ($member->getSex() === "F") $sexCode = "SY2";

            $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $member->getId());
            $member->setMatricule($matricule);

            $expiredDate = $date->format('Y-12-31');
            $member->setSubscriptionExpireDate(new \DateTime($expiredDate));

            $member->setPassword($this->userPasswordHasher->hashPassword($member, PasswordHelper::generate()));
        }

        if (!empty($data["member_registration_photo"])) {
            $fileName = $this->memberAssetHelper->uploadAsset($data["member_registration_photo"], $member->getReference());
            if ($fileName) $member->setPhoto($fileName);
        }

        $this->saveMember($member);

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
            $cardImage = $this->memberCardGeneratorService->generate($member);
            $member->setCardPhoto(new File($cardImage));
            $member->setModifiedAt(new DateTime());
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
            $members = $this->memberRepository->findAll();
        }else{
            $members = $this->memberRepository->findBy(["matricule" => $matricules]);
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
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/members/tmp/members.zip';;
        if(file_exists($zipFile)) unlink($zipFile);
        if($zipArchive->open($zipFile, \ZipArchive::CREATE) === true)
        {
            /**@var Member $member **/
            foreach($members as $member)
            {
                if(is_file($member->getPhoto())) {
                    $zipArchive->addFile($this->getMemberDir($member) . $member->getPhoto(), $member->getReference() . '_photo.png');
                }

                if(is_file($member->getCardPhoto())) {
                    $zipArchive->addFile($this->getMemberDir($member) . $member->getCardPhoto(), $member->getReference() . '_card.png');
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
                    if (isset($row["COMPAGNIE"])) $member->setCompany(mb_strtoupper(trim($row["COMPAGNIE"]), 'UTF-8'));
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

        if (isset($images['paymentReceiptCnmci'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['paymentReceiptCnmci'], $member->getReference());
            if ($fileName) $member->setPaymentReceiptCnmci($fileName->getFilename());
        }
        if (isset($images['paymentReceiptSynacvtcci'])) {
            $fileName = $this->memberAssetHelper->uploadAsset($images['paymentReceiptSynacvtcci'], $member->getReference());
            if ($fileName) $member->setPaymentReceiptCnmci($fileName->getFilename());
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
        return new PdfResponse($content, 'recu_macaron.pdf');
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
            file_put_contents($this->getMemberDir($member) . "cnmci.pdf", $content);
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
            $qrCodeData = self::WEBSITE_URL . "/admin/member/" . $member->getId();
            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            $folder = self::MEDIA_DIR . $member->getReference();
            if(!file_exists(self::MEDIA_DIR)) mkdir(self::MEDIA_DIR, 0777, true);
            file_put_contents( $folder . "_barcode.png", $content);
            $viewTemplate = 'frontend/member/receipt-pdf.html.twig';
            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['member' => $member]);
            file_put_contents($folder . "_receipt.pdf", $content);
            if(file_exists($folder . "_barcode.png")) \unlink($folder . "_barcode.png");
            return $content ?? null;

        }catch(\Exception $e){
            if(file_exists($folder . "_barcode.png")) \unlink($folder . "_barcode.png");
            if(file_exists($folder . "_receipt.pdf")) \unlink($folder . "_receipt.pdf");
            return $e->getTraceAsString();
        }
    }

}

