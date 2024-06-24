<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadHelper
{
    public function upload(?File $file, ?string $destinationDirectory, ?string $newName = null): ?File
    {
        try {
            if(!$file) return null;

            if(!$newName) $fileName = FileHelper::generateUniqFileName($file);
            else $fileName = $newName;

            if(!file_exists($destinationDirectory)) mkdir($destinationDirectory, 0777, true);
            return $file->move($destinationDirectory, $fileName);

        } catch (\Exception $e) {
            return null;
        }
    }

    public function remove(?File $file): ?bool
    {
        try {
            if($file->isFile()) \unlink($file->getRealPath());
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
