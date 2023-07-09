<?php

namespace App\Command;

use App\Entity\Member;
use App\Service\Member\ArtisanService;
use App\Service\Member\MemberService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'member:correct',
    description: 'Add a short description for your command',
)]
class MemberCorrectCommand extends Command
{

    /**
     * @var ArtisanService
     */
    private $memberService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, MemberService $memberService)
    {
        $this->entityManager = $entityManager;
        $this->memberService = $memberService;

        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('from', InputArgument::OPTIONAL, 'Initial matricule')
            ->addOption('to', null, InputOption::VALUE_NONE, 'Final matricule')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        ini_set('max_execution_time', '-1');

        if (($from = $input->getOption('from')) && ($to = $input->getOption('to')))
        {
            $from = (int)substr($from, -5);
            $to = (int) substr($to, -5);
            $ranges = range($from, $to);
            foreach($ranges as $matricule){
                $matricules[] = "SY12023" .   sprintf('%05d', $matricule);
            }
            $memberDtos = $this->memberService->generateMultipleMemberCards($matricules);
        }else{
            $memberDtos = $this->memberService->generateMultipleMemberCards();
        }

        $zipFile = $this->memberService->archiveMemberCards($memberDtos);
        return Command::SUCCESS;
    }
}
