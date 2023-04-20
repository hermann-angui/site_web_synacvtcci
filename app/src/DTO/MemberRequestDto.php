<?php

namespace App\DTO;

use App\Entity\Child;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class MemberRequestDto implements PasswordAuthenticatedUserInterface
{
    private ?int $id = null;

    private ?string $email = null;

    private ?string $firstName = null;

    private ?string $lastName = null;

    private ?string $matricule = null;

    private ?string $titre = null;

    private ?\DateTimeInterface $subscription_date = null;

    private ?\DateTimeInterface $subscription_expire_date = null;

    private ?string $sex = null;

    private ?File $photo = null;

    private ?File $cardPhoto = null;

    private ?\DateTimeInterface $date_of_birth = null;

    private ?string $birth_city = null;

    private ?string $drivingLicenseNumber = null;

    private ?string $IdNumber = null;

    private ?string $IdType = null;

    private ?string $country = null;

    private ?string $nationality = null;

    private ?string $city = null;

    private ?string $commune = null;

    private ?string $quartier = null;

    private ?string $mobile = null;

    private ?string $phone = null;

    private ?string $whatsapp = null;

    private ?string $company = null;

    private ?string $partner_first_name = null;

    private ?string $partner_last_name = null;

    private ?string $status = 'PENDING';

    private ?File $photoPiece_front = null;

    private ?File $photoPiece_back = null;

    private ?File $photoPermis_front = null;

    private ?File $photoPermis_back = null;

    private $roles = [];

    private ?string $password;

    private ?string $plain_password;

    private ?\DateTimeInterface $created_at;

    private ?\DateTimeInterface $modified_at;

    private Collection $children;

    public function __construct()
    {
        $this->created_at = new \DateTime('now');
        $this->modified_at = new \DateTime('now');
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

    public function getPhoto(): ?File
    {
        return $this->photo;
    }

    public function setPhoto(?File $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCardPhoto(): ?File
    {
        return $this->cardPhoto;
    }

    public function setCardPhoto(?File $cardPhoto): self
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
     * @return MemberRequestDto
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
     * @return MemberRequestDto
     */
    public function setModifiedAt(?\DateTimeInterface $modified_at): self
    {
        $this->modified_at = $modified_at;
        return $this;
    }


    /**
     * @return string
     */
    public function getPhotoPieceFront(): ?File
    {
        return $this->photoPiece_front;
    }

    /**
     * @param File $photoPiece_front
     * @return MemberRequestDto
     */
    public function setPhotoPieceFront(?File $photoPiece_front): self
    {
        $this->photoPiece_front = $photoPiece_front;
        return $this;
    }

    /**
     * @return File
     */
    public function getPhotoPieceBack(): ?File
    {
        return $this->photoPiece_back;
    }

    /**
     * @param File $photoPiece_back
     * @return MemberRequestDto
     */
    public function setPhotoPieceBack(?File $photoPiece_back): self
    {
        $this->photoPiece_back = $photoPiece_back;
        return $this;
    }

    /**
     * @return File
     */
    public function getPhotoPermisFront() : ?File
    {
        return $this->photoPermis_front;
    }

    /**
     * @param File $photoPermis_front
     * @return MemberRequestDto
     */
    public function setPhotoPermisFront(?File $photoPermis_front): self
    {
        $this->photoPermis_front = $photoPermis_front;
        return $this;
    }

    /**
     * @return File
     */
    public function getPhotoPermisBack() : ?File
    {
        return $this->photoPermis_back;
    }

    /**
     * @param File $photoPermis_back
     * @return MemberRequestDto
     */
    public function setPhotoPermisBack(?File $photoPermis_back): self
    {
        $this->photoPermis_back = $photoPermis_back;
        return $this;
    }

    public static function getTitres()
    {
        return  [
            'Chauffeur' => 'Chauffeur',
            'Secrétaire général' => 'Secrétaire général',
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
     * @return MemberRequestDto
     */
    public function setNationality(?string $nationality): MemberRequestDto
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
     * @return MemberRequestDto
     */
    public function setQuartier(?string $quartier): MemberRequestDto
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
     * @return MemberRequestDto
     */
    public function setWhatsapp(?string $whatsapp): MemberRequestDto
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
     * @return MemberRequestDto
     */
    public function setCompany(?string $company): MemberRequestDto
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return Collection<int, ChildDto>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(ChildDto $childDto): self
    {
        if (!$this->children->contains($childDto)) {
            $this->children[] = $childDto;
            $childDto->setParent($this);
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
     * @return MemberRequestDto
     */
    public function setPartnerFirstName(?string $partner_first_name): MemberRequestDto
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
     * @return MemberRequestDto
     */
    public function setPartnerLastName(?string $partner_last_name): MemberRequestDto
    {
        $this->partner_last_name = $partner_last_name;
        return $this;
    }

    /**
     * @param string|null $status
     * @return MemberRequestDto
     */
    public function setStatus(?string $status): MemberRequestDto
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
