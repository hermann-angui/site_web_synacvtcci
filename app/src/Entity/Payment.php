<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: '`payment`')]
#[ORM\HasLifecycleCallbacks()]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $receipt_file;

    #[ORM\Column(type: 'string')]
    private ?string $type;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $montant;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $reference;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $target;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $receipt_number;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $operateur;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $code_payment_operateur;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $modified_at;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    private ?Member $payment_for = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    private ?User $user = null;

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

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Payment
     */
    public function setType(string $type): Payment
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMontant(): ?int
    {
        return $this->montant;
    }

    /**
     * @param int|null $montant
     * @return Payment
     */
    public function setMontant(?int $montant): Payment
    {
        $this->montant = $montant;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param string|null $reference
     * @return Payment
     */
    public function setReference(?string $reference): Payment
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReceiptNumber(): ?string
    {
        return $this->receipt_number;
    }

    /**
     * @param string|null $receipt_number
     * @return Payment
     */
    public function setReceiptNumber(?string $receipt_number): Payment
    {
        $this->receipt_number = $receipt_number;
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
     * @param string|null $status
     * @return Payment
     */
    public function setStatus(?string $status): Payment
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOperateur(): ?string
    {
        return $this->operateur;
    }

    /**
     * @param string|null $operateur
     * @return Payment
     */
    public function setOperateur(?string $operateur): Payment
    {
        $this->operateur = $operateur;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodePaymentOperateur(): ?string
    {
        return $this->code_payment_operateur;
    }

    /**
     * @param string|null $code_payment_operateur
     * @return Payment
     */
    public function setCodePaymentOperateur(?string $code_payment_operateur): Payment
    {
        $this->code_payment_operateur = $code_payment_operateur;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getReceiptFile(): ?string
    {
        return $this->receipt_file;
    }

    /**
     * @param string $receipt_file
     * @return Payment
     */
    public function setReceiptFile(?string $receipt_file): Payment
    {
        $this->receipt_file = $receipt_file;
        return $this;
    }

    public function getPaymentFor(): ?Member
    {
        return $this->payment_for;
    }

    public function setPaymentFor(?Member $payment_for): self
    {
        $this->payment_for = $payment_for;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    /**
     * @param string|null $target
     * @return Payment
     */
    public function setTarget(?string $target): Payment
    {
        $this->target = $target;
        return $this;
    }


}
