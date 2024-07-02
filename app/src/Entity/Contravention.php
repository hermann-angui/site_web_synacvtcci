<?php

namespace App\Entity;

use App\Repository\ContraventionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContraventionRepository::class)]
#[ORM\Table(name: '`contravention`')]
#[ORM\HasLifecycleCallbacks()]
class Contravention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contravention_numero;

    #[ORM\Column(type: 'string')]
    private $nom_controleur;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $infraction_type;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $infraction_date;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $infraction_lieu;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contrevenant_nom;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contrevenant_numero_permis;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contrevenant_telephone;

    #[ORM\Column(type: 'json')]
    private array $contrevenant_activites = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $vehicule_immatriculation;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $vehicule_marque;

    #[ORM\Column(type: 'text', nullable: true)]
    private $rapport_agent;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $is_paid;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $montant_contravention;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $montant_enrolement_cnmci;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $modified_at;

    #[ORM\OneToOne(inversedBy: 'contravention', cascade: ['persist', 'remove'])]
    private ?Artisan $target = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->modified_at = new \DateTime();
    }


    /**
     * Prepersist gets triggered on Insert
     * @ORM\PrePersist
     */
    public function updatedTimestamps()
    {
        if ($this->created_at == null) {
            $this->created_at = new \DateTime('now');
        }
        $this->modified_at =  new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTime $createAt): self
    {
        $this->created_at = $createAt;

        return $this;
    }

    public function setModifiedAt(?\DateTime $modified_at): self
    {
        $this->modified_at = $modified_at;

        return $this;
    }

    public function getModifiedAt(): ?\DateTime
    {
        return $this->modified_at;
    }

    public function getTarget(): ?Artisan
    {
        return $this->target;
    }

    public function setTarget(?Artisan $target): self
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMontantEnrolementCnmci()
    {
        return $this->montant_enrolement_cnmci;
    }

    /**
     * @param mixed $montant_enrolement_cnmci
     * @return Contravention
     */
    public function setMontantEnrolementCnmci($montant_enrolement_cnmci)
    {
        $this->montant_enrolement_cnmci = $montant_enrolement_cnmci;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNomControleur()
    {
        return $this->nom_controleur;
    }

    /**
     * @param mixed $nom_controleur
     * @return Contravention
     */
    public function setNomControleur($nom_controleur)
    {
        $this->nom_controleur = $nom_controleur;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInfractionType()
    {
        return $this->infraction_type;
    }

    /**
     * @param mixed $infraction_type
     * @return Contravention
     */
    public function setInfractionType($infraction_type)
    {
        $this->infraction_type = $infraction_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInfractionDate()
    {
        return $this->infraction_date;
    }

    /**
     * @param mixed $infraction_date
     * @return Contravention
     */
    public function setInfractionDate($infraction_date)
    {
        $this->infraction_date = $infraction_date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInfractionLieu()
    {
        return $this->infraction_lieu;
    }

    /**
     * @param mixed $infraction_lieu
     * @return Contravention
     */
    public function setInfractionLieu($infraction_lieu)
    {
        $this->infraction_lieu = $infraction_lieu;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContrevenantNom()
    {
        return $this->contrevenant_nom;
    }

    /**
     * @param mixed $contrevenant_nom
     * @return Contravention
     */
    public function setContrevenantNom($contrevenant_nom)
    {
        $this->contrevenant_nom = $contrevenant_nom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContrevenantNumeroPermis()
    {
        return $this->contrevenant_numero_permis;
    }

    /**
     * @param mixed $contrevenant_numero_permis
     * @return Contravention
     */
    public function setContrevenantNumeroPermis($contrevenant_numero_permis)
    {
        $this->contrevenant_numero_permis = $contrevenant_numero_permis;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContrevenantTelephone()
    {
        return $this->contrevenant_telephone;
    }

    /**
     * @param mixed $contrevenant_telephone
     * @return Contravention
     */
    public function setContrevenantTelephone($contrevenant_telephone)
    {
        $this->contrevenant_telephone = $contrevenant_telephone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVehiculeImmatriculation()
    {
        return $this->vehicule_immatriculation;
    }

    /**
     * @param mixed $vehicule_immatriculation
     * @return Contravention
     */
    public function setVehiculeImmatriculation($vehicule_immatriculation)
    {
        $this->vehicule_immatriculation = $vehicule_immatriculation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVehiculeMarque()
    {
        return $this->vehicule_marque;
    }

    /**
     * @param mixed $vehicule_marque
     * @return Contravention
     */
    public function setVehiculeMarque($vehicule_marque)
    {
        $this->vehicule_marque = $vehicule_marque;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsPaid()
    {
        return $this->is_paid;
    }

    /**
     * @param mixed $is_paid
     * @return Contravention
     */
    public function setIsPaid($is_paid)
    {
        $this->is_paid = $is_paid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMontantContravention()
    {
        return $this->montant_contravention;
    }

    /**
     * @param mixed $montant_contravention
     * @return Contravention
     */
    public function setMontantContravention($montant_contravention)
    {
        $this->montant_contravention = $montant_contravention;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRapportAgent()
    {
        return $this->rapport_agent;
    }

    /**
     * @param mixed $rapport_agent
     * @return Contravention
     */
    public function setRapportAgent($rapport_agent)
    {
        $this->rapport_agent = $rapport_agent;
        return $this;
    }

    public function getContrevenantActivites(): array
    {
        return $this->contrevenant_activites;
    }

    public function setContrevenantActivites(array $contrevenant_activites): Contravention
    {
        $this->contrevenant_activites = $contrevenant_activites;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContraventionNumero()
    {
        return $this->contravention_numero;
    }

    /**
     * @param mixed $contravention_numero
     * @return Contravention
     */
    public function setContraventionNumero($contravention_numero)
    {
        $this->contravention_numero = $contravention_numero;
        return $this;
    }

}
