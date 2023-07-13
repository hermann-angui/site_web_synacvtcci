<?php

namespace App\Helper;

use App\Entity\Member;
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

    public function __construct(string $uploadDirectory,
                                FileUploadHelper $fileUploadHelper,
                                ImageHelper $imageHelper)
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

    public function uploadAsset(?File $file, ?string $destDirectory): ?File
    {
        return $this->fileUploadHelper->upload($file, $this->getUploadDirectory($destDirectory));
    }

    public function removeAsset(?File $file): ?string
    {
        return $this->fileUploadHelper->remove($file);
    }


    public function createThumbnail(?File $file, ?string $destDirectory){
        $this->imageHelper->createThumbnail($file->getRealPath(), $this->getUploadDirectory($destDirectory));
    }

}
