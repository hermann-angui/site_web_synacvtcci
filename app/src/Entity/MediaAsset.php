<?php

namespace App\Entity;

use App\Repository\MediaAssetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: MediaAssetRepository::class)]
#[ORM\Table(name: '`media_asset`')]
class MediaAsset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $is_thumbnail = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $modified_at;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'mediaAssets')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'mediaAssets')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Article $album = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->modified_at = new \DateTime();
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return MediaAsset
     */
    public function setName(?string $name): MediaAsset
    {
        $this->name = $name;
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
     * @return MediaAsset
     */
    public function setDescription(?string $description): MediaAsset
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * @param string|null $file
     * @return MediaAsset
     */
    public function setFile(?string $file): MediaAsset
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return Article|null
     */
    public function getArticle(): ?Article
    {
        return $this->article;
    }

    /**
     * @param Article|null $article
     * @return MediaAsset
     */
    public function setArticle(?Article $article): MediaAsset
    {
        $this->article = $article;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsThumbnail(): ?bool
    {
        return $this->is_thumbnail;
    }

    /**
     * @param bool|null $is_thumbnail
     * @return MediaAsset
     */
    public function setIsThumbnail(?bool $is_thumbnail): MediaAsset
    {
        $this->is_thumbnail = $is_thumbnail;
        return $this;
    }

    /**
     * @return Article|null
     */
    public function getAlbum(): ?Article
    {
        return $this->album;
    }

    /**
     * @param Article|null $album
     * @return MediaAsset
     */
    public function setAlbum(?Article $album): MediaAsset
    {
        $this->album = $album;
        return $this;
    }


}
