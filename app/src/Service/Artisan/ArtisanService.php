<?php

namespace App\Service\Artisan;;
use App\Entity\Artisan;
use App\Entity\Child;
use App\Helper\ArtisanAssetHelper;
use App\Helper\CsvReaderHelper;
use App\Helper\PasswordHelper;
use App\Repository\ChildRepository;
use App\Repository\ArtisanRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class ArtisanService
{
    private ArtisanReceiptGeneratorService $artisanReceiptGeneratorService;
    private ArtisanRepository $artisanRepository;
    private ChildRepository $childRepository;

    private ContainerInterface $container;
    private CsvReaderHelper $csvReaderHelper;
    private ArtisanAssetHelper $artisanAssetHelper;

    public function __construct(
        ContainerInterface             $container,
        ArtisanReceiptGeneratorService $artisanReceiptGeneratorService,
        ArtisanRepository               $artisanRepository,
        ChildRepository                $childRepository,
        UserPasswordHasherInterface    $userPasswordHasher,
        CsvReaderHelper                $csvReaderHelper,
        ArtisanAssetHelper             $artisanAssetHelper)
    {
        $this->artisanReceiptGeneratorService = $artisanReceiptGeneratorService;
        $this->artisanRepository = $artisanRepository;
        $this->childRepository = $childRepository;
        $this->container = $container;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->csvReaderHelper = $csvReaderHelper;
        $this->artisanAssetHelper = $artisanAssetHelper;
    }

    /**
     * @param $row
     * @param string $uploadDir
     * @param Artisan $artisan
     * @return void
     */
    public function storeAsset($row, string $uploadDir, Artisan $artisan): void
    {
        if (isset($row) && !empty($row)) {
            $photo = new File($uploadDir . $row, false);
            if (file_exists($photo->getPathname())) {
                $fileName = $this->artisanAssetHelper->uploadAsset($photo, $artisan->getReference());
                if ($fileName) $artisan->setPhoto($fileName);
            }
        }
    }


    /**
     * @param Artisan $artisan
     * @return void
     */
    public function store(?Artisan $artisan){
        if(!$artisan)  return;
        $this->artisanRepository->add($artisan, true);
    }

    /**
     * @param $data
     * @return Artisan
     */
    public function create($data)
    {
        $artisan =  new Artisan();

        /***********  ARTISAN EXPLOITANT ***/
        $this->saveExploitant($artisan, $data);

        /*******FORMATION PROFESSIONNELLE******/
        $this->saveFormationProfessionnelle($artisan, $data);

        /***************ETABLISSEMENT ********************/
        $this->saveEtablissement($artisan, $data);

        /****  PERSONNE POUVANT ENGAGER L'ENTREPRISE ****/
        $this->saveReferant($artisan, $data);

        /*************STORE IMAGE FILES*********************/
    //  $this->createArtisan($data, $artisan);
        $this->saveArtisanImages($artisan, $data);

        /**************STORE CHILDREN*****************/
        $this->saveChildren($artisan, $data);

        return $artisan;
    }

    /**
     * @param $data
     * @param Artisan $artisan
     * @return Artisan|null
     */
    public function update($data, Artisan $artisan) : ?Artisan
    {
        if(!$artisan) return null;

        /***********  ARTISAN EXPLOITANT ***/
        $this->saveExploitant($artisan, $data);

        /*******       FORMATION PROFESSIONNELLE      ******/
        $this->saveFormationProfessionnelle($artisan, $data);

        /******************** ETABLISSEMENT ********************/
        $this->saveEtablissement($artisan, $data);

        /****  PERSONNE POUVANT ENGAGER L'ENTREPRISE ****/
        $this->saveReferant($artisan, $data);

        /********************STORE IMAGE FILES *********************/
       // $this->createArtisan($data, $artisan);
        $this->saveArtisanImages($artisan, $data);

        /************** STORE CHILDREN*****************/
        $this->saveChildren($artisan, $data);

        return $artisan;
    }


    /**
     * @param Artisan $artisan
     * @param $data
     * @return mixed
     */
    public function saveChildren(Artisan $artisan, $data) : void
    {
        if(isset($data['child_lastname'])) {
            $count = count($data['child_lastname']);
            for($i = 0; $i < $count ; $i++){
                $child =  new Child();
                $child->setLastName($data['child_lastname'][$i]);
                $child->setFirstName($data['child_firstname'][$i]);
                $child->setSex($data['child_sex'][$i]);
                $child->setArtisan($artisan);
                $artisan->addChild($child);
            }
        }
    }

    /**
     * @param Artisan $artisan
     * @param $data
     * @return mixed
     */
    public function saveExploitant(Artisan $artisan, $data): void
    {
        $artisan->setReference(str_replace("-","", substr(Uuid::v4()->toRfc4122(), 0, 18)));

        if (isset($data["exploitantNom"])) $artisan->setLastName(strtoupper($data["exploitantNom"]));
        if (isset($data["exploitantPrenoms"])) $artisan->setFirstName(strtoupper($data["exploitantPrenoms"]));
        if (isset($data["exploitantDateNais"])) {
            try {
                $date = \DateTime::createFromFormat("d/m/Y", $data["exploitantDateNais"]);
                if (!$date) throw new \Exception();
                $artisan->setDateOfBirth($date);
            } catch (\Exception $e) {
                $artisan->setDateOfBirth(null);
            }
        }
        if (isset($data["exploitantLieuNais"])) $artisan->setBirthCity(strtoupper($data["exploitantLieuNais"]));
        if (isset($data["exploitantNationalite"])) $artisan->setNationality(strtoupper($data["exploitantNationalite"]));
        if (isset($data["exploitantSex"])) $artisan->setSex(strtoupper($data["exploitantSex"]));
        if (isset($data["exploitantTypeDocAutre"])) $artisan->setIdType(strtoupper($data["exploitantTypeDocAutre"]));
        if (isset($data["exploitantTypeDocNum"])) $artisan->setIdNumber($data["exploitantTypeDocNum"]);
        if (isset($data["exploitantDocLieuDelivrance"])) $artisan->setReprIDDeliveryPlace(strtoupper($data["exploitantDocLieuDelivrance"]));
        if (isset($data["exploitantDocDateDelivrance"])) {
            try {
                $date = \DateTime::createFromFormat("d/m/Y", $data["exploitantDocDateDelivrance"]);
                if (!$date) throw new \Exception();
                $artisan->setIdDeliveryDate($date);
            } catch (\Exception $e) {
                $artisan->setIdDeliveryDate(null);
            }
        }
        if (isset($data["exploitantTel"])) $artisan->setPhone($data["exploitantTel"]);
        if (isset($data["exploitantEmail"])) $artisan->setEmail($data["exploitantEmail"]);
        if (isset($data["exploitantDomicile"])) $artisan->setDomicile($data["exploitantDomicile"]);
    }

    /**
    /**
     * @param Artisan $artisan
     * @param $data
     * @return mixed
     */
    public function saveReferant(Artisan $artisan, $data): void
    {
        if (isset($data["referantNom"])) $artisan->setReprLastName($data["referantNom"]);
        if (isset($data["referantPrenom"])) $artisan->setReprFirstName($data["referantPrenom"]);
        if (isset($data["referantNationality"])) $artisan->setReprFirstName($data["reprNationality"]);

        if (isset($data["referantDateNais"])) {
            try {
                $date = \DateTime::createFromFormat("d/m/Y", $data["referantDateNais"]);
                if (!$date) throw new \Exception();
                $artisan->setReprDateNais($date);
            } catch (\Exception $e) {
                $artisan->setReprDateNais(null);
            }
        }
        if (isset($data["referantLieuNais"])) $artisan->setReprLieuNais($data["referantLieuNais"]);
        if (isset($data["referantEtatCivil"])) $artisan->setReprEtatCivil($data["referantEtatCivil"]);
        if (isset($data["referantDomicile"])) $artisan->setReprDomicile($data["referantDomicile"]);
        if (isset($data["referantSex"])) $artisan->setReprSex($data["referantSex"]);
        if (isset($data["referantTypeDoc"])) $artisan->setReprIDType($data["referantTypeDoc"]);
        if (isset($data["referantTypeDocAutre"])) $artisan->setReprIDType($data["referantTypeDocAutre"]);
        if (isset($data["referantNumDoc"])) $artisan->setReprIDNum($data["referantNumDoc"]);
        if (isset($data["referantDocLieuDelivrance"])) $artisan->setReprIDDeliveryPlace($data["referantDocLieuDelivrance"]);

        if (isset($data["referantDocDateDelivrance"])) {
            try {
                $date = \DateTime::createFromFormat("d/m/Y", $data["referantDocDateDelivrance"]);
                if (!$date) throw new \Exception();
                $artisan->setReprIDDeliveryDate($date);
            } catch (\Exception $e) {
                $artisan->setReprIDDeliveryDate(null);
            }
        }

        if (isset($data["referantTel"])) $artisan->setReprTel($data["referantTel"]);
        if (isset($data["referantEmail"])) $artisan->setReprEmail($data["referantEmail"]);
    }

    /**
     * @param Artisan $artisan
     * @param $data
     * @return mixed
     */
    public function saveEtablissement(Artisan $artisan, $data): void
    {
        if (isset($data["principalActiviteEtabl"])) $artisan->setCompanyMainActivity($data["principalActiviteEtabl"]);
        if (isset($data["activiteSecondaireEtabl"])) $artisan->setCompanySecondaryActivity($data["activiteSecondaireEtabl"]);
        if (isset($data["raisonSocialEtabl"])) $artisan->setCompanyName($data["raisonSocialEtabl"]);
        if (isset($data["sigleEnseigneEtabl"])) $artisan->setCompanySigle($data["sigleEnseigneEtabl"]);

        if (isset($data["dateDebutActiviteEtabl"])) {
            try {
                $date = \DateTime::createFromFormat("d/m/Y", $data["dateDebutActiviteEtabl"]);
                if (!$date) throw new \Exception();
                $artisan->setCompanyStartingDate($date);
            } catch (\Exception $e) {
                $artisan->setCompanyStartingDate(null);
            }
        }
        if (isset($data["identifiantCNPSEtabl"])) $artisan->setIdentifiantCnps($data["identifiantCNPSEtabl"]);
        if (isset($data["regimeFiscalEtabl"])) $artisan->setCompanyFiscalRegime($data["regimeFiscalEtabl"]);
        if (isset($data["typeEntrepriseEtabl"])) $artisan->setTypeCompany($data["typeEntrepriseEtabl"]);
        if (isset($data["numCompteContribuableEtabl"])) $artisan->setNumCompteContribuableEtabl($data["numCompteContribuableEtabl"]);
        if (isset($data["addressPostalEtabl"])) $artisan->setCompanyAdressPostal($data["addressPostalEtabl"]);
        if (isset($data["TelEtabl"])) $artisan->setCompanyTel($data["TelEtabl"]);
        if (isset($data["faxEtabl"])) $artisan->setCompanyFax($data["faxEtabl"]);
        if (isset($data["communeEtabl"])) $artisan->setCompanyCommune($data["communeEtabl"]);
        if (isset($data["spEtabl"])) $artisan->setCompanySp($data["spEtabl"]);
        if (isset($data["quartEtabl"])) $artisan->setCompanyQuartier($data["quartEtabl"]);
        if (isset($data["villageEtabl"])) $artisan->setCompanyVillage($data["villageEtabl"]);
        if (isset($data["lotEtabl"])) $artisan->setCompanyLotNum($data["lotEtabl"]);
        if (isset($data["ilotEtabl"])) $artisan->setCompanyILotNum($data["ilotEtabl"]);
        if (isset($data["effectifSalarieEtablFemme"])) $artisan->setCompanyTotalWomen($data["effectifSalarieEtablFemme"]);
        if (isset($data["effectifSalarieEtablHomme"])) $artisan->setCompanyTotalMen($data["effectifSalarieEtablHomme"]);
        if (isset($data["effectifApprenantEtablFemme"])) $artisan->setCompanyTotalWomenApprentis($data["effectifApprenantEtablFemme"]);
        if (isset($data["effectifApprenantEtablHomme"])) $artisan->setCompanyTotalMenApprentis($data["effectifApprenantEtablHomme"]);
    }

    /**
     * @param Artisan $artisan
     * @param $data
     * @return void
     */
    public function saveFormationProfessionnelle(Artisan $artisan, $data): void
    {
        if (isset($data["formationNiveauEtude"])) $artisan->setFormationNiveauEtude($data["formationNiveauEtude"]);
        if (isset($data["formationClass"])) $artisan->setFormationClass($data["formationClass"]);
        if (isset($data["formationDiplomeObtenu"])) $artisan->setFormationDiplomeObtenu($data["formationDiplomeObtenu"]);
        if (isset($data["formationApprenMetierNiveau"])) $artisan->setFormationApprenMetierNiveau($data["formationApprenMetierNiveau"]);
        if (isset($data["formationApprenMetierDiplomeObtenu"])) $artisan->setFormationApprenMetierDiplomeObtenu($data["formationApprenMetierDiplomeObtenu"]);
        if (isset($data["exploitantEtatCivil"])) $artisan->setExploitantEtatCivil($data["exploitantEtatCivil"]);
        if (isset($data["formationApprenMetier"])) $artisan->setFormationApprenMetier($data["formationApprenMetier"]);
        if (isset($data["formationApprenMetierNiveau"])) $artisan->setFormationApprenMetierNiveau($data["formationApprenMetierNiveau"]);
        if (isset($data["formationApprenMetierDiplomeObtenu"])) $artisan->setFormationApprenMetierDiplomeObtenu($data["formationApprenMetierDiplomeObtenu"]);
        if (isset($data["formationApprenMetierCNMCI"])) $artisan->setFormationApprenMetierCNMCI($data["formationApprenMetierCNMCI"]);
        if (isset($data["formationApprenMetierTypeCNMCI"])) $artisan->setFormationApprenMetierTypeCNMCI($data["formationApprenMetierTypeCNMCI"]);
    }

    /**
     * @param Artisan $artisan
     * @param $data
     * @return void
     */
    public function saveArtisanImages(Artisan $artisan, $data): void
    {
        $dir = $artisan->getReference();
        if (isset($data["artisan_registration_photo"]) && !empty($data["artisan_registration_photo"])) {
            $fileName = $this->artisanAssetHelper->uploadAsset($data["artisan_registration_photo"], $dir);
            if ($fileName) $artisan->setPhoto($fileName->getFilename());
        }

        if (isset($data["artisan_photoPieceFront"]) && !empty($data["artisan_photoPieceFront"])) {
            $fileName = $this->artisanAssetHelper->uploadAsset($data["artisan_photoPieceFront"], $dir);
            if ($fileName) $artisan->setPhotoPieceFront($fileName->getFilename());
        }
        if (isset($data["artisan_photoPieceBack"]) && !empty($data["artisan_photoPieceBack"])) {
            $fileName = $this->artisanAssetHelper->uploadAsset($data["artisan_photoPieceBack"], $dir);
            if ($fileName) $artisan->setPhotoPieceBack($fileName->getFilename());
        }
        if (isset($data["artisan_photoPermisFront"]) && !empty($data["artisan_photoPermisFront"])) {
            $fileName = $this->artisanAssetHelper->uploadAsset($data["artisan_photoPermisFront"], $dir);
            if ($fileName) $artisan->setPhotoPermisFront($fileName->getFilename());
        }
        if (isset($data["artisan_photoPermisBack"]) && !empty($data["artisan_photoPermisBack"])) {
            $fileName = $this->artisanAssetHelper->uploadAsset($data["artisan_photoPermisBack"], $dir);
            if ($fileName) $artisan->setPhotoPermisBack($fileName->getFilename());
        }
    }


    /**
     * @param Artisan $artisan
     * @return void
     * @throws \Exception
     */
    public function createArtisan(Artisan $artisan): void
    {
        date_default_timezone_set("Africa/Abidjan");

        try{
            $this->artisanRepository->setAutoIncrementToLast($this->artisanRepository->getLastRowId());
            $lastRowId = $this->artisanRepository->getLastRowId();
        }catch(\Exception $e){
            $lastRowId = 0;
        }

        $artisan->setRoles(['ROLE_USER']);

        $date = new \DateTime('now');
        $artisan->setSubscriptionDate($date);

        $sexCode = null;
        if($artisan->getSex() === "H") $sexCode = "SY1";
        elseif($artisan->getSex() === "F") $sexCode = "SY2";

        $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $lastRowId+1);
        $artisan->setMatricule($matricule);

        $expiredDate = $date->format('Y-12-31');
        $artisan->setSubscriptionExpireDate(new \DateTime($expiredDate));

        $artisan->setPassword($this->userPasswordHasher->hashPassword($artisan, PasswordHelper::generate()));

        $this->artisanRepository->add($artisan, true);
    }


}
