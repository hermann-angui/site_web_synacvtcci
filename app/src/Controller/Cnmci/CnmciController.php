<?php

namespace App\Controller\Cnmci;

use App\Entity\Artisan;
use App\Entity\Service;
use App\Entity\Villes;
use App\Form\ArtisanRegistrationType;
use App\Helper\ActivityLogger;
use App\Helper\DataTableHelper;
use App\Repository\ArtisanRepository;
use App\Repository\VillesRepository;
use App\Service\Artisan\ArtisanService;
use App\Service\Payment\PaymentService;
use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cnmci')]
class CnmciController extends AbstractController
{
    #[Route('/index', name: 'cnmci_index', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        return $this->render('cnmci/index.html.twig');
    }

    #[Route('/stats', name: 'cnmci_stats', methods: ['GET', 'POST'])]
    public function stats(Request $request, ArtisanRepository $artisanRepository): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $from = new \DateTime();
        $from = $from->modify('yesterday');
        $to = new \DateTime();

        $totalInscription = $artisanRepository->getTotalArtisans();
        $actgroups = $artisanRepository->getTotalGroupByActivity();

        $stats = [
            "CONDUCTEUR VTC" => 0,
            "CONDUCTEUR TAXI COMPTEUR" => 0,
            "CONDUCTEUR TAXI COMMUNAL" => 0,
            "CONDUCTEUR MOTO TAXI" => 0,
            "CONDUCTEUR LIVREUR" => 0,
            "CONDUCTEUR TRICYCLE" => 0,
        ];

        foreach ($actgroups as $group) {
            $stats[$group['activity']] = $group['total'];
        }
        $latest = $artisanRepository->getLastest();

        $totals = [
            "total_souscriptions" => $totalInscription,
            "total_vtc" => $stats["CONDUCTEUR VTC"] ? : 0,
            "total_taxi_compteur" => $stats["CONDUCTEUR TAXI COMPTEUR"] ? : 0,
            "total_taxi_communal" => $stats["CONDUCTEUR TAXI COMMUNAL"] ? : 0,
            "total_moto_taxi" => $stats["CONDUCTEUR MOTO TAXI"] ? : 0,
            "total_livreur" => $stats["CONDUCTEUR LIVREUR"] ? : 0,
            "total_tricyle" => $stats["CONDUCTEUR TRICYCLE"] ? : 0,
            "total_validation_paiement" => 0,
            "total_validation_souscription" => 0
        ];

        return $this->render('cnmci/dashboard.html.twig', [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'), 'latest' => $latest,
            'totals' => $totals
        ]);
    }


    #[Route('/artisans', name: 'cnmci_souscripteurs', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $from = new \DateTime();
        $from = $from->modify('-1 year');
        $to = new \DateTime();
        return $this->render('cnmci/list_enrolement.html.twig', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]);
    }

    #[Route('/enrolements/dt', name: 'cnmci_enrolement_datatable', methods: ['GET', 'POST'])]
    public function souscriptionDT(Request $request, Connection $connection)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'artisan';
        $primaryKey = 'id';
        $whereResult = '';

        $columns = [];

        if($request->get('payment_validated')) {
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
                        $content = "<div class='avatar-sm img-fluid rounded-circle'><img src='/artisans/$imageUrl' alt='' class='img-fluid d-block rounded-circle'></div>";
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
                    'dt' => 'subscription_date',
                    'formatter' => function ($d, $row) {
                        return date_format(new \DateTime($d), 'd/m/Y');
                    }
                ],
                [
                    'db' => 'driving_license_number',
                    'dt' => 'driving_license_number'
                ],
            ];
        }else{
            $columns = [
                [
                    'db' => 'id',
                    'dt' => 'id'
                ],
                [
                    'db' => 'photo',
                    'dt' => 'photo',
                    'formatter' => function ($d, $row) {
                        $imageUrl = $row['reference'] . "/$d";
                        $content = "<div class='avatar-sm img-fluid rounded-circle'><img src='/artisans/$imageUrl' alt='' class='img-fluid d-block rounded-circle'></div>";
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
                    'dt' => 'subscription_date',
                    'formatter' => function ($d, $row) {
                        return date_format(new \DateTime($d), 'd/m/Y');
                    }
                ],
                [
                    'db' => 'driving_license_number',
                    'dt' => 'driving_license_number'
                ],
            ];
        }

        if($request->get('payment_validated')) {
            $columns[] =  [
                'db' => 'is_payment_validated',
                'dt' => 'is_payment_validated',
                'formatter' => function ($d, $row) {
                    if($d) $content = sprintf("<span class='badge rounded-pill badge-soft-success font-size-12'>%s</span>", "VALIDER");
                    else $content = sprintf("<span class='badge rounded-pill badge-soft-warning font-size-12'>%s</span>", "EN ATTENTE DE VALIDATION");
                    return $content;
                }
            ];

            $columns[] =  [
                'db' => 'reference',
                'dt' => 'reference',
                'formatter' => function ($d, $row) {
                    $id = $row['id'];
                    $content = "<div class='d-flex justify-content-center gap-3'>
                                    <a href='/cnmci/fiche/$id' class='btn btn-light btn-sm'><i class='mdi mdi-eye font-size-18'></i></a>";

                    if(!$row['is_payment_validated']) {
                        $content .= "<span data-id='$id' class='btn btn-soft-success btn-sm btn-validate-payment'><i class='mdi mdi-account-check font-size-18'></i></span>";
                        $content .= "<span href='#' data-id='$id' class='btn btn-soft-danger btn-sm btn-reject-payment'><i class='mdi mdi-account-cancel font-size-18'></i></span>";
                    }
                    $content.= "</div>";
                    return $content;
                }
            ];

            $whereResult = " (is_payment_validated = 0 OR is_payment_validated IS NULL) AND (status != 'ENROLEMENT_PAIEMENT_REJETE')";
        } elseif($request->get('inscription_validated')) {
            $columns[] =  [
                'db' => 'is_payment_validated',
                'dt' => 'is_payment_validated',
                'formatter' => function ($d, $row) {
                    if($d) $content = sprintf("<span class='badge rounded-pill badge-soft-success font-size-12'>%s</span>", "VALIDER");
                    else $content = sprintf("<span class='badge rounded-pill badge-soft-warning font-size-12'>%s</span>", "EN ATTENTE DE VALIDATION");
                    return $content;
                }
            ];
            $columns [] =  [
                'db' => 'is_inscription_validated',
                'dt' => 'is_inscription_validated',
                'formatter' => function ($d, $row) {
                    if($d) $content = sprintf("<span class='badge rounded-pill badge-soft-success font-size-12'>%s</span>", "VALIDER");
                    else $content = sprintf("<span class='badge rounded-pill badge-soft-warning font-size-12'>%s</span>", "EN ATTENTE DE VALIDATION");
                    return $content;
                }
            ];
            $columns[] =  [
                'db' => 'reference',
                'dt' => 'reference',
                'formatter' => function ($d, $row) {
                    $id = $row['id'];
                    $content = "<div class='d-flex gap-3'>
                                    <a href='/cnmci/fiche/$id' class='btn btn-light btn-sm' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Afficher le dossier'><i class='mdi mdi-eye font-size-18'></i></a>
                                    <a href='/cnmci/telecharger/fiche-pdf/$id' class='btn btn-light btn-sm' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Télécharger le dossier'><i class='mdi mdi-file-download font-size-18'></i></a>";

                    if(!$row['is_inscription_validated'] && $row['is_payment_validated']) {
                        $content .= "<span data-id='$id' class='btn btn-soft-success btn-sm btn-validate-enrolement' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Valider le dossier'><i class='mdi mdi-account-check font-size-18'></i></span>";
                        $content .= "<span data-id='$id' class='btn btn-soft-danger btn-sm btn-reject-enrolement' data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Rejeter le dossier'><i class='mdi mdi-account-cancel font-size-18'></i></span>";
                    }
                    $content.= "</div>";
                    return $content;
                }
            ];
            $whereResult = " (is_payment_validated = 1  AND (is_inscription_validated = 0 OR is_inscription_validated IS NULL) AND status != 'ENROLEMENT_REJETE') ";
        } else {
            $columns[] =  [
                'db' => 'reference',
                'dt' => 'reference',
                'formatter' => function ($d, $row) {
                    $id = $row['id'];

                    $content = "<div class='d-flex justify-content-center gap-3'>
                                    <a href='/cnmci/fiche/$id' class='btn btn-light btn-sm'><i class='mdi mdi-eye font-size-18'></i></a>";
                    $content.= "</div>";
                    return $content;
                }
            ];

        }


        $sql_details = [
            'user' => $paramDB['user'],
            'pass' => $paramDB['password'],
            'db' => $paramDB['dbname'],
            'host' => $paramDB['host']
        ];

        if(isset($params['date_start']) && isset($params['date_end']))  $whereResult .= " AND etape >= 4 AND (subscription_date BETWEEN '" . $params['date_start'] . "' AND '" . $params['date_end'] . "') ";

        $response = DataTableHelper::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);
        return new JsonResponse($response);
    }


    /**
     * @throws \Exception
     */
    #[Route('/fiche/{id}', name: 'cnmci_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Artisan $artisan, ArtisanService $artisanService, VillesRepository $villesRepository, ActivityLogger $activityLogger): Response
    {
     //   return $this->render('cnmci/fiche.html.twig', ['artisan' => $artisan]);

        date_default_timezone_set("Africa/Abidjan");

        $form = $this->createForm(ArtisanRegistrationType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artisanService->updateArtisan($artisan, []);
            $activityLogger->update($artisan, "Mise à jour des données du souscripteur");
        }

        return $this->renderForm('cnmci/edit.html.twig', [
            'artisan' => $artisan,
            'form' => $form,
        ]);
    }

    #[Route('/generate/virtual-cnmci-carte/{id}', name: 'cnmci_generate_virtual-cnmci-carte', methods: ['GET', 'POST'])]
    public function generateCnmciCard(Artisan $artisan, ArtisanService $artisanService): Response
    {
        $artisanService->generateSingleCnmciCard($artisan);
      //  if($artisan && $artisan->getIsInscriptionValidated()) $artisanService->generateSingleCnmciCard($artisan);
        return $this->json('SUCCESS');
    }

    #[Route('/sticker/{id}', name: 'artisan_cncmi_sticker', methods: ['GET'])]
    public function artisan_cncmi_sticker($id, ArtisanRepository $artisanRepository): Response
    {
        $artisan = $artisanRepository->findOneBy(['code_sticker' => $id]);
        return $this->render('admin/artisan/cnmci_show_sticker.html.twig', ['artisan' => $artisan]);
    }

    #[Route('/fiche/{artisan_id}/service/{service_id}', name: 'admin_artisan_review_cncmi', methods: ['GET'])]
    #[ParamConverter('artisan', options: ['id' => 'artisan_id'])]
    #[ParamConverter('service', options: ['id' => 'service_id'])]
    public function ficheCnmciShow(Artisan $artisan, Service $service, PaymentService $paymentService): Response
    {
        $payment = $paymentService->findArtisanPaymentByTarget($artisan, $service->getId());
      //  $paymentService->generatePaymentReceipt($payment);
        return $this->render('admin/artisan/cnmci_show.html.twig', ['artisan' => $artisan, "payment" => $payment]);
    }


}
