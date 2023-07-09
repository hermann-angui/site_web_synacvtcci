<?php

namespace App\Entity;

use App\Repository\ArtisanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: ArtisanRepository::class)]
#[ORM\Table(name: '`artisan`')]
#[UniqueEntity(fields: ['matricule'], message: 'There is already an account with this email')]
class Artisan implements UserInterface, PasswordAuthenticatedUserInterface
{
    /******* FORMATION PROFESSIONNELLE ********/
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;                        // Nom

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;                       // Prénoms

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_of_birth = null;        // Date naissance

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birth_city = null;                       // Lieu naissance

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nationality = null;                      // Nationalité

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sex = null;                               // Sexe

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $IdType = null;                            // Type de document

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $IdNumber = null;                          // Numero du document

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?string $IdDeliveryDate = null;                  // Numéro du document

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;                           // Etat civil

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mobile = null;                          // Telephone

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;                           // Telephone


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;                         // Email

    /******************* FORMATION PROFESSIONNELLE *******************/
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $studyLevel = null;                     // Niveau d'études

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $studyClass = null;                     // Précisez la classe

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $studyDegree = null;                    //  diplome obtenu

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jobTraining = null;                    //  Apprentissage du métier

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preciseLevel = null;                //  Précisez le niveau

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preciseDegreeObtain= null;          //  Précisez le diplome obtenu

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cnmciDegree= null;          //  diplome delivré par le cnmci

    /******************* ETABLISSEMENT *******************/

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyMainActivity = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companySecondaryActivity = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyName = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companySigle = null;
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?string $companyStartingDate = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeCompany = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyFiscalRegime = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $identifiantCnps = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyAdressPostal = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyTel = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyFax = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyDepartement = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyCommune = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companySp = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyQuartier = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyVillage = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyLotNum = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyILotNum = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyTotalMen = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyTotalWomen = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyTotalMenApprentis = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyTotalWomenApprentis = null;

    /***  PERSONNE POUVANT ENGAGER l'ENTREPRISE ***/
    private ?string $reprLastName = null;
    private ?string $reprFirstName = null;
    private ?string $reprQualification= null;
    private ?string $reprSex = null;
    private ?string $reprIDType = null;
    private ?string $reprIDNum = null;
    private ?string $reprIDDeliveryPlace = null;
    private ?string $reprIDDeliveryDate = null;
    private ?string $reprTitle = null;
    private ?string $reprTel = null;
    private ?string $reprEmail = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $drivingLicenseNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commune = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $quartier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $whatsapp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $partner_first_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $partner_last_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = 'PENDING';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photoPiece_front = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photoPiece_back = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photoPermis_front = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photoPermis_back = null;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password;

