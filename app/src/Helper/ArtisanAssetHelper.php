<?php

namespace App\Helper;

use App\Entity\Artisan;
use Symfony\Component\HttpFoundation\File\File;

class ArtisanAssetHelper implements AssetHelperInterface
{
    /**
     * @var FileUploadHelper
     */
    protected FileUploadHelper $fileUploadHelper;

    /**
     * @var ImageHelper
     */
    protected ImageHelper $imageHelper;

    /**
     * @var string
     */
    protected string $uploadDirectory;

    public function __construct(string $uploadDirectory, FileUploadHelper $fileUploadHelper, ImageHelper $imageHelper)
    {
        $this->fileUploadHelper = $fileUploadHelper;
        $this->uploadDirectory = $uploadDirectory;
        $this->imageHelper = $imageHelper;
    }

    public function getUploadDirectory(?string $destDirectory): ?string
    {
        try {
            if (!$destDirectory) return null;
            $path = $this->uploadDirectory . "/public/artisans/" . $destDirectory . "/";
            if (!file_exists($path)) mkdir($path, 0777, true);
            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function uploadAsset(?File $file, ?string $destDirectory, ?string $newName = null): ?File
    {
        return $this->fileUploadHelper->upload($file, $this->getUploadDirectory($destDirectory));
    }

    public function removeAsset(?File $file): ?string
    {
        return $this->fileUploadHelper->remove($file);
    }


    public function createThumbnail(String $file, ?string $destDirectory,$width, $height): ?string{
        $filePath = new File($this->getUploadDirectory($destDirectory) . $file);
        return $this->imageHelper->createThumbnail($filePath, $this->getUploadDirectory($destDirectory), $width, $height);
    }

    /**
     * @param Artisan $artisan
     * @return string
     */
    public function getArtisanDir(Artisan $artisan): ?string {
        $folder = $this->uploadDirectory . "/public/artisans/" . $artisan->getReference() . "/";
        if(!file_exists($folder)) mkdir($folder, 0777, true);
        return $folder;
    }
}
