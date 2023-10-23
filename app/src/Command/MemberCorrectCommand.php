<?php

namespace App\Command;

use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'member:correct',
    description: 'Add a short description for your command',
)]
class MemberCorrectCommand extends Command
{
    /**
     * @var MemberRepository
     */
    private $memberRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, MemberRepository $memberRepository)
    {
        $this->entityManager = $entityManager;
        $this->memberRepository = $memberRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $folder = "/var/www/html/public/members/";
        $members = $this->memberRepository->findAll();
        foreach ($members as $member) {
            try {
                $path = $folder . $member->getReference();
                if (!file_exists($path)) mkdir($path, 0777, true);

                $photo = $folder . $member->getMatricule() . "/" . $member->getPhoto();
                if (file_exists($photo)) copy($photo, $path . "/" . $member->getPhoto());

                $cardPhoto = $folder . $member->getMatricule() . "/" . $member->getCardPhoto();
                if (file_exists($cardPhoto)) copy($cardPhoto, $path . "/" . $member->getCardPhoto());

                $barCode = $folder . $member->getMatricule() . "/" . $member->getMatricule() . "_card.png";
                if (file_exists($barCode)) copy($cardPhoto, $barCode);
            } catch (\Exception $e) {
                dump($e);
            }
        }

        return 0;
    }
}