    private ?string $plain_password;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $modified_at;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $matricule = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $subscription_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $subscription_expire_date = null;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $cardPhoto = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Child::class, cascade: ["remove", "persist"], orphanRemoval: true)]
    private Collection $children;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->modified_at = new \DateTime();
        $this->children = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(?string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getSubscriptionDate(): ?\DateTimeInterface
    {
        return $this->subscription_date;
    }

    public function setSubscriptionDate(?\DateTimeInterface $subscription_date): self
    {
        $this->subscription_date = $subscription_date;

        return $this;
    }

    public function getSubscriptionExpireDate(): ?\DateTimeInterface
    {
        return $this->subscription_expire_date;
    }

    public function setSubscriptionExpireDate(?\DateTimeInterface $subscription_expire_date): self
    {
        $this->subscription_expire_date = $subscription_expire_date;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(?string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->date_of_birth;
    }

    public function setDateOfBirth(?\DateTime $date_of_birth): self
    {
        $this->date_of_birth = $date_of_birth;

        return $this;
    }

    public function getBirthCity(): ?string
    {
        return $this->birth_city;
    }

    public function setBirthCity(?string $birth_city): self
    {
        $this->birth_city = $birth_city;

        return $this;
    }

    public function getDrivingLicenseNumber(): ?string
    {
        return $this->drivingLicenseNumber;
    }

    public function setDrivingLicenseNumber(?string $drivingLicenseNumber): self
    {
        $this->drivingLicenseNumber = $drivingLicenseNumber;

        return $this;
    }

    public function getIdNumber(): ?string
    {
        return $this->IdNumber;
    }

    public function setIdNumber(?string $IdNumber): self
    {
        $this->IdNumber = $IdNumber;

        return $this;
    }

    public function getIdType(): ?string
    {
        return $this->IdType;
    }

    public function setIdType(?string $IdType): self
    {
        $this->IdType = $IdType;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(?string $commune): self
    {
        $this->commune = $commune;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plain_password = null;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plain_password;
    }

    /**
     * @param string $plain_password
     * @return User
     */
    public function setPlainPassword(?string $plain_password) : self
    {
        $this->plain_password = $plain_password;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCardPhoto(): ?string
    {
        return $this->cardPhoto;
    }

    public function setCardPhoto(?string $cardPhoto): self
    {
        $this->cardPhoto = $cardPhoto;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    /**
     * @param \DateTimeInterface|null $created_at
     * @return Member
     */
    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modified_at;
    }

    /**
     * @param \DateTimeInterface|null $modified_at
     * @return Member
     */
    public function setModifiedAt(?\DateTimeInterface $modified_at): self
    {
        $this->modified_at = $modified_at;
        return $this;
    }


    /**
     * @return string
     */
    public function getPhotoPieceFront(): ?string
    {
        return $this->photoPiece_front;
    }

    /**
     * @param string $photoPiece_front
     * @return Member
     */
    public function setPhotoPieceFront(?string $photoPiece_front): self
    {
        $this->photoPiece_front = $photoPiece_front;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoPieceBack(): ?string
    {
        return $this->photoPiece_back;
    }

    /**
     * @param string $photoPiece_back
     * @return Member
     */
    public function setPhotoPieceBack(?string $photoPiece_back): self
    {
        $this->photoPiece_back = $photoPiece_back;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoPermisFront() : ?string
    {
        return $this->photoPermis_front;
    }

    /**
     * @param string $photoPermis_front
     * @return Member
     */
    public function setPhotoPermisFront(?string $photoPermis_front): self
    {
        $this->photoPermis_front = $photoPermis_front;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoPermisBack() : ?string
    {
        return $this->photoPermis_back;
    }

    /**
     * @param string $photoPermis_back
     * @return Member
     */
    public function setPhotoPermisBack(?string $photoPermis_back): self
    {
        $this->photoPermis_back = $photoPermis_back;
        return $this;
    }

    public static function getTitres()
    {
        return  [
            'Chauffeur' => 'Chauffeur',
            'Secrétaire général' => 'Secrétaire général',
            "Secrétaire de section d'Abobo" => "Secrétaire de section d'Abobo",
            "Secrétaire de section d'Adjamé" => "Secrétaire de section d'Adjamé",
            "Secrétaire de section de Port-Bouët" => "Secrétaire de section de Port-Bouët",
            "Secrétaire de section de Bingerville" => "Secrétaire de section de Bingerville",
            "Adjoint au secrétaire de section d'Abobo" => "Adjoint au secrétaire de section d'Abobo",
            "Secrétaire de section de Marcory" => "Secrétaire de section de Marcory",
            "Conseillé du secrétaire général" => "Conseillé du secrétaire général",
            "Conseillé en communication du secrétaire général" => "Conseillé en communication du secrétaire général",
            'SN au contrôle informatique' => 'SN au contrôle informatique',
            'SN aux finances' => 'SN aux finances',
            'SN à l’administration' => 'SN à l’administration',
            'Secrétaire général adjoint' => 'Secrétaire général adjoint',
            'SN à l’organisation' => 'SN à l’organisation',
            'SN à la communication' => 'SN à la communication',
            'SN à la formation' => 'SN à la formation',
            'SN chargé  des applications' => 'SN chargé  des applications',
            'SN Adjointe aux finances' => 'SN Adjointe aux finances',
            'Chef de section Marcory' => "Chef de section Marcory",
            'Conseillé' => "Conseillé",
        ];
    }

    /**
     * @return string|null
     */
    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    /**
     * @param string|null $nationality
     * @return Member
     */
    public function setNationality(?string $nationality): Artisan
    {
        $this->nationality = $nationality;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getQuartier(): ?string
    {
        return $this->quartier;
    }

    /**
     * @param string|null $quartier
     * @return Member
     */
    public function setQuartier(?string $quartier): Artisan
    {
        $this->quartier = $quartier;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    /**
     * @param string|null $whatsapp
     * @return Member
     */
    public function setWhatsapp(?string $whatsapp): Artisan
    {
        $this->whatsapp = $whatsapp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @param string|null $company
     * @return Member
     */
    public function setCompany(?string $company): Artisan
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return Collection<int, Child>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Child $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Child $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPartnerFirstName(): ?string
    {
        return $this->partner_first_name;
    }

    /**
     * @param string|null $partner_first_name
     * @return Member
     */
    public function setPartnerFirstName(?string $partner_first_name): Artisan
    {
        $this->partner_first_name = $partner_first_name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPartnerLastName(): ?string
    {
        return $this->partner_last_name;
    }

    /**
     * @param string|null $partner_last_name
     * @return Member
     */
    public function setPartnerLastName(?string $partner_last_name): Artisan
    {
        $this->partner_last_name = $partner_last_name;
        return $this;
    }

    /**
     * @param string|null $status
     * @return Member
     */
    public function setStatus(?string $status): Artisan
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getIdDeliveryDate(): ?string
    {
        return $this->IdDeliveryDate;
    }

    /**
     * @param string|null $IdDeliveryDate
     * @return Artisan
     */
    public function setIdDeliveryDate(?string $IdDeliveryDate): Artisan
    {
        $this->IdDeliveryDate = $IdDeliveryDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStudyLevel(): ?string
    {
        return $this->studyLevel;
    }

    /**
     * @param string|null $studyLevel
     * @return Artisan
     */
    public function setStudyLevel(?string $studyLevel): Artisan
    {
        $this->studyLevel = $studyLevel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStudyClass(): ?string
    {
        return $this->studyClass;
    }

    /**
     * @param string|null $studyClass
     * @return Artisan
     */
    public function setStudyClass(?string $studyClass): Artisan
    {
        $this->studyClass = $studyClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStudyDegree(): ?string
    {
        return $this->studyDegree;
    }

    /**
     * @param string|null $studyDegree
     * @return Artisan
     */
    public function setStudyDegree(?string $studyDegree): Artisan
    {
        $this->studyDegree = $studyDegree;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJobTraining(): ?string
    {
        return $this->jobTraining;
    }

    /**
     * @param string|null $jobTraining
     * @return Artisan
     */
    public function setJobTraining(?string $jobTraining): Artisan
    {
        $this->jobTraining = $jobTraining;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreciseLevel(): ?string
    {
        return $this->preciseLevel;
    }

    /**
     * @param string|null $preciseLevel
     * @return Artisan
     */
    public function setPreciseLevel(?string $preciseLevel): Artisan
    {
        $this->preciseLevel = $preciseLevel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreciseDegreeObtain(): ?string
    {
        return $this->preciseDegreeObtain;
    }

    /**
     * @param string|null $preciseDegreeObtain
     * @return Artisan
     */
    public function setPreciseDegreeObtain(?string $preciseDegreeObtain): Artisan
    {
        $this->preciseDegreeObtain = $preciseDegreeObtain;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCnmciDegree(): ?string
    {
        return $this->cnmciDegree;
    }

    /**
     * @param string|null $cnmciDegree
     * @return Artisan
     */
    public function setCnmciDegree(?string $cnmciDegree): Artisan
    {
        $this->cnmciDegree = $cnmciDegree;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyMainActivity(): ?string
    {
        return $this->companyMainActivity;
    }

    /**
     * @param string|null $companyMainActivity
     * @return Artisan
     */
    public function setCompanyMainActivity(?string $companyMainActivity): Artisan
    {
        $this->companyMainActivity = $companyMainActivity;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanySecondaryActivity(): ?string
    {
        return $this->companySecondaryActivity;
    }

    /**
     * @param string|null $companySecondaryActivity
     * @return Artisan
     */
    public function setCompanySecondaryActivity(?string $companySecondaryActivity): Artisan
    {
        $this->companySecondaryActivity = $companySecondaryActivity;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @param string|null $companyName
     * @return Artisan
     */
    public function setCompanyName(?string $companyName): Artisan
    {
        $this->companyName = $companyName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanySigle(): ?string
    {
        return $this->companySigle;
    }

    /**
     * @param string|null $companySigle
     * @return Artisan
     */
    public function setCompanySigle(?string $companySigle): Artisan
    {
        $this->companySigle = $companySigle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyStartingDate(): ?string
    {
        return $this->companyStartingDate;
    }

    /**
     * @param string|null $companyStartingDate
     * @return Artisan
     */
    public function setCompanyStartingDate(?string $companyStartingDate): Artisan
    {
        $this->companyStartingDate = $companyStartingDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTypeCompany(): ?string
    {
        return $this->typeCompany;
    }

    /**
     * @param string|null $typeCompany
     * @return Artisan
     */
    public function setTypeCompany(?string $typeCompany): Artisan
    {
        $this->typeCompany = $typeCompany;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyFiscalRegime(): ?string
    {
        return $this->companyFiscalRegime;
    }

    /**
     * @param string|null $companyFiscalRegime
     * @return Artisan
     */
    public function setCompanyFiscalRegime(?string $companyFiscalRegime): Artisan
    {
        $this->companyFiscalRegime = $companyFiscalRegime;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdentifiantCnps(): ?string
    {
        return $this->identifiantCnps;
    }

    /**
     * @param string|null $identifiantCnps
     * @return Artisan
     */
    public function setIdentifiantCnps(?string $identifiantCnps): Artisan
    {
        $this->identifiantCnps = $identifiantCnps;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyAdressPostal(): ?string
    {
        return $this->companyAdressPostal;
    }

    /**
     * @param string|null $companyAdressPostal
     * @return Artisan
     */
    public function setCompanyAdressPostal(?string $companyAdressPostal): Artisan
    {
        $this->companyAdressPostal = $companyAdressPostal;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyTel(): ?string
    {
        return $this->companyTel;
    }

    /**
     * @param string|null $companyTel
     * @return Artisan
     */
    public function setCompanyTel(?string $companyTel): Artisan
    {
        $this->companyTel = $companyTel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyFax(): ?string
    {
        return $this->companyFax;
    }

    /**
     * @param string|null $companyFax
     * @return Artisan
     */
    public function setCompanyFax(?string $companyFax): Artisan
    {
        $this->companyFax = $companyFax;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyDepartement(): ?string
    {
        return $this->companyDepartement;
    }

    /**
     * @param string|null $companyDepartement
     * @return Artisan
     */
    public function setCompanyDepartement(?string $companyDepartement): Artisan
    {
        $this->companyDepartement = $companyDepartement;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyCommune(): ?string
    {
        return $this->companyCommune;
    }

    /**
     * @param string|null $companyCommune
     * @return Artisan
     */
    public function setCompanyCommune(?string $companyCommune): Artisan
    {
        $this->companyCommune = $companyCommune;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanySp(): ?string
    {
        return $this->companySp;
    }

    /**
     * @param string|null $companySp
     * @return Artisan
     */
    public function setCompanySp(?string $companySp): Artisan
    {
        $this->companySp = $companySp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyQuartier(): ?string
    {
        return $this->companyQuartier;
    }

    /**
     * @param string|null $companyQuartier
     * @return Artisan
     */
    public function setCompanyQuartier(?string $companyQuartier): Artisan
    {
        $this->companyQuartier = $companyQuartier;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyVillage(): ?string
    {
        return $this->companyVillage;
    }

    /**
     * @param string|null $companyVillage
     * @return Artisan
     */
    public function setCompanyVillage(?string $companyVillage): Artisan
    {
        $this->companyVillage = $companyVillage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyLotNum(): ?string
    {
        return $this->companyLotNum;
    }

    /**
     * @param string|null $companyLotNum
     * @return Artisan
     */
    public function setCompanyLotNum(?string $companyLotNum): Artisan
    {
        $this->companyLotNum = $companyLotNum;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyILotNum(): ?string
    {
        return $this->companyILotNum;
    }

    /**
     * @param string|null $companyILotNum
     * @return Artisan
     */
    public function setCompanyILotNum(?string $companyILotNum): Artisan
    {
        $this->companyILotNum = $companyILotNum;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyTotalMen(): ?string
    {
        return $this->companyTotalMen;
    }

    /**
     * @param string|null $companyTotalMen
     * @return Artisan
     */
    public function setCompanyTotalMen(?string $companyTotalMen): Artisan
    {
        $this->companyTotalMen = $companyTotalMen;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyTotalWomen(): ?string
    {
        return $this->companyTotalWomen;
    }

    /**
     * @param string|null $companyTotalWomen
     * @return Artisan
     */
    public function setCompanyTotalWomen(?string $companyTotalWomen): Artisan
    {
        $this->companyTotalWomen = $companyTotalWomen;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyTotalMenApprentis(): ?string
    {
        return $this->companyTotalMenApprentis;
    }

    /**
     * @param string|null $companyTotalMenApprentis
     * @return Artisan
     */
    public function setCompanyTotalMenApprentis(?string $companyTotalMenApprentis): Artisan
    {
        $this->companyTotalMenApprentis = $companyTotalMenApprentis;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyTotalWomenApprentis(): ?string
    {
        return $this->companyTotalWomenApprentis;
    }

    /**
     * @param string|null $companyTotalWomenApprentis
     * @return Artisan
     */
    public function setCompanyTotalWomenApprentis(?string $companyTotalWomenApprentis): Artisan
    {
        $this->companyTotalWomenApprentis = $companyTotalWomenApprentis;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprLastName(): ?string
    {
        return $this->reprLastName;
    }

    /**
     * @param string|null $reprLastName
     * @return Artisan
     */
    public function setReprLastName(?string $reprLastName): Artisan
    {
        $this->reprLastName = $reprLastName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprFirstName(): ?string
    {
        return $this->reprFirstName;
    }

    /**
     * @param string|null $reprFirstName
     * @return Artisan
     */
    public function setReprFirstName(?string $reprFirstName): Artisan
    {
        $this->reprFirstName = $reprFirstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprQualification(): ?string
    {
        return $this->reprQualification;
    }

    /**
     * @param string|null $reprQualification
     * @return Artisan
     */
    public function setReprQualification(?string $reprQualification): Artisan
    {
        $this->reprQualification = $reprQualification;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprSex(): ?string
    {
        return $this->reprSex;
    }

    /**
     * @param string|null $reprSex
     * @return Artisan
     */
    public function setReprSex(?string $reprSex): Artisan
    {
        $this->reprSex = $reprSex;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprIDType(): ?string
    {
        return $this->reprIDType;
    }

    /**
     * @param string|null $reprIDType
     * @return Artisan
     */
    public function setReprIDType(?string $reprIDType): Artisan
    {
        $this->reprIDType = $reprIDType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprIDNum(): ?string
    {
        return $this->reprIDNum;
    }

    /**
     * @param string|null $reprIDNum
     * @return Artisan
     */
    public function setReprIDNum(?string $reprIDNum): Artisan
    {
        $this->reprIDNum = $reprIDNum;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprIDDeliveryPlace(): ?string
    {
        return $this->reprIDDeliveryPlace;
    }

    /**
     * @param string|null $reprIDDeliveryPlace
     * @return Artisan
     */
    public function setReprIDDeliveryPlace(?string $reprIDDeliveryPlace): Artisan
    {
        $this->reprIDDeliveryPlace = $reprIDDeliveryPlace;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprIDDeliveryDate(): ?string
    {
        return $this->reprIDDeliveryDate;
    }

    /**
     * @param string|null $reprIDDeliveryDate
     * @return Artisan
     */
    public function setReprIDDeliveryDate(?string $reprIDDeliveryDate): Artisan
    {
        $this->reprIDDeliveryDate = $reprIDDeliveryDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprTitle(): ?string
    {
        return $this->reprTitle;
    }

    /**
     * @param string|null $reprTitle
     * @return Artisan
     */
    public function setReprTitle(?string $reprTitle): Artisan
    {
        $this->reprTitle = $reprTitle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprTel(): ?string
    {
        return $this->reprTel;
    }

    /**
     * @param string|null $reprTel
     * @return Artisan
     */
    public function setReprTel(?string $reprTel): Artisan
    {
        $this->reprTel = $reprTel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReprEmail(): ?string
    {
        return $this->reprEmail;
    }

    /**
     * @param string|null $reprEmail
     * @return Artisan
     */
    public function setReprEmail(?string $reprEmail): Artisan
    {
        $this->reprEmail = $reprEmail;
        return $this;
    }


}
