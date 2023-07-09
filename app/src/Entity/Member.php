<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\Table(name: '`member`')]
#[UniqueEntity(fields: ['matricule','drivingLicenseNumber','IdNumber'], message: 'There is already an account with this email')]
class Member implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $matricule = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $subscription_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $subscription_expire_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sex = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $cardPhoto = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_of_birth = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birth_city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $drivingLicenseNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $IdNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $IdType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nationality = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commune = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $quartier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mobile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

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
    public function setNationality(?string $nationality): Member
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
    public function setQuartier(?string $quartier): Member
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
    public function setWhatsapp(?string $whatsapp): Member
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
    public function setCompany(?string $company): Member
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
    public function setPartnerFirstName(?string $partner_first_name): Member
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
    public function setPartnerLastName(?string $partner_last_name): Member
    {
        $this->partner_last_name = $partner_last_name;
        return $this;
    }

    /**
     * @param string|null $status
     * @return Member
     */
    public function setStatus(?string $status): Member
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

}
