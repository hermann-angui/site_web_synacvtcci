<?php

namespace App\Controller\Cnmci;

use App\Entity\Member;
use App\Helper\DataTableHelper;
use App\Repository\MemberRepository;
use App\Service\Member\MemberService;
use Doctrine\DBAL\Connection;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\Wizard;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\Routing\Generator\UrlGenerator;

#[Route('/cnmci/telecharger')]
class DownloadController extends AbstractController
{
    #[Route('/photo/{id}', name: 'cnmci_download_photo', methods: ['GET', 'POST'])]
    public function downloadPhoto(Request $request, Member $member): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $imageUrl = $this->getParameter("kernel.project_dir") . "/public/members/" . $member->getReference() . "/" . basename($member->getPhoto());
        $info = new SplFileInfo($imageUrl);
        $outputFile = $member->getReference() . '_' . $member->getLastName() . ' ' . $member->getFirstName() . '.' . $info->getExtension();
        return $this->file($imageUrl, $outputFile);
    }

    #[Route('/documents/{id}', name: 'cnmci_download_documents', methods: ['GET', 'POST'])]
    public function downloadAllDocs(Request $request, Member $member, MemberService $memberService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');

        $outputFile = $memberService->combinePdfsForPrint($member, true, 'download');
        return $this->file($outputFile);
    }

    #[Route('/adherents', name: 'cnmci_download_list', methods: ['POST', 'GET'])]
    public function downloadList(Request $request, MemberRepository $memberRepository, MemberService $memberService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $from = $request->get('date_from');
        $to = $request->get('date_to');
        $members = $memberRepository->findAdherentsFromTo($from, $to);
        if (!$members) return $this->json(null);
        $file = $memberService->generateAdherentListXlsxFile($members);
        $outputFile = $memberService->archiveMemberDocuments($members, $file);
        return $this->file($outputFile, 'liste_adherents.zip');
    }

    #[Route('/matrice', name: 'cnmci_download_matrice', methods: ['POST', 'GET'])]
    public function downloadMatriceEncaissement(Request $request, MemberRepository $memberRepository, MemberService $memberService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $from = $request->get('date_from');
        $to = $request->get('date_to');
        $members = $memberRepository->findAdherentsFromTo($from, $to);
        if (!$members) return $this->json(null);
        $fileXls = $memberService->generateMatriceEncaissementXlsxFile($members);
        return $this->file($fileXls, basename($fileXls));
    }


}
