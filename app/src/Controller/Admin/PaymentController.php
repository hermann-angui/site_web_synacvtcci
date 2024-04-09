<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Payment;
use App\Helper\ActivityLogger;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use App\Service\ConfigurationService\ConfigurationService;
use App\Service\Member\MemberService;
use App\Service\Payment\PaymentService;
use App\Service\Wave\WaveService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/admin/payment')]
class PaymentController extends AbstractController
{
    #[Route(path: '', name: 'admin_payment_index')]
    public function index(Request $request, MemberRepository $memberRepository): Response
    {
        $members = $memberRepository->findAll();
        return $this->render('admin/pages/index.html.twig', ["members" => $members]);
    }

    #[Route(path: '/choose/{id}', name: 'admin_payment_choose')]
    public function searchMain(Member $member): Response
    {
        return $this->render('admin/payment/choose.html.twig', ['member' => $member]);
    }

    #[Route(path: '/cashin/{id}', name: 'admin_payment_cash')]
    public function cashin(Member $member, PaymentService $paymentService, ConfigurationService $configurationService, ActivityLogger $activityLogger): Response
    {
        $payment = $paymentService->create(
            $member,
            $this->getUser(),
            $configurationService->getParameter('app.montant_frais_service_technique'),
            null,
            "FRAIS_SERVICE_TECHNIQUE",
            'PAID',
            'CASH',
            null
        );
        $paymentService->generatePaymentReceipt($payment);
        $activityLogger->create($payment, "Paiement cash effectuée");
        return $this->redirectToRoute('payment_succes_page', ['id' => $payment->getId()]);
    }

    #[Route(path: '/carte/syndicat/{id}', name: 'do_payment_carte_syndicat')]
    public function doSyndicatPayment(Member $member, WaveService $waveService, PaymentService $paymentService, ActivityLogger $activityLogger, ConfigurationService $configurationService, PaymentRepository $paymentRepository): Response
    {
        try{
            $montant = match ($member->getActivity()) {
                "CHAUFFEUR VTC" => $configurationService->getParameter('app.montant_frais_carte_synacvtcci'),
                "CHAUFFEUR TAXI" => $configurationService->getParameter('app.montant_frais_carte_taxi'),
                "CHAUFFEUR LIVREUR" => $configurationService->getParameter('app.montant_frais_carte_falci')
            };
            $response = $waveService->makePayment($montant);
            if ($response) {
                $payment = $paymentService->create(
                    $member,
                    $this->getUser(),
                    $montant,
                    $response->getClientReference(),
                    "FRAIS_CARTE_SYNDICAT",
                    strtoupper($response->getPaymentStatus()),
                    'MOBILE_MONEY',
                    "WAVE"
                );
                $activityLogger->create($payment, "Payment carte syndical initié");
                return $this->redirect($response->getWaveLaunchUrl());

            } else return $this->redirectToRoute('admin_index');
        }catch(\Exception $e){
            return new Response($e->getTraceAsString());
        }

    }

    #[Route(path: '/do/{id}', name: 'do_payment')]
    public function doPaymentServiceTechnique(Member $member, WaveService $waveService, ActivityLogger $activityLogger, PaymentService $paymentService, ConfigurationService $configurationService, PaymentRepository $paymentRepository): Response
    {
        $response = $waveService->makePayment($configurationService->getParameter('app.montant_frais_service_technique'));

        if ($response) {
            $payment = $paymentService->create(
                $member,
                $this->getUser(),
                $response->getAmount(),
                $response->getClientReference(),
                "FRAIS_SERVICE_TECHNIQUE",
                $response->getPaymentStatus(),
                'MOBILE_MONEY',
                "WAVE"
            );

            $activityLogger->create($payment, "Payment frais service technique initié");
            return $this->redirect($response->getWaveLaunchUrl());
        } else return $this->redirectToRoute('admin_index');
    }

    #[Route(path: '/wave/checkout/{status}', name: 'admin_wave_payment_callback')]
    public function wavePaymentCheckoutStatusCallback($status, Request $request, MemberRepository $memberRepository, PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->findOneBy(["reference" => $request->get("ref")]);
        if ($payment && (strtoupper(trim($status)) === "SUCCESS")) {
            $payment->setStatus("PAID");
            $paymentRepository->add($payment, true);
            if ($payment->getTarget() === "FRAIS_SERVICE_TECHNIQUE") return $this->redirectToRoute('admin_payment_success_page', ["id" => $payment->getId(), "status" => $status]);
            elseif ($payment->getTarget() === "FRAIS_CARTE_SYNDICAT") return $this->redirectToRoute('payment_succes_carte_syndicat', ["id" => $payment->getId(), "status" => $status]);
        }
        return $this->redirectToRoute('admin_index');
    }

    #[Route(path: '/wave', name: 'admin_wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request, PaymentRepository $paymentRepository, MemberRepository $memberRepository): Response
    {
        $payload = json_decode($request->getContent(), true);
        if (!empty($payload) && array_key_exists("data", $payload)) {
            $data = $payload['data'];
            if (!empty($data) && array_key_exists("client_reference", $data)) {
                $payment = $paymentRepository->findOneBy(["reference" => $data["client_reference"]]);
                if ($payment && (array_key_exists("payment_status", $data) && (strtoupper($data["payment_status"]) === "SUCCEEDED"))) {
                    $payment->setCodePaymentOperateur($data["transaction_id"]);
                    $payment->setStatus("PAID");
                    $paymentRepository->add($payment, true);
                }
            }
        }
        return $this->json($payload);
    }

    #[Route(path: '/receipt/{id}', name: 'member_display_receipt', methods: ['POST', 'GET'])]
    public function showPaymentReceipt(?Payment $payment, PaymentService $paymentService): Response
    {
        $paymentService->generatePaymentReceipt($payment);
        return $this->render('admin/payment/receipt.html.twig', ['payment' => $payment]);
    }

    #[Route(path: '/successpage/{id}', name: 'admin_payment_success_page', methods: ['POST', 'GET'])]
    public function paymentSuccessPage(?Payment $payment, PaymentService $paymentService, MemberRepository $memberRepository): Response
    {
        $member = $payment->getPaymentFor();
        $member->setEtape(3);
        $memberRepository->add($member, true);
        $paymentService->generatePaymentReceipt($payment);
        return $this->render('admin/payment/payment-success.html.twig', ['payment' => $payment]);
    }

    #[Route(path: '/carte-syndicat/success/{id}', name: 'payment_succes_carte_syndicat', methods: ['POST', 'GET'])]
    public function paymentCarteSyndicatSuccessPage(?Payment $payment, PaymentService $paymentService, MemberRepository $memberRepository): Response
    {
        $paymentService->generatePaymentReceipt($payment);
        echo "Here 6";
        $member = $payment->getPaymentFor();
        $member->setEtape(5);
        $memberRepository->add($member, true);
        return $this->render('admin/payment/payment_succes_carte_syndicat.html.twig', ['payment' => $payment]);
    }

}
