<?php

namespace App\Service\Member;

use App\DTO\ChildDto;
use App\DTO\MemberRequestDto;
use App\Entity\Member;
use App\Helper\CsvReaderHelper;
use App\Helper\MemberAssetHelper;
use App\Helper\PasswordHelper;
use App\Mapper\ChildMapper;
use App\Mapper\MemberMapper;
use App\Repository\ChildRepository;
use App\Repository\MemberRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MemberService
{
    private MemberCardGeneratorService $memberCardGeneratorService;

    private MemberReceiptGeneratorService $memberReceiptGeneratorService;

    private MemberAssetHelper $memberAssetHelper;

    private MemberRepository $memberRepository;
    private ChildRepository $childRepository;

    private ContainerInterface $container;
    private UserPasswordHasherInterface $userPasswordHasher;

    private CsvReaderHelper $csvReaderHelper;

    public function __construct(
        ContainerInterface $container,
        MemberCardGeneratorService $memberCardGeneratorService,
        MemberReceiptGeneratorService $memberReceiptGeneratorService,
        MemberAssetHelper $memberAssetHelper,
        MemberRepository $memberRepository,
        ChildRepository $childRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        CsvReaderHelper $csvReaderHelper)
    {
        $this->memberCardGeneratorService = $memberCardGeneratorService;
        $this->memberReceiptGeneratorService = $memberReceiptGeneratorService;
        $this->memberAssetHelper = $memberAssetHelper;
        $this->memberRepository = $memberRepository;
        $this->childRepository = $childRepository;
        $this->container = $container;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->csvReaderHelper = $csvReaderHelper;
    }


    public function createMember(MemberRequestDto $memberRequestDto)
    {
        date_default_timezone_set("Africa/Abidjan");

        $this->memberRepository->setAutoIncrementToLast($this->memberRepository->getLastRowId());
        $lastRowId = $this->memberRepository->getLastRowId();
        $memberRequestDto->setRoles(['ROLE_USER']);

        $date = new \DateTime('now');
        $memberRequestDto->setSubscriptionDate($date);

        $sexCode = null;
        if($memberRequestDto->getSex() === "H") $sexCode = "SY1";
        elseif($memberRequestDto->getSex() === "F") $sexCode = "SY2";

        $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $lastRowId+1);
        $memberRequestDto->setMatricule($matricule);

        $expiredDate = $date->format('Y-12-31');
        $memberRequestDto->setSubscriptionExpireDate(new \DateTime($expiredDate));

        $memberRequestDto->setPassword($this->userPasswordHasher->hashPassword($memberRequestDto, PasswordHelper::generate()));

        if($memberRequestDto->getPhoto()){
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhoto(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhoto($fileName);
        }

        if($memberRequestDto->getPhotoPieceFront()){
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhotoPieceFront(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhotoPieceFront($fileName);
        }

        if($memberRequestDto->getPhotoPieceBack()){
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhotoPieceBack(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhotoPieceBack($fileName);
        }

        if($memberRequestDto->getPhotoPieceBack()){
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhotoPieceBack(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhotoPermisFront($fileName);
        }

        if($memberRequestDto->getPhotoPermisBack()){
            $fileName = $this->memberAssetHelper->uploadAsset($memberRequestDto->getPhotoPermisBack(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhotoPermisBack($fileName);
        }

        $member = MemberMapper::MapToMember($memberRequestDto);
        $this->memberRepository->add($member, true);
    }

    public function deleteMember(?MemberRequestDto $memberDto){

    }

    public function updateMember(?MemberRequestDto $memberRequestDto){
        $this->memberRepository->setAutoIncrementToLast($this->memberRepository->getLastRowId());
        $memberRequestDto->setRoles(['ROLE_USER']);
        date_default_timezone_set("Africa/Abidjan");
        $date = new \DateTime('now');
        $memberRequestDto->setSubscriptionDate($date);

        $sexCode = "SY1";
        if($memberRequestDto->getSex() === "H") $sexCode = "SY1";
        if($memberRequestDto->getSex() === "F") $sexCode = "SY2";

        if($sex = $data['sex']->getData()){
            $sexChar = substr( $memberRequestDto->getMatricule(),2, 1);
            if($sexChar == '1' && $sex == 'F') $memberRequestDto->setMatricule(str_replace('SY1', 'SY2',$memberRequestDto->getMatricule() ));
            if($sexChar == '2' && $sex == 'H') $memberRequestDto->setMatricule(str_replace('SY2', 'SY1',$memberRequestDto->getMatricule() ));
        }

        // $expiredDate = $date->add(new \DateInterval("P1Y"));
        $expiredDate = $date->format('Y-12-31');
        $memberRequestDto->setSubscriptionExpireDate(new \DateTime($expiredDate));

        $memberRequestDto->setPassword($this->userPasswordHasher->hashPassword($memberRequestDto, PasswordHelper::generate()));

        $member = MemberMapper::MapToMember($memberRequestDto);
        $this->memberRepository->add($member, true);

        $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $memberRequestDto->getId());
        $memberRequestDto->setMatricule($matricule);

        if($data['photo']->getData()){
            $fileName = $this->memberAssetHelper->uploadAsset($data['photo']->getData(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhoto($fileName);
        }

        if($data['photoPieceFront']->getData()){
            $fileName = $this->memberAssetHelper->uploadAsset($data['photoPieceFront']->getData(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhotoPieceFront($fileName);
        }

        if($data['photoPieceBack']->getData()){
            $fileName = $this->memberAssetHelper->uploadAsset($data['photoPieceBack']->getData(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhotoPieceBack($fileName);
        }

        if($data['photoPermisFront']->getData()){
            $fileName = $this->memberAssetHelper->uploadAsset($data['photoPermisFront']->getData(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhotoPermisFront($fileName);
        }

        if($data['photoPermisBack']->getData()){
            $fileName = $this->memberAssetHelper->uploadAsset($data['photoPermisBack']->getData(), $memberRequestDto->getMatricule());
            if($fileName) $memberRequestDto->setPhotoPermisBack($fileName);
        }

        $cl = $data['child_lastname'];
        if(is_array($cl)){
            $count = count($cl);
            for($i =0; $i < $count ; $i++){
                $child =  new ChildDto();
                $child->setLastName($data['child_lastname'][$i]);
                $child->setFirstName($data['child_firstname'][$i]);
                $child->setSex($data['child_sex'][$i]);
                $child->setParent($memberRequestDto);
                $memberRequestDto->addChild($child);
                $this->childRepository->add(ChildMapper::MapToChild($child));
            }
        }

        $member = MemberMapper::MapToMember($memberRequestDto);
        $this->memberRepository->add($member, true);
    }


    public function uploadAsset(?File $file, ?string $destDirectory): ?string
    {
        return $this->memberAssetHelper->uploadAsset($file, $destDirectory);
    }

    public function generateMemberCard(?MemberRequestDto $memberRequestDto): void
    {
        date_default_timezone_set("Africa/Abidjan");
        if ($memberRequestDto) {
            if(empty($memberRequestDto->getPhoto())) return;
            $cardImage = $this->memberCardGeneratorService->generate($memberRequestDto);
            $memberRequestDto->setCardPhoto(new File($cardImage));
            $memberRequestDto->setModifiedAt(new \DateTime());
            $member = MemberMapper::MapToMember($memberRequestDto);
            $this->memberRepository->add($member, true);
        }
    }

    public function generateAllMemberCards(): array
    {
        date_default_timezone_set("Africa/Abidjan");
        $memberDtos = [];
        $members = $this->memberRepository->findAll();
        foreach ($members as $member) {
            $memberDto = MemberMapper::MapToMemberRequestDto($member);
            $this->generateMemberCard($memberDto);
            $memberDtos[] = $memberDto;
        }
        return $memberDtos;
    }

    public function archiveMemberCards(array $memberDtos): ?string
    {
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(0);
        $zipArchive = new \ZipArchive();
        $zipFile = $this->container->getParameter('kernel.project_dir') . '/public/members/tmp/members.zip';;
        if(file_exists($zipFile)) unlink($zipFile);
        if($zipArchive->open($zipFile, \ZipArchive::CREATE) === true)
        {
            $this->generateAllMemberCards();
            foreach($memberDtos as $memberDto)
            {
                $cardPhotoRealPath = $this->container->getParameter('kernel.project_dir') . "/public/members/" . $memberDto->getMatricule() . "/" . $memberDto->getCardPhoto();
                if(is_file($cardPhotoRealPath)) $zipArchive->addFile($cardPhotoRealPath, $memberDto->getCardPhoto() );
            }
            $zipArchive->close();
            return $zipFile;
        }
        return null;
    }

    public function generateMemberPdfReceipt(?MemberRequestDto $memberRequestDto): void
    {
        $this->memberReceiptGeneratorService->generate($memberRequestDto);
    }

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

    public function createMemberFromFile(): void
    {

        set_time_limit(3600);
        $finder = new Finder();
        $uploadDir = $this->container->getParameter('kernel.project_dir') . '/public/uploads/';
        $csvFiles = $finder->in($uploadDir)->name(['*.csv','*.jpg', '*.jpeg','*.png','*.gif']);
        $fs = new Filesystem();
        $fs->remove($csvFiles); // remove file after import
        foreach($csvFiles as $file) {
            $rows =  $this->csvReaderHelper->read($file);
            $this->memberRepository->setAutoIncrementToLast($this->memberRepository->getLastRowId());
            foreach ($rows as $row){
                try{
                    date_default_timezone_set("Africa/Abidjan");
                    $date = new \DateTime('now');

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
                    if (isset($row["DATE_NAISSANCE"])) $member->setDateOfBirth(new \DateTime($row["DATE_NAISSANCE"]));
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
                        else $member->setSubscriptionDate(new \DateTime($row["DATE_SOUSCRIPTION"]));
                    }

                    if (array_key_exists("DATE_EXPIRATION_SOUSCRIPTION", $row)) {
                        $expiredDate = new \DateTime($row["DATE_SOUSCRIPTION"]);
                        //   $expiredDate = $expiredDate->add(new \DateInterval("P1Y"));
                        $expiredDate = $expiredDate->format('Y-12-31');
                        if (!empty($row["DATE_EXPIRATION_SOUSCRIPTION"])) $member->setSubscriptionExpireDate(new \DateTime($row["DATE_EXPIRATION_SOUSCRIPTION"]));
                        else $member->setSubscriptionExpireDate(new \DateTime($expiredDate));
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
                        if (isset($row["PHOTO"]) && !empty($row["PHOTO"])) {
                            $photo = new File($uploadDir . $row["PHOTO"], false);
                            if (file_exists($photo->getPathname())) {
                                $fileName = $this->uploadAsset($photo, $member->getMatricule());
                                if ($fileName) $member->setPhoto($fileName);
                            }
                        }

                        if (isset($row["PHOTO_PIECE_RECTO"]) && !empty($row["PHOTO_PIECE_RECTO"])) {
                            $photo = new File($uploadDir . $row["PHOTO_PIECE_RECTO"], false);
                            if (file_exists($photo->getPathname())) {
                                $fileName = $this->uploadAsset($photo, $member->getMatricule());
                                if ($fileName) $member->setPhotoPieceFront($fileName);
                            }
                        }

                        if (isset($row["PHOTO_PIECE_VERSO"]) && !empty($row["PHOTO_PIECE_VERSO"])) {
                            $photo = new File($uploadDir . $row["PHOTO_PIECE_VERSO"], false);
                            if (file_exists($photo->getPathname())) {
                                $fileName = $this->uploadAsset($photo, $member->getMatricule());
                                if ($fileName) $member->setPhotoPieceBack($fileName);
                            }
                        }

                        if (isset($row["PHOTO_PERMIS_RECTO"]) && !empty($row["PHOTO_PERMIS_RECTO"])) {
                            $photo = new File($uploadDir . $row["PHOTO_PERMIS_RECTO"], false);
                            if (file_exists($photo->getPathname())) {
                                $fileName = $this->uploadAsset($photo, $member->getMatricule());
                                if ($fileName) $member->setPhotoPermisFront($fileName);
                            }
                        }

                        if (isset($row["PHOTO_PERMIS_VERSO"]) && !empty($row["PHOTO_PERMIS_VERSO"])) {
                            $photo = new File($uploadDir . $row["PHOTO_PERMIS_VERSO"], false);
                            if (file_exists($photo->getPathname())) {
                                $fileName = $this->uploadAsset($photo, $member->getMatricule());
                                if ($fileName) $member->setPhotoPermisBack($fileName);
                            }
                        }
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

    }

}
