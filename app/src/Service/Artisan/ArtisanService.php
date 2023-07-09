<?php

namespace App\Service\Artisan;;
use App\Entity\Artisan;
use App\Helper\ArtisanAssetHelper;
use App\Helper\CsvReaderHelper;
use App\Repository\ChildRepository;
use App\Repository\ArtisanRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
     * @param Artisan $artisan
     * @return void
     */

    public function saveArtisanImages(Artisan $artisanRequestDto): Artisan
    {
        if ($artisanRequestDto->getPhoto()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhoto(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhoto($fileName);
        }
        if ($artisanRequestDto->getPhotoPieceFront()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhotoPieceFront(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhotoPieceFront($fileName);
        }
        if ($artisanRequestDto->getPhotoPieceBack()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhotoPieceBack(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhotoPieceBack($fileName);
        }
        if ($artisanRequestDto->getPhotoPieceBack()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhotoPieceBack(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhotoPermisFront($fileName);
        }
        if ($artisanRequestDto->getPhotoPermisBack()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhotoPermisBack(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhotoPermisBack($fileName);
        }
        return $artisanRequestDto;
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
                $fileName = $this->artisanAssetHelper->uploadAsset($photo, $artisan->getMatricule());
                if ($fileName) $artisan->setPhoto($fileName);
            }
        }
    }

    public function store(Artisan $artisan){
        $this->artisanRepository->add($artisan);
    }

    public function create($data){

        $artisan =  new Artisan();

        /***********  ARTISAN EXPLOITANT ***/
        if(isset($data["exploitantNom"])) $artisan->setLastName($data["exploitantNom"]);
        if(isset($data["exploitantPrenoms"]))  $artisan->setFirstName($data["exploitantPrenoms"]);
        if(isset($data["exploitantDateNais"]))  $artisan->setDateOfBirth($data["exploitantDateNais"]);
        if(isset($data["exploitantLieuNais"]))  $artisan->setBirthCity($data["exploitantLieuNais"]);
        if(isset($data["exploitantNationalite"]))  $artisan->setNationality($data["exploitantNationalite"]);
        if(isset($data["exploitantSex"]))  $artisan->setSex($data["exploitantSex"]);
        if(isset($data["exploitantTypeDocAutre"]))  $artisan->setIdType($data["exploitantTypeDocAutre"]);
        if(isset($data["exploitantTypeDocNum"]))  $artisan->setIdNumber($data["exploitantTypeDocNum"]);
        if(isset($data["exploitantDocLieuDelivrance"]))  $artisan->setReprIDDeliveryPlace($data["exploitantDocLieuDelivrance"]);
        if(isset($data["exploitantDocDateDelivrance"]))  $artisan->setIdDeliveryDate($data["exploitantDocDateDelivrance"]);
        if(isset($data["exploitantTel"]))  $artisan->setMobile($data["exploitantTel"]);
        if(isset($data["exploitantEmail"]))  $artisan->setEmail($data["exploitantEmail"]);
        if(isset($data["exploitantDomicile"]))  $artisan->setDomicile($data["exploitantDomicile"]);

        /*******       FORMATION PROFESSIONNELLE      ******/
        if(isset($data["formationNiveauEtude"]))  $artisan->setFormationNiveauEtude($data["formationNiveauEtude"]);
        if(isset($data["formationClass"]))  $artisan->setFormationClass($data["formationClass"]);
        if(isset($data["formationDiplomeObtenu"]))  $artisan->setFormationDiplomeObtenu($data["formationDiplomeObtenu"]);
        if(isset($data["formationApprenMetierNiveau"]))  $artisan->setFormationApprenMetierNiveau($data["formationApprenMetierNiveau"]);
        if(isset($data["formationApprenMetierDiplomeObtenu"]))  $artisan->setFormationApprenMetierDiplomeObtenu($data["formationApprenMetierDiplomeObtenu"]);
        if(isset($data["exploitantEtatCivil"]))  $artisan->setExploitantEtatCivil($data["exploitantEtatCivil"]);
        if(isset($data["formationApprenMetier"]))  $artisan->setFormationApprenMetier($data["formationApprenMetier"]);
        if(isset($data["formationApprenMetierNiveau"]))  $artisan->setFormationApprenMetierNiveau($data["formationApprenMetierNiveau"]);
        if(isset($data["formationApprenMetierDiplomeObtenu"]))  $artisan->setFormationApprenMetierDiplomeObtenu($data["formationApprenMetierDiplomeObtenu"]);
        if(isset($data["formationApprenMetierCNMCI"]))  $artisan->setFormationApprenMetierCNMCI($data["formationApprenMetierCNMCI"]);
        if(isset($data["formationApprenMetierTypeCNMCI"]))  $artisan->setFormationApprenMetierTypeCNMCI($data["formationApprenMetierTypeCNMCI"]);

        /******************** ETABLISSEMENT ********************/
        if(isset($data["principalActiviteEtabl"]))  $artisan->setCompanyMainActivity($data["principalActiviteEtabl"]);
        if(isset($data["activiteSecondaireEtabl"]))  $artisan->setCompanySecondaryActivity($data["activiteSecondaireEtabl"]);
        if(isset($data["raisonSocialEtabl"]))  $artisan->setCompanyName($data["raisonSocialEtabl"]);
        if(isset($data["sigleEnseigneEtabl"]))  $artisan->setCompanySigle($data["sigleEnseigneEtabl"]);
        if(isset($data["dateDebutActiviteEtabl"]))  $artisan->setCompanyStartingDate($data["dateDebutActiviteEtabl"]);
        if(isset($data["identifiantCNPSEtabl"]))  $artisan->setIdentifiantCnps($data["identifiantCNPSEtabl"]);
        if(isset($data["regimeFiscalEtabl"]))  $artisan->setCompanyFiscalRegime($data["regimeFiscalEtabl"]);
        if(isset($data["typeEntrepriseEtabl"]))  $artisan->setTypeCompany($data["typeEntrepriseEtabl"]);
        if(isset($data["numCompteContribuableEtabl"]))  $artisan->setNumCompteContribuableEtabl($data["numCompteContribuableEtabl"]);
        if(isset($data["addressPostalEtabl"]))  $artisan->setCompanyAdressPostal($data["addressPostalEtabl"]);
        if(isset($data["TelEtabl"]))  $artisan->setCompanyTel($data["TelEtabl"]);
        if(isset($data["faxEtabl"]))  $artisan->setCompanyFax($data["faxEtabl"]);
        if(isset($data["communeEtabl"]))  $artisan->setCompanyCommune($data["communeEtabl"]);
        if(isset($data["spEtabl"]))  $artisan->setCompanySp($data["spEtabl"]);
        if(isset($data["quartEtabl"]))  $artisan->setCompanyQuartier($data["quartEtabl"]);
        if(isset($data["villageEtabl"]))  $artisan->setCompanyVillage($data["villageEtabl"]);
        if(isset($data["lotEtabl"]))  $artisan->setCompanyLotNum($data["lotEtabl"]);
        if(isset($data["ilotEtabl"]))  $artisan->setCompanyILotNum($data["ilotEtabl"]);
        if(isset($data["effectifSalarieEtablFemme"]))  $artisan->setCompanyTotalWomen($data["effectifSalarieEtablFemme"]);
        if(isset($data["effectifSalarieEtablHomme"]))  $artisan->setCompanyTotalMen($data["effectifSalarieEtablHomme"]);
        if(isset($data["effectifApprenantEtablFemme"]))  $artisan->setCompanyTotalWomenApprentis($data["effectifApprenantEtablFemme"]);
        if(isset($data["effectifApprenantEtablHomme"]))  $artisan->setCompanyTotalMenApprentis($data["effectifApprenantEtablHomme"]);

        /****  PERSONNE POUVANT ENGAGER L'ENTREPRISE ****/
        if(isset($data["referantNom"]))  $artisan->setReprLastName($data["referantNom"]);
        if(isset($data["referantPrenom"]))  $artisan->setReprFirstName($data["referantPrenom"]);
        if(isset($data["referantDateNais"]))  $artisan->setReprDateNais($data["referantDateNais"]);
        if(isset($data["referantLieuNais"]))  $artisan->setReprLieuNais($data["referantLieuNais"]);
        if(isset($data["referantEtatCivil"]))  $artisan->setReprEtatCivil($data["referantEtatCivil"]);
        if(isset($data["referantDomicile"]))  $artisan->setReprDomicile($data["referantDomicile"]);
        if(isset($data["referantSex"]))  $artisan->setReprSex($data["referantSex"]);
        if(isset($data["referantTypeDoc"]))  $artisan->setReprIDType($data["referantTypeDoc"]);
        if(isset($data["referantTypeDocAutre"]))  $artisan->setReprIDType($data["referantTypeDocAutre"]);
        if(isset($data["referantNumDoc"]))  $artisan->setReprIDNum($data["referantNumDoc"]);
        if(isset($data["referantDocLieuDelivrance"]))  $artisan->setReprIDDeliveryPlace($data["referantDocLieuDelivrance"]);
        if(isset($data["referantDocDateDelivrance"]))  $artisan->setReprIDDeliveryDate($data["referantDocDateDelivrance"]);
        if(isset($data["referantTel"]))  $artisan->setReprTel($data["referantTel"]);
        if(isset($data["referantEmail"]))  $artisan->setReprEmail($data["referantEmail"]);
        return $artisan;
    }

    public function update($data, Artisan $artisan){

        /***********  ARTISAN EXPLOITANT ***/
        if(isset($data["exploitantNom"])) $artisan->setLastName($data["exploitantNom"]);
        if(isset($data["exploitantPrenoms"]))  $artisan->setFirstName($data["exploitantPrenoms"]);
        if(isset($data["exploitantDateNais"]))  $artisan->setDateOfBirth(new \DateTime($data["exploitantDateNais"]));
        if(isset($data["exploitantLieuNais"]))  $artisan->setBirthCity($data["exploitantLieuNais"]);
        if(isset($data["exploitantNationalite"]))  $artisan->setNationality($data["exploitantNationalite"]);
        if(isset($data["exploitantSex"]))  $artisan->setSex($data["exploitantSex"]);
        if(isset($data["exploitantTypeDocAutre"]))  $artisan->setIdType($data["exploitantTypeDocAutre"]);
        if(isset($data["exploitantTypeDocNum"]))  $artisan->setIdNumber($data["exploitantTypeDocNum"]);
        if(isset($data["exploitantDocLieuDelivrance"]))  $artisan->setReprIDDeliveryPlace($data["exploitantDocLieuDelivrance"]);
        if(isset($data["exploitantDocDateDelivrance"]))  $artisan->setIdDeliveryDate($data["exploitantDocDateDelivrance"]);
        if(isset($data["exploitantTel"]))  $artisan->setMobile($data["exploitantTel"]);
        if(isset($data["exploitantEmail"]))  $artisan->setEmail($data["exploitantEmail"]);
        if(isset($data["exploitantDomicile"]))  $artisan->setDomicile($data["exploitantDomicile"]);

        /*******       FORMATION PROFESSIONNELLE      ******/
        if(isset($data["formationNiveauEtude"]))  $artisan->setFormationNiveauEtude($data["formationNiveauEtude"]);
        if(isset($data["formationClass"]))  $artisan->setFormationClass($data["formationClass"]);
        if(isset($data["formationDiplomeObtenu"]))  $artisan->setFormationDiplomeObtenu($data["formationDiplomeObtenu"]);
        if(isset($data["formationApprenMetierNiveau"]))  $artisan->setFormationApprenMetierNiveau($data["formationApprenMetierNiveau"]);
        if(isset($data["formationApprenMetierDiplomeObtenu"]))  $artisan->setFormationApprenMetierDiplomeObtenu($data["formationApprenMetierDiplomeObtenu"]);
        if(isset($data["exploitantEtatCivil"]))  $artisan->setExploitantEtatCivil($data["exploitantEtatCivil"]);
        if(isset($data["formationApprenMetier"]))  $artisan->setFormationApprenMetier($data["formationApprenMetier"]);
        if(isset($data["formationApprenMetierNiveau"]))  $artisan->setFormationApprenMetierNiveau($data["formationApprenMetierNiveau"]);
        if(isset($data["formationApprenMetierDiplomeObtenu"]))  $artisan->setFormationApprenMetierDiplomeObtenu($data["formationApprenMetierDiplomeObtenu"]);
        if(isset($data["formationApprenMetierCNMCI"]))  $artisan->setFormationApprenMetierCNMCI($data["formationApprenMetierCNMCI"]);
        if(isset($data["formationApprenMetierTypeCNMCI"]))  $artisan->setFormationApprenMetierTypeCNMCI($data["formationApprenMetierTypeCNMCI"]);

        /******************** ETABLISSEMENT ********************/
        if(isset($data["principalActiviteEtabl"]))  $artisan->setCompanyMainActivity($data["principalActiviteEtabl"]);
        if(isset($data["activiteSecondaireEtabl"]))  $artisan->setCompanySecondaryActivity($data["activiteSecondaireEtabl"]);
        if(isset($data["raisonSocialEtabl"]))  $artisan->setCompanyName($data["raisonSocialEtabl"]);
        if(isset($data["sigleEnseigneEtabl"]))  $artisan->setCompanySigle($data["sigleEnseigneEtabl"]);
        if(isset($data["dateDebutActiviteEtabl"]))  $artisan->setCompanyStartingDate(new \DateTime($data["dateDebutActiviteEtabl"]));
        if(isset($data["identifiantCNPSEtabl"]))  $artisan->setIdentifiantCnps($data["identifiantCNPSEtabl"]);
        if(isset($data["regimeFiscalEtabl"]))  $artisan->setCompanyFiscalRegime($data["regimeFiscalEtabl"]);
        if(isset($data["typeEntrepriseEtabl"]))  $artisan->setTypeCompany($data["typeEntrepriseEtabl"]);
        if(isset($data["numCompteContribuableEtabl"]))  $artisan->setNumCompteContribuableEtabl($data["numCompteContribuableEtabl"]);
        if(isset($data["addressPostalEtabl"]))  $artisan->setCompanyAdressPostal($data["addressPostalEtabl"]);
        if(isset($data["TelEtabl"]))  $artisan->setCompanyTel($data["TelEtabl"]);
        if(isset($data["faxEtabl"]))  $artisan->setCompanyFax($data["faxEtabl"]);
        if(isset($data["communeEtabl"]))  $artisan->setCompanyCommune($data["communeEtabl"]);
        if(isset($data["spEtabl"]))  $artisan->setCompanySp($data["spEtabl"]);
        if(isset($data["quartEtabl"]))  $artisan->setCompanyQuartier($data["quartEtabl"]);
        if(isset($data["villageEtabl"]))  $artisan->setCompanyVillage($data["villageEtabl"]);
        if(isset($data["lotEtabl"]))  $artisan->setCompanyLotNum($data["lotEtabl"]);
        if(isset($data["ilotEtabl"]))  $artisan->setCompanyILotNum($data["ilotEtabl"]);
        if(isset($data["effectifSalarieEtablFemme"]))  $artisan->setCompanyTotalWomen($data["effectifSalarieEtablFemme"]);
        if(isset($data["effectifSalarieEtablHomme"]))  $artisan->setCompanyTotalMen($data["effectifSalarieEtablHomme"]);
        if(isset($data["effectifApprenantEtablFemme"]))  $artisan->setCompanyTotalWomenApprentis($data["effectifApprenantEtablFemme"]);
        if(isset($data["effectifApprenantEtablHomme"]))  $artisan->setCompanyTotalMenApprentis($data["effectifApprenantEtablHomme"]);

        /****  PERSONNE POUVANT ENGAGER L'ENTREPRISE ****/
        if(isset($data["referantNom"]))  $artisan->setReprLastName($data["referantNom"]);
        if(isset($data["referantPrenom"]))  $artisan->setReprFirstName($data["referantPrenom"]);
        if(isset($data["referantDateNais"]))  $artisan->setReprDateNais(new \DateTime($data["referantDateNais"]));
        if(isset($data["referantLieuNais"]))  $artisan->setReprLieuNais($data["referantLieuNais"]);
        if(isset($data["referantEtatCivil"]))  $artisan->setReprEtatCivil($data["referantEtatCivil"]);
        if(isset($data["referantDomicile"]))  $artisan->setReprDomicile($data["referantDomicile"]);
        if(isset($data["referantSex"]))  $artisan->setReprSex($data["referantSex"]);
        if(isset($data["referantTypeDoc"]))  $artisan->setReprIDType($data["referantTypeDoc"]);
        if(isset($data["referantTypeDocAutre"]))  $artisan->setReprIDType($data["referantTypeDocAutre"]);
        if(isset($data["referantNumDoc"]))  $artisan->setReprIDNum($data["referantNumDoc"]);
        if(isset($data["referantDocLieuDelivrance"]))  $artisan->setReprIDDeliveryPlace($data["referantDocLieuDelivrance"]);
        if(isset($data["referantDocDateDelivrance"]))  $artisan->setReprIDDeliveryDate(new \DateTime($data["referantDocDateDelivrance"]));
        if(isset($data["referantTel"]))  $artisan->setReprTel($data["referantTel"]);
        if(isset($data["referantEmail"]))  $artisan->setReprEmail($data["referantEmail"]);
        return $artisan;
    }

}
