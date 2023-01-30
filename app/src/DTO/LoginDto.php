<?php

namespace App\DTO;


use Symfony\Component\Form\Extension\Validator\Constraints as Assert;
class LoginDto
{
    #[Assert\Password]
    #[Assert\Length(min: 8)]
    #[Assert\NotBlank]
    private $password;

    #[Assert\Email]
    private $email;

    /**
     * @return mixed
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }


}