<?php
namespace App\Helper;

use Symfony\Component\HttpFoundation\File\File;

interface AssetHelperInterface
{
    public function getUploadDirectory(?string $destDirectory): ?string;

    public function uploadAsset(?File $file, ?string $destDirectory): ?File;

    public function removeAsset(?File $file): ?string;
}
