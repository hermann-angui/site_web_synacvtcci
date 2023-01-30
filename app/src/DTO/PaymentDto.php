<?php

namespace App\DTO;

use App\Dto\UserDto;

/**
 *
 */
class PaymentDto
{
    private string $code;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $amount;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $payment_mode;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $payment_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modified_at;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
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

}
