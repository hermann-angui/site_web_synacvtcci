<?php

namespace App\Helper;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;


class FileUploadHelper
{

    public function __construct()
    {
    }

    public function upload(?File $file, ?string $destinationDirectory = null, ?bool $keepName = false): ?File
    {
        try {
            if(!$file) return null;

            if(!$keepName) $fileName = time() . uniqid() .'.'. ($file->guessExtension() ? $file->guessExtension(): $file->getExtension());
            else $fileName = $file->getClientOriginalName();

            if(!file_exists($destinationDirectory)) mkdir($destinationDirectory);
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
