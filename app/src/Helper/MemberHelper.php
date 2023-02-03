<?php
namespace App\Helper;

use App\Entity\Member;
use Symfony\Component\HttpFoundation\File\File;

class MemberHelper
{
    /**
     * @var FileUploadHelper
     */
    protected FileUploadHelper $fileUploadHelper;

    /**
     * @var string
     */
    protected string $uploadDirectory;

    public function __construct(string $uploadDirectory, FileUploadHelper $fileUploadHelper)
    {
        $this->fileUploadHelper = $fileUploadHelper;
        $this->uploadDirectory = $uploadDirectory;

    }

    public function getUploadDirectory(?Member $member): ?string
    {
        try {
            if(!$member) return null;
            $path = $this->uploadDirectory . "/public/members/" . $member->getMatricule() . "/" ;
            if (!file_exists($path)) mkdir($path, 0777, true);
            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function uploadAsset(?File $file, ?Member $member): ?string
    {
        return $this->fileUploadHelper->upload($file, $this->getUploadDirectory($member));
    }

    public function removeAsset(?File $file): ?string
    {
        return $this->fileUploadHelper->remove($file);
    }

}