<?php

namespace App\Command;

use App\Helper\FileHelper;
use App\Service\Member\MemberService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'member:clean:folder',
    description: 'Add a short description for your command',
)]
class CleanMemberFolderCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager, private MemberService $memberService)
    { }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $folder = "/var/www/html/public/members/";
        $members = $this->memberService->getAllMembers();
        foreach ($members as $member) {
            try {
                $path = $folder . $member->getReference() . "/";
                if($member->getPhoto()) $files[] = $member->getPhoto();
                if($member->getCardPhoto()) $files[] = $member->getCardPhoto();
                if($member->getCnmciCardFrontImage()) $files[] = $member->getCnmciCardFrontImage();
                if($member->getCnmciCardBackImage()) $files[] = $member->getCnmciCardBackImage();
                if($member->getFormulaireCnmciPdf()) $files[] = $member->getFormulaireCnmciPdf();
                if($member->getMergedDocumentsPdf()) $files[] = $member->getMergedDocumentsPdf();
                if($member->getPaymentReceiptCarteSyndicatPdf()) $files[] = $member->getPaymentReceiptCarteSyndicatPdf();
                if($member->getOnlineRegistrationReceiptPdf()) $files[] = $member->getOnlineRegistrationReceiptPdf();
                if($member->getPaymentReceiptCnmciPdf()) $files[] = $member->getPaymentReceiptCnmciPdf();
                if($member->getPaymentReceiptServiceTechniquePdf()) $files[] = $member->getPaymentReceiptServiceTechniquePdf();
                if($member->getScanDocumentIdentitePdf()) $files[] = $member->getScanDocumentIdentitePdf();
                $files[] = $member->getReference() . '_barcode.png';
                $files[] = $member->getReference() . '_card.png';
                $files[] = $member->getReference() . '_card_cnmci.png';

                $all_files = scandir($path);
                $intersect_files = array_diff($all_files , $files);
                foreach($intersect_files as $file) {
                    $p = $path . $file;
                    if(is_file($p) && file_exists($p)) \unlink($p);
                }

                FileHelper::deleteExistingFile($this->memberService->getMemberDir($member) . $member->getPhoto());
                $this->memberService->createThumbnail($member, 128, 128);

            } catch (\Exception $e) {
                dump($e);
                continue;
            }
        }

        return 0;
    }
}

