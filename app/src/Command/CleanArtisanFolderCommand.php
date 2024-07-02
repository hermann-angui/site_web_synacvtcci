<?php

namespace App\Command;

use App\Helper\FileHelper;
use App\Service\Artisan\ArtisanService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'artisan:clean:folder',
    description: 'Add a short description for your command',
)]
class CleanArtisanFolderCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager, private ArtisanService $artisanService)
    { }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $folder = "/var/www/html/public/artisans/";
        $artisans = $this->artisanService->getAllArtisans();
        foreach ($artisans as $artisan) {
            try {
                $path = $folder . $artisan->getReference() . "/";
                if($artisan->getPhoto()) $files[] = $artisan->getPhoto();
                if($artisan->getCardPhoto()) $files[] = $artisan->getCardPhoto();
                if($artisan->getCnmciCardFrontImage()) $files[] = $artisan->getCnmciCardFrontImage();
                if($artisan->getCnmciCardBackImage()) $files[] = $artisan->getCnmciCardBackImage();
                if($artisan->getFormulaireCnmciPdf()) $files[] = $artisan->getFormulaireCnmciPdf();
                if($artisan->getMergedDocumentsPdf()) $files[] = $artisan->getMergedDocumentsPdf();
                if($artisan->getPaymentReceiptCarteSyndicatPdf()) $files[] = $artisan->getPaymentReceiptCarteSyndicatPdf();
                if($artisan->getOnlineRegistrationReceiptPdf()) $files[] = $artisan->getOnlineRegistrationReceiptPdf();
                if($artisan->getPaymentReceiptCnmciPdf()) $files[] = $artisan->getPaymentReceiptCnmciPdf();
                if($artisan->getPaymentReceiptServiceTechniquePdf()) $files[] = $artisan->getPaymentReceiptServiceTechniquePdf();
                if($artisan->getScanDocumentIdentitePdf()) $files[] = $artisan->getScanDocumentIdentitePdf();
                $files[] = $artisan->getReference() . '_barcode.png';
                $files[] = $artisan->getReference() . '_card.png';
                $files[] = $artisan->getReference() . '_card_cnmci.png';

                $all_files = scandir($path);
                $intersect_files = array_diff($all_files , $files);
                foreach($intersect_files as $file) {
                    $p = $path . $file;
                    if(is_file($p) && file_exists($p)) \unlink($p);
                }

                FileHelper::deleteExistingFile($this->artisanService->getArtisanDir($artisan) . $artisan->getPhoto());
                $this->artisanService->createThumbnail($artisan, 128, 128);

            } catch (\Exception $e) {
                dump($e);
                continue;
            }
        }

        return 0;
    }
}

