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
        $from = $from->modify('yesterday');
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
        $file = $this->generateAdherentListXlsxFile($members);
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
        $fileXls = $this->generateMatriceEncaissementXlsxFile($members);
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
                    $imageUrl = $row['reference'] . "/" . $row['photo'];
                    $content = "<img src='/members/" . $imageUrl . "' alt='' class='avatar-md rounded-circle img-thumbnail' width='100'>";
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
                    if($d) $content = sprintf("<span class='badge badge-pill badge-soft-success font-size-14'>%s</span>", "VALIDER");
                    else $content = sprintf("<span class='badge badge-pill badge-soft-warning font-size-14'>%s</span>", "EN ATTENTE DE VALIDATION");
                    return $content;
                }
            ],
            [
                'db' => 'status',
                'dt' => 'status',
                'formatter' => function ($d, $row) {
                    if($d) $content = sprintf("<span class='badge badge-pill badge-soft-success font-size-14'>%s</span>", "VALIDER");
                    else $content = sprintf("<span class='badge badge-pill badge-soft-warning font-size-14'>%s</span>", "EN ATTENTE DE VALIDATION");
                    return $content;
                }
            ],
            [
                'db' => 'id',
                'dt' => '',
                'formatter' => function ($d, $row) {
                    $id = $row['id'];
                    $content = "<div class='d-flex gap-2 flex-wrap justify-content-start'>
                                    <div class='btn-group'>
                                        <button class='btn btn-cnmci dropdown-toggle btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                            <small></small><i class='mdi mdi-menu'></i>
                                        </button>
                                        <div class='dropdown-menu' style=''>
                                            <a class='dropdown-item' href='/admin/cnmci/fiche/$id'><i class='mdi mdi-eye'></i> Voir la fiche CNMCI</a>
                                            <a class='dropdown-item' href='/admin/cnmci/telecharger/documents/$id'><i class='mdi mdi-file-download'></i> Télécharger les documents</a>
                                            <a class='dropdown-item' href='/admin/cnmci/telecharger/photo/$id'><i class='mdi mdi-download'></i> Télécharger la photo</a>";

                    if(!$row['is_payment_validated'])  $content.="<a class='dropdown-item btn-validate-payment' href='#' data-id='$id'><i class='mdi mdi-check-circle'></i> Valider paiement</a>";
                    if(!$row['is_inscription_validated'])  $content.="<a class='dropdown-item btn-validate-souscription' href='#' data-id='$id'><i class='mdi mdi-check'></i> Valider l'inscription</a>";
                    $content.="</div></div></div> ";
                    return $content;
                }
            ],
            [
                'db' => 'reference',
                'dt' => 'reference'
            ],
            [
                'db' => 'photo',
                'dt' => 'photo'
            ],
            [
                'db' => 'is_inscription_validated',
                'dt' => 'is_inscription_validated'
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

    #[Route('/validation/souscription', name: 'cnmci_validation_souscription', methods: ['POST'])]
    public function validateSouscription(Request $request, MemberRepository $memberRepository, MemberService $memberService): Response
    {
        $request = $request->request;
        $member = $memberRepository->find($request->get('member_id'));
        if(!$member?->getIsPaymentValidated()) return $this->json('FAILED');
        $member->setCnmciNumeroRm($request->get('cnmci_numero_rm'));
        $member->setCnmciNumeroCarteProfessionelle($request->get('cnmci_carte_professionelle'));
        $member->setIsInscriptionValidated(true);
        $member->setStatus("VALIDER");

        $memberService->generateSingleCnmciCard($member);

        $memberRepository->add($member, true);
        return $this->json('OK');
    }

    #[Route('/validate-paiement-souscription/{id}', name: 'cnmci_validate_payment', methods: ['GET', 'POST'])]
    public function validatePayment(Member $member, MemberRepository $memberRepository): Response
    {
        $member->setIsPaymentValidated(true);
        $memberRepository->add($member, true);
        return $this->render('cnmci/show.html.twig', ['member' => $member]);
    }

    private function generateMatriceEncaissementXlsxFile($members): ?string
    {
        try {

            $dir = $this->getCnmciDir();
            if (!file_exists($dir)) mkdir($dir, 0777, true);

            $inputFileName = $dir . "CNMCI-Matrice des encaissements.xls";
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            $spreadsheet = $reader->load($inputFileName);
            $worksheet = $spreadsheet->getSheet(0);

            $count = 1;
            $cel = 3;
            /** @var Member $member */
            foreach ($members as $member) {
                $d = [
                    $count++,
                    "Registre des metiers et Carte d Artisans",
                    '15000',
                    $member->getPaymentReceiptCnmciCode(), //$row['payment_receipt_cnmci_code']  $member->get,
                    $member->getSubscriptionDate()->format('d/m/Y'), //$row['subscription_date']->format('d/m/Y'),
                    $member->getLastName() . '' . $member->getFirstName(),
                    $member->getActivity(),
                    $member->getActivityGeoLocation(),
                    $member->getMobile(),
                    '',
                ];

                $worksheet->fromArray(
                    $d,             // The data to set
                    NULL,        // Array values with this value will not be set
                    "A" . $cel++         // Top left coordinate of the worksheet range where we want to set these values (default is A1)
                );
            }

            $spreadsheet->getActiveSheet()->setAutoFilter(
                $spreadsheet->getActiveSheet()->calculateWorksheetDimension()
            );

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $outputFileName = $dir . time() . uniqid() . ".xls";

            if (file_exists($outputFileName)) \unlink($outputFileName);
            $writer->save($outputFileName);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return $outputFileName;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return null;
    }

    private function generateAdherentListXlsxFile($members): ?string
    {
        try {
            $dir = $this->getCnmciDir();
            if (!file_exists($dir)) mkdir($dir, 0777, true);

            $inputFileName = $dir . "CNMCI-Matrice des inscrits.xls";

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            $spreadsheet = $reader->load($inputFileName);
            $worksheet = $spreadsheet->getSheet(0);

            $count = 1;
            $cel = 3;
            /** @var Member $member * */
            foreach ($members as $member) {
                try {
                    $d = [
                        "N°" => $count++,
                        "EMAIL" => $member->getEmail(),
                        "NOM" => $member->getLastName(),
                        "PRENOMS" => $member->getFirstName(),
                        "ACTIVITE" => $member->getActivity(),
                        "DATE SOUSCRIPTION" => $member->getSubscriptionDate()?->format('d/m/Y'),
                        "SEXE" => $member->getSex(),
                        "PHOTO" => $member->getPhoto(),
                        "DATE DE NAISSANCE" => $member->getDateOfBirth()?->format('d/m/Y'),
                        "VILLE NAISSANCE" => $member->getBirthLocality(),
                        "N° PERMIS DE CONDUIRE" => $member->getDrivingLicenseNumber(),
                        "NUMERO PIECE D'IDENTITE" => $member->getIdNumber(),
                        "TYPE DE PIECE" => $member->getIdType(),
                        "PAYS" => $member->getCountry(),
                        "VILLE" => $member->getCity(),
                        "COMMUNE" => $member->getCommune(),
                        "MOBILE" => $member->getMobile(),
                        "TEL" => $member->getPhone(),
                        "PHOTO PIECE RECTO" => $member->getPhotoPieceFront(),
                        "PHOTO PIECE VERSO" => $member->getPhotoPieceBack(),
                        "PHOTO PERMIS RECTO" => $member->getPhotoPermisFront(),
                        "PHOTO PERMIS VERSO" => $member->getPhotoPermisBack(),
                        "NATIONALITE" => $member->getNationality(),
                        "QUARTIER DE RESIDENCE" => $member->getQuartier(),
                        "WHATSAPP" => $member->getWhatsapp(),
                        "ENTREPRISES" => !empty($member->getCompany()) ? implode("|", $member->getCompany()): '',
                        "NOM CONJOINT" => $member->getPartnerLastName(),
                        "PRENOMS CONJOINT" => $member->getFirstName(),
                        "LIEU DE DELIVRANCE PIECE" => $member->getIdDeliveryPlace(),
                        "DATE DE DELIVRANCE PIECE" => $member->getIdDeliveryDate()?->format('d/m/Y'),
                        "ETAT CIVIL" => $member->getEtatCivil(),
                        "REFERENCE" => $member->getReference(),
                        "PAYS DE NAISSANCE" => $member->getIdDeliveryPlace(),
                        "LOCALITE NAISSANCE" => $member->getBirthLocality(),
                        "AUTORITE DE DELIVRANCE PIECE" => $member->getIdDeliveryAuthority(),
                        "BOITE POSTALE" => $member->getPostalCode(),
                        "PAIEMENT ORANGE MONEY" => $member->getPaymentReceiptCnmciCode(),
                        "LOCALISATION GEOGRAPHIQUE DE L'ACTIVITE" => $member->getIdDeliveryPlace(),
                        "PAYS DE L'ACTIVITE" => $member->getActivityCountryLocation(),
                        "VILLE DE L'ACTIVITE" => $member->getActivityCityLocation(),
                        "QUARTIER DE L'ACTIVITE" => $member->getActivityQuartierLocation(),
                        "CATEGORIE SOCIOPROFESSIONNELLE" => $member->getSocioprofessionnelleCategory(),
                        "DATE DEBUT ACTIVITE " => $member->getActivityDateDebut()?->format('d/m/Y'),
                        "PRENOMS PERSONNE A CONTACTER" => $member->getPartnerFirstName(),
                        "NOM PERSONNE A CONTACTER" => $member->getPartnerLastName(),
                        //    "TELEPHONE PERSONNE A CONTACTER" => "",
                        //    "RECU ORANGE MONEY" => "",
                        //    "FORMULAIRE CNMCI" => "",
                        //    "DOCUMENTS" => "",
                        //    "DOCUMENTS IDENTITE" => ""
                    ];
                    $r = array_values($d);
                    $worksheet->fromArray(
                        $r,     // The data to set
                        NULL,               // Array values with this value will not be set
                        "A" . $cel++     // Top left coordinate of the worksheet range where we want to set these values (default is A1)
                    );

                } catch (\Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }
            }

            $spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $outputFileName = $dir . uniqid(). ".xls";
            if (file_exists($outputFileName)) \unlink($outputFileName);
            $writer->save($outputFileName);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return $outputFileName;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return null;
    }

    private function getCnmciDir()
    {
        return $this->getParameter("kernel.project_dir") . "/public/cnmci/";
    }

}
