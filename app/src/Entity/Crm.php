<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: CrmRepository::class)]
#[ORM\Table(name: '`crm`')]
#[ORM\HasLifecycleCallbacks()]
class Crm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: Member::class, cascade: ["remove", "persist"], orphanRemoval: true)]
    private ?Collection $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(): ?Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
            $member->setCrm($this);
        }

        return $this;
    }

    public function removeMember(Member $member): self
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getCrm() === $this) {
                $member->setCrm(null);
            }
        }

        return $this;
    }
}