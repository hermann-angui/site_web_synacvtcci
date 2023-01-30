<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class CompanyDto
{
    private $first_name;

    private $phone_number;

    private $address;

    private $status;

    private $date_created = null;

}