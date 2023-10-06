<?php

namespace App\Service\Member;

use App\DTO\MemberRequestDto;
use App\Entity\Child;
use App\Entity\Member;
use App\Helper\CsvReaderHelper;
use App\Helper\MemberAssetHelper;
use App\Helper\PasswordHelper;
use App\Helper\PdfGenerator;
use App\Mapper\ChildMapper;
use App\Mapper\MemberMapper;
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

class MemberService
{
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
    public function createMember(Member $member): void
    {
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

        $this->saveMemberImages(MemberMapper::MapToMemberRequestDto($member));

        $this->memberRepository->add($member, true);

        foreach($member->getChildren() as $childDto){
            $this->childRepository->add(ChildMapper::MapToChild($member, $childDto), true);
        }

    }


    /**
     * @param MemberRequestDto $memberRequestDto
     * @return void
     * @throws \Exception
     */
    public function createMemberFromDto(MemberRequestDto $memberRequestDto, bool $skipMatricule = false): void
    {
        date_default_timezone_set("Africa/Abidjan");

        $memberRequestDto->setRoles(['ROLE_USER']);
        $date = new DateTime('now');
        $memberRequestDto->setSubscriptionDate($date);

        if (!$memberRequestDto->getReference()) {
            $memberRequestDto->setReference(
                str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18))
            );
        }
        if(!$skipMatricule){
            $sexCode = null;
            if($memberRequestDto->getSex() === "H") $sexCode = "SY1";
            elseif($memberRequestDto->getSex() === "F") $sexCode = "SY2";

            $this->memberRepository->setAutoIncrementToLast($this->memberRepository->getLastRowId());
            $lastRowId = $this->memberRepository->getLastRowId();

            $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $lastRowId+1);
            $memberRequestDto->setMatricule($matricule);

            $expiredDate = $date->format('Y-12-31');
            $memberRequestDto->setSubscriptionExpireDate(new \DateTime($expiredDate));
        }

        $memberRequestDto->setPassword($this->userPasswordHasher->hashPassword($memberRequestDto, PasswordHelper::generate()));
        $this->saveMemberImages($memberRequestDto);
        $member = MemberMapper::MapToMember($memberRequestDto);
        $this->memberRepository->add($member, true);

        foreach($memberRequestDto->getChildren() as $childDto){
            $this->childRepository->add(ChildMapper::MapToChild($member, $childDto), true);
        }

    }

    /**
     * @param MemberRequestDto|null $memberDto
     * @return void
     */
    public function deleteMember(?MemberRequestDto $memberDto): void
    {

    }

    public function getAllMembers(){
        return $this->memberRepository->findAll();
    }

    /**
     * @param MemberRequestDto|null $memberRequestDto
     * @return MemberRequestDto|null
     */
    public function generateSingleMemberCard(?MemberRequestDto $memberRequestDto): ?MemberRequestDto
    {
        date_default_timezone_set("Africa/Abidjan");
        if ($memberRequestDto) {
            if(empty($memberRequestDto->getPhoto())) return null;
            $cardImage = $this->memberCardGeneratorService->generate($memberRequestDto);
            $memberRequestDto->setCardPhoto(new File($cardImage));
            $memberRequestDto->setModifiedAt(new DateTime());
            return $memberRequestDto;
        }
        return null;
    }

    /**
     * @return array
     */
    public function generateMultipleMemberCards(array $matricules = []): array
    {
        date_default_timezone_set("Africa/Abidjan");
        $memberDtos = [];
        if(empty($matricules)){
            $members = $this->memberRepository->findAll();
        }else{
            $members = $this->memberRepository->findBy(["matricule" => $matricules]);
        }

        foreach ($members as $member) {
            $memberDto = MemberMapper::MapToMemberRequestDto($member);
            $this->generateSingleMemberCard($memberDto);
            $memberDtos[] = $memberDto;
        }
        return $memberDtos;
    }

    /**
     * @param array $memberDtos
     * @return string|null
     */
    public function archiveMemberCards(array $memberDtos): ?string
    {
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(0);
        $zipArchive = new \ZipArchive();
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/members/tmp/members.zip';;
        if(file_exists($zipFile)) unlink($zipFile);
        if($zipArchive->open($zipFile, \ZipArchive::CREATE) === true)
        {
            /**@var MemberRequestDto $memberDto **/
            foreach($memberDtos as $memberDto)
            {
                $photoRealPath =  $memberDto->getPhoto();
                if(is_file($photoRealPath)) {
                    $zipArchive->addFile($photoRealPath->getRealPath(), $memberDto->getReference() . '_photo.png');
                }

                $cardPhotoRealPath =  $memberDto->getCardPhoto();
                if(is_file($cardPhotoRealPath)) {
                    $zipArchive->addFile($cardPhotoRealPath->getRealPath(), $memberDto->getReference() . '_card.png');
                }

                $barCodePhotoRealPath = $this->container->getParameter('kernel.project_dir') . "/public/members/" . $memberDto->getReference() . "/" . $memberDto->getReference() . "_barcode.png";
                if(is_file($barCodePhotoRealPath)) {
                    $zipArchive->addFile($barCodePhotoRealPath, $memberDto->getReference() . '_barcode.png');
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
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/members/tmp/members.zip';;
         if(!file_exists($zipFile)){
             $this->generateMultipleMemberCards();
         }
    }

    /**
     * @param Member $member
     * @return void
     */
    public function save(Member $member): void
    {
         $this->memberRepository->add($member, true);
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
     * @param MemberRequestDto $memberRequestDto
     * @return void
     */
    public function saveMemberImages(MemberRequestDto $memberRequestDto): MemberRequestDto
    {
        if ($memberRequestDto->getPhoto()) {
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhoto(), $memberRequestDto->getReference());
            if ($fileName) $memberRequestDto->setPhoto($fileName);
        }

        if ($memberRequestDto->getPhotoPieceFront()) {
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhotoPieceFront(), $memberRequestDto->getReference());
            if ($fileName) $memberRequestDto->setPhotoPieceFront($fileName);
        }

        if ($memberRequestDto->getPhotoPieceBack()) {
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhotoPieceBack(), $memberRequestDto->getReference());
            if ($fileName) $memberRequestDto->setPhotoPieceBack($fileName);
        }

        if ($memberRequestDto->getPhotoPieceBack()) {
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhotoPieceBack(), $memberRequestDto->getReference());
            if ($fileName) $memberRequestDto->setPhotoPermisFront($fileName);
        }

        if ($memberRequestDto->getPhotoPermisBack()) {
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhotoPermisBack(), $memberRequestDto->getReference());
            if ($fileName) $memberRequestDto->setPhotoPermisBack($fileName);
        }

        return $memberRequestDto;
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
        $folder = "/var/www/html/public/members/" . $member->getReference() . '/';
        try {
            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['member' => $member]);
            file_put_contents($folder . "cnmci.pdf", $content);
            return $content ?? null;
        }catch(\Exception $e){
            return null;
        }
    }

    /**
     * @param Member $member
     * @return void
     * @throws \Exception
     */
    public function updateFromCnmciForm(array $data, Member $member): ?Member
    {
        if(empty($data)) return null;
//      $data["immatriculation"];
        $member->setLastName(strtoupper($data["exploitantNom"]));
        $member->setFirstName(strtoupper($data["exploitantPrenoms"]));
        $member->setDateOfBirth(new \DateTime($data["exploitantDateNais"]));
        $member->setBirthCity(strtoupper($data["exploitantLieuNais"]));
        $member->setNationality(strtoupper($data["exploitantNationalite"]));
        $member->setSex(strtoupper($data["exploitantSex"]));
        $member->setAddress(strtoupper($data["exploitantDomicile"]));
        $member->setIdType(strtoupper($data["exploitantTypeDoc"]));
        if (!empty($data["exploitantTypeDocAutre"])) $member->setIdType($data["exploitantTypeDocAutre"]);
        $member->setEtatCivil(strtoupper($data["exploitantEtatCivil"]));
        $member->setIdNumber($data["exploitantTypeDocNum"]);
        $member->setIdDeliveryPlace(strtoupper($data["exploitantDocLieuDelivrance"]));
        $member->setPhone($data["exploitantTel"]);
        $member->setEmail($data["exploitantEmail"]);
        $member->setCodeSticker($data["CodeSticker"]);
        /*
               $member->setDateOfBirth($data["formationClass"]);
               $member->setDateOfBirth($data["formationDiplomeObtenu"]);
               $member->setDateOfBirth($data["formationApprenMetierNiveau"]);
               $member->setDateOfBirth($data["formationApprenMetierDiplomeObtenu"]);
               $member->setDateOfBirth($data["principalActiviteEtabl"]);
               $member->setDateOfBirth($data["activiteSecondaireEtabl"]);
               $member->setDateOfBirth($data["raisonSocialEtabl"]);
               $member->setDateOfBirth($data["sigleEnseigneEtabl"] );
               $member->setDateOfBirth($data["dateDebutActiviteEtabl"]);
               $member->setDateOfBirth($data["identifiantCNPSEtabl"]) ;
               $member->setDateOfBirth($data["numCompteContribuableEtabl"]) ;
               $member->setDateOfBirth($data["addressPostalEtabl"]) ;
               $member->setDateOfBirth($data["TelEtabl"]);
               $member->setDateOfBirth($data["faxEtabl"]);
               $member->setDateOfBirth($data["communeEtabl"]);
               $member->setDateOfBirth($data["spEtabl"]);
               $member->setDateOfBirth($data["quartEtabl"]);
               $member->setDateOfBirth($data["villageEtabl"]);
               $member->setDateOfBirth($data["lotEtabl"]);
               $member->setDateOfBirth($data["ilotEtabl"]);
               $member->setDateOfBirth($data["effectifSalarieEtablFemme"]);
               $member->setDateOfBirth($data["effectifSalarieEtablHomme"]);
               $member->setDateOfBirth($data["effectifApprenantEtablFemme"]);
               $member->setDateOfBirth($data["effectifApprenantEtablHomme"]);

               $member->setLastName($data["referantNom"]);
               $member->setFirstName($data["firstname"]);
               $member->setDateOfBirth(new \DateTime($data["referantDateNais"]));
               $member->setBirthCity($data["referantLieuNais"]);
               $member->setNationality($data["nationality"]);
               $member->setSex($data["referantSex"]);
               $member->setAddress($data["referantDomicile"]);
               $member->setIdType($data["referantTypeDoc"]);
               $member->setIdNumber($data["referantNumDoc"]);
               $member->setIdDeliveryPlace($data["referantDocLieuDelivrance"]);
               $member->setIdDeliveryDate(new \DateTime($data["referantDocDateDelivrance"]));
               $member->setEtatCivil($data["referantEtatCivil"] );
               $member->setMobile($data["mobile"]);
               $member->setEmail($data["referantEmail"]);
               $member->setCodeSticker($data["CodeSticker"]);
       */

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

        $this->memberRepository->add($member, true);

        return $member;
    }

    public function createThumbnail(?File $file, Member $member, $width, $height){
        $this->memberAssetHelper->createThumbnail($file,  $member->getReference(), $width, $height);
    }
}

