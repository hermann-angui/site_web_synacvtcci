<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: '`article`')]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;   // video , text

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type:Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $modified_at;

    #[ORM\OneToMany(mappedBy: Article::class, targetEntity: MediaAsset::class, cascade: ["remove", "persist"], orphanRemoval: true)]
    private Collection $mediaAssets;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->modified_at = new \DateTime();
        $this->mediaAssets = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
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
     * @return Member
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
     * @return Member
     */
    public function setModifiedAt(?\DateTimeInterface $modified_at): self
    {
        $this->modified_at = $modified_at;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return Article
     */
    public function setTitle(?string $title): Article
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Article
     */
    public function setDescription(?string $description): Article
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @return Article
     */
    public function setContent(?string $content): Article
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return Collection<int, MediaAsset>
     */
    public function getMediaAssets(): Collection
    {
        return $this->mediaAssets;
    }

    public function addMediaAssets(MediaAsset $mediaAsset): self
    {
        if (!$this->mediaAssets->contains($mediaAsset)) {
            $this->mediaAssets[] = $mediaAsset;
            $mediaAsset->setArticle($this);
        }

        return $this;
    }

    public function removeChild(MediaAsset $mediaAsset): self
    {
        if ($this->mediaAssets->removeElement($mediaAsset)) {
            // set the owning side to null (unless already changed)
            if ($mediaAsset->getArticle() === $this) {
                $mediaAsset->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     * @return Article
     */
    public function setCategory(?string $category): Article
    {
        $this->category = $category;
        return $this;
    }

}
