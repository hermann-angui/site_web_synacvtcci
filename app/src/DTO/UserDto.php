<?php

namespace App\DTO;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Date;

class UserDto
{
    private string $email;

    private $roles = [];

    private $password;

    private $firstname;

    private $lastname;

    private $place_of_birth;

    private $date_of_birth;

    private $nationality;

    private $sex;

    private $phone_number;

    private $address;

    private $photo;

    private $type;

    private $status;

    private $created_at;

    private $modified_at;

    private Collection $membershipFeePayments;

}
