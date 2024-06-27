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

#[Route('/cnmci')]
class ValidationController extends AbstractController
{
     #[Route('/validate/paiement-souscription/batch', name: 'cnmci_validate_payment_batch', methods: ['GET', 'POST'])]
    public function validateBatchPayment(Request $request, MemberService $memberService): Response
    {
        if(!empty($request->get('ids'))) $memberService->validatePaymentBatch($request->get('ids'));
        return $this->json('SUCCESS');
    }

    #[Route('/validate/souscription-cnmci/batch', name: 'cnmci_validation_souscription_batch', methods: ['POST'])]
    public function validateSouscriptionBatch(Request $request, MemberService $memberService): Response
    {
        if(!empty($request->get('ids'))) $memberService->validateSouscriptionBatch($request->get('ids'));
        return $this->json('SUCCESS');
    }

    #[Route('/validate/souscription-cnmci', name: 'cnmci_validation_souscription', methods: ['POST', 'GET'])]
    public function validateSouscription(Request $request, MemberService $memberService): Response
    {
        $response = $memberService->validateSouscription(
            intval($request->get('member_id')),
            $request->get('cnmci_numero_rm'),
            $request->get('cnmci_carte_professionelle')
        );
        if($response) return $this->json('SUCCESS');
        else return $this->json('FAILED');
    }

    #[Route('/validate/paiement-souscription/{id}', name: 'cnmci_validate_payment', methods: ['GET', 'POST'])]
    public function validatePayment(Member $member, MemberService $memberService): Response
    {
        if($member && !$member->getIsPaymentValidated()) $memberService->validatePayment($member);
        return $this->json('SUCCESS');
    }

    #[Route('/generate/virtual-cnmci-carte/{id}', name: 'cnmci_generate_virtual-cnmci-carte', methods: ['GET', 'POST'])]
    public function generateCnmciCard(Member $member, MemberService $memberService): Response
    {
        $memberService->generateSingleCnmciCard($member);
      //  if($member && $member->getIsInscriptionValidated()) $memberService->generateSingleCnmciCard($member);
        return $this->json('SUCCESS');
    }


}
