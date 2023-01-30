<?php


namespace App\Helper;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;


class FileUploadHelper
{
    private SluggerInterface $slugger;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
        $this->logger = $logger;
    }

    public function upload(?File $file, string $destinationDirectory): ?string
    {
        try {
            if(!$file) return null;
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $safeFilename . time() . uniqid() .'.'. $file->guessExtension();

            $fs = new Filesystem();
            if(!$fs->exists($destinationDirectory)) $fs->mkdir($destinationDirectory);
                $file->move($destinationDirectory, $fileName);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return null;
        }
        return $fileName;
    }


    public function remove(?File $file, string $destinationDirectory): ?string
    {
        try {
            if(!$file) return null;
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $safeFilename . time() . uniqid() .'.'. $file->guessExtension();

            $fs = new Filesystem();
            if(!$fs->exists($destinationDirectory)) $fs->mkdir($destinationDirectory);
            $file->move($destinationDirectory, $fileName);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return null;
        }
        return $fileName;
    }
}
