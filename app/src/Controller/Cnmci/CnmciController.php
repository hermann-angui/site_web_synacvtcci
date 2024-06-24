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

#[Route('/admin/cnmci')]
class CnmciController extends AbstractController
{
    #[Route('', name: 'cnmci_index', methods: ['GET', 'POST'])]
    public function dashboard(Request $request): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        return $this->render('cnmci/index.html.twig');
    }

    #[Route('/stats', name: 'cnmci_stats', methods: ['GET', 'POST'])]
    public function stats(Request $request, MemberRepository $memberRepository): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $from = new \DateTime();
        $from = $from->modify('yesterday');
        $to = new \DateTime();

        $totalInscription = $memberRepository->getTotalMembers();
        $actgroups = $memberRepository->getTotalGroupByActivity();

        $stats = [
            "CHAUFFEUR VTC" => 0,
            "CHAUFFEUR TAXI" => 0,
            "CHAUFFEUR LIVREUR" => 0,
        ];

        foreach ($actgroups as $group) {
            $stats[$group['activity']] = $group['total'];
        }
        $latest = $memberRepository->getLastest();

        $totals = [
            "total_souscriptions" => $totalInscription,
            "total_vtc" => $stats["CHAUFFEUR VTC"] ? : 0,
            "total_taxi" => $stats["CHAUFFEUR TAXI"] ? : 0,
            "total_livreurs" => $stats["CHAUFFEUR LIVREUR"] ? : 0,
            "total_validation_paiement" => 0,
            "total_validation_souscription" => 0
        ];

        return $this->render('cnmci/dashboard.html.twig', [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'), 'latest' => $latest,
            'totals' => $totals
        ]);
    }

    #[Route('/adherents', name: 'cnmci_souscripteurs', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $from = new \DateTime();
        $from = $from->modify('-1 year');
        $to = new \DateTime();
        return $this->render('cnmci/souscripteurs.html.twig', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]);
    }

    #[Route('/telecharger/photo/{id}', name: 'cnmci_download_photo', methods: ['GET', 'POST'])]
    public function downloadPhoto(Request $request, Member $member): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $imageUrl = $this->getParameter("kernel.project_dir") . "/public/members/" . $member->getReference() . "/" . basename($member->getPhoto());
        $info = new SplFileInfo($imageUrl);
        $outputFile = $member->getReference() . '_' . $member->getLastName() . ' ' . $member->getFirstName() . '.' . $info->getExtension();
        return $this->file($imageUrl, $outputFile);
    }

    #[Route('/telecharger/documents/{id}', name: 'cnmci_download_documents', methods: ['GET', 'POST'])]
    public function downloadAllDocs(Request $request, Member $member, MemberService $memberService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');

        $outputFile = $memberService->combinePdfsForPrint($member, true, 'download');
        return $this->file($outputFile);
    }

    #[Route('/telecharger/adherents', name: 'cnmci_download_list', methods: ['POST', 'GET'])]
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

    #[Route('/telecharger/matrice', name: 'cnmci_download_matrice', methods: ['POST', 'GET'])]
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

    #[Route('/souscription/dt', name: 'cnmci_souscription_datatable', methods: ['GET', 'POST'])]
    public function souscriptionDT(Request $request, Connection $connection)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'member';
        $primaryKey = 'id';
        $columns = [
            [
                'db' => 'id',
                'dt' => 'id',
                'formatter' => function ($d, $row) {
                    $content = "<div class='form-check form-check-dark font-size-16'>
                                     <input class='form-check-input row-selector' type='checkbox' data-id='$d'>
                                     <label class='form-check-label' for='transactionCheck02'></label>
                               </div>";
                    return $content;
                }
            ],
            [
                'db' => 'photo',
                'dt' => 'photo',
                'formatter' => function ($d, $row) {
                    $imageUrl = $row['reference'] . "/$d";
                    $content = "<div class='avatar-md img-fluid rounded-circle'><img src='/members/$imageUrl' alt='' class='img-fluid d-block rounded-circle'></div>";
                    return $content;
                }
            ],
            [
                'db' => 'last_name',
                'dt' => 'last_name',
            ],
            [
                'db' => 'first_name',
                'dt' => 'first_name',
            ],
            [
                'db' => 'subscription_date',
                'dt' => 'subscription_date'
            ],
            [
                'db' => 'driving_license_number',
                'dt' => 'driving_license_number'
            ],
            [
                'db' => 'is_payment_validated',
                'dt' => 'is_payment_validated',
                'formatter' => function ($d, $row) {
                    if($d) $content = sprintf("<span class='badge rounded-pill badge-soft-success font-size-12'>%s</span>", "VALIDER");
                    else $content = sprintf("<span class='badge rounded-pill badge-soft-warning font-size-12'>%s</span>", "EN ATTENTE DE VALIDATION");
                    return $content;
                }
            ],
            [
                'db' => 'is_inscription_validated',
                'dt' => 'is_inscription_validated',
                'formatter' => function ($d, $row) {
                    if($d) $content = sprintf("<span class='badge rounded-pill badge-soft-success font-size-12'>%s</span>", "VALIDER");
                    else $content = sprintf("<span class='badge rounded-pill badge-soft-warning font-size-12'>%s</span>", "EN ATTENTE DE VALIDATION");
                    return $content;
                }
            ],
            [
                'db' => 'reference',
                'dt' => 'reference',
                'formatter' => function ($d, $row) {
                    $id = $row['id'];
                    $content = "<div class='d-flex gap-2 flex-wrap justify-content-center'>
                                    <div class='btn-group'>
                                        <button class='btn btn-soft-success dropdown-toggle btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                            <small></small><i class='mdi mdi-menu'></i>
                                        </button>
                                        <div class='dropdown-menu' style=''>
                                            <a class='dropdown-item' href='/admin/cnmci/fiche/$id'><i class='mdi mdi-eye'></i> Voir la fiche CNMCI</a>
                                            <a class='dropdown-item' href='/admin/cnmci/telecharger/documents/$id'><i class='mdi mdi-file-download'></i> Télécharger les documents</a>
                                            <a class='dropdown-item' href='/admin/cnmci/telecharger/photo/$id'><i class='mdi mdi-download'></i> Télécharger la photo</a>";

                    if(!$row['is_payment_validated'])      $content.="<a class='dropdown-item btn-validate-payment' href='#' data-id='$id'><i class='mdi mdi-check-circle'></i> Valider paiement</a>";
                    if(!$row['is_inscription_validated'] && $row['is_payment_validated'])  $content.="<a class='dropdown-item btn-validate-souscription' href='#' data-id='$id'><i class='mdi mdi-check'></i> Valider l'inscription</a>";
                    $content.="</div></div></div> ";
                    return $content;
                }
            ],
        ];

        $sql_details = [
            'user' => $paramDB['user'],
            'pass' => $paramDB['password'],
            'db' => $paramDB['dbname'],
            'host' => $paramDB['host']
        ];

        $whereResult = '';
        if(isset($params['date_start']) && isset($params['date_end']))  $whereResult = " etape >= 4 AND subscription_date BETWEEN '" . $params['date_start'] . "' AND '" . $params['date_end'] . "' ";

        $response = DataTableHelper::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);
        return new JsonResponse($response);
    }

    #[Route('/fiche/{id}', name: 'cnmci_show', methods: ['GET', 'POST'])]
    public function show(Member $member): Response
    {
        return $this->render('cnmci/show.html.twig', ['member' => $member]);
    }

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
