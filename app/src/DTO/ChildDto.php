<?php

namespace App\DTO;

use App\Entity\Child;
use App\Entity\Member;

class ChildDto
{
    private $id;

    private $first_name;

    private $last_name;

    private $sex;

    private $lieu_naissance;

    private $created_at;

    private $modified_at;

    private ?MemberRequestDto $parent = null;


    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->modified_at = new \DateTime();
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

    public function getParent(): ?MemberRequestDto
    {
        return $this->parent;
    }

    public function setParent(?MemberRequestDto $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
     * @return Child
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $last_name
     * @return Child
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param mixed $sex
     * @return Child
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLieuNaissance()
    {
        return $this->lieu_naissance;
    }

    /**
     * @param mixed $lieu_naissance
     * @return Child
     */
    public function setLieuNaissance($lieu_naissance)
    {
        $this->lieu_naissance = $lieu_naissance;
        return $this;
    }


}
