<?php

namespace App\Command;

use App\Repository\ArtisanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'artisan:correct',
    description: 'Add a short description for your command',
)]
class ArtisanCorrectCommand extends Command
{
    /**
     * @var ArtisanRepository
     */
    private $artisanRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ArtisanRepository $artisanRepository)
    {
        $this->entityManager = $entityManager;
        $this->artisanRepository = $artisanRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $folder = "/var/www/html/public/artisans/";
        $artisans = $this->artisanRepository->findAll();
        foreach ($artisans as $artisan) {
            try {
                $path = $folder . $artisan->getReference();
                if (!file_exists($path)) mkdir($path, 0777, true);

                $photo = $folder . "/" . $artisan->getPhoto();
                if (file_exists($photo)) copy($photo, $path . "/" . $artisan->getPhoto());

                $cardPhoto = $folder . "/" . $artisan->getCardPhoto();
                if (file_exists($cardPhoto)) copy($cardPhoto, $path . "/" . $artisan->getCardPhoto());

                $barCode = $folder . $artisan->getReference() . "/" . $artisan->getReference() . "_card.png";
                if (file_exists($barCode)) copy($cardPhoto, $barCode);
            } catch (\Exception $e) {
                dump($e);
            }
        }

        return 0;
    }
}

