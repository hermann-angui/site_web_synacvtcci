<?php

namespace App\Entity;


use App\Repository\CrmRepository;
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

    #[ORM\OneToMany(mappedBy: 'artisan', targetEntity: Artisan::class, cascade: ["remove", "persist"], orphanRemoval: true)]
    private ?Collection $artisans;

    public function __construct()
    {
        $this->artisans = new ArrayCollection();
    }

    /**
     * @return Collection<int, Artisan>
     */
    public function getArtisans(): ?Collection
    {
        return $this->artisans;
    }

    public function addArtisan(Artisan $artisan): self
    {
        if (!$this->artisans->contains($artisan)) {
            $this->artisans[] = $artisan;
            $artisan->setCrm($this);
        }

        return $this;
    }

    public function removeArtisan(Artisan $artisan): self
    {
        if ($this->artisans->removeElement($artisan)) {
            // set the owning side to null (unless already changed)
            if ($artisan->getCrm() === $this) {
                $artisan->setCrm(null);
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Crm
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Crm
    {
        $this->name = $name;
        return $this;
    }


}