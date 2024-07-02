<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;

class FileHelper {

    public static function deleteExistingFile($file): void {
        if(is_file($file)) \unlink( $file);
    }

    public static function generateUniqFileName(File $file): string {
        return  time() . uniqid() . '.'. ($file->guessExtension() ? $file->guessExtension(): '');
    }

    public static function generateUuidFileName(File $file): string {
        return  substr(Uuid::v4()->toRfc4122(), 0, 18) . '.'. ($file->guessExtension() ? $file->guessExtension(): '');
    }
}