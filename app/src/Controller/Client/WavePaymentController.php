<?php

namespace App\Controller\Client;

use App\Entity\Payment;
use App\Helper\ActivityLogger;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use App\Service\Member\MemberService;
use App\Service\Payment\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/payment')]
class WavePaymentController extends AbstractController
{
    #[Route(path: '/wave/checkout/{status}', name: 'wave_payment_callback')]
    public function wavePaymentCheckoutStatusCallback($status, Request $request, MemberRepository $memberRepository, PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->findOneBy(["reference" => $request->get("ref")]);
        if ($payment && (strtoupper(trim($status)) === "SUCCESS")) {
            $payment->setStatus("PAID");
            $paymentRepository->add($payment, true);
            if ($payment->getTarget() === "FRAIS_SERVICE_TECHNIQUE") return $this->redirectToRoute('payment_succes_page', ["id" => $payment->getId(), "status" => $status]);
            elseif ($payment->getTarget() === "FRAIS_CARTE_SYNDICAT") return $this->redirectToRoute('payment_succes_carte_syndicat', ["id" => $payment->getId(), "status" => $status]);
        }
        return $this->redirectToRoute('admin_index');
    }

    #[Route(path: '/wave', name: 'wave_payment_checkout_webhook')]
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

    #[Route(path: '/successpage/{id}', name: 'payment_succes_page', methods: ['POST', 'GET'])]
    public function paymentSuccessPage(?Payment $payment, PaymentService $paymentService, MemberRepository $memberRepository): Response
    {
        $member = $payment->getPaymentFor();
        $member->setEtape(3);
        $memberRepository->add($member, true);
        $paymentService->generatePaymentReceipt($payment);
        return $this->render('admin/payment/payment-success.html.twig', ['payment' => $payment]);
    }

    #[Route(path: '/carte-syndicat/success/{id}', name: 'payment_succes_carte_syndicat', methods: ['POST', 'GET'])]
    public function paymentCarteSyndicatSuccessPage(?Payment $payment, PaymentService $paymentService, MemberService $memberService): Response
    {
        $member = $payment->getPaymentFor();
        $member->setEtape(5);
        $memberService->saveMember($member);
        $paymentService->generatePaymentReceipt($payment);
        $memberService->generateSingleMemberCard($payment->getPaymentFor());
        return $this->render('admin/payment/payment_succes_carte_syndicat.html.twig', ['payment' => $payment]);
    }

    #[Route('/receipt/download/{id}', name: 'download_payment_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Payment $payment, PaymentService $paymentService, ActivityLogger $activityLogger): Response
    {
        $activityLogger->create($payment, "Téléchargement de reçu");
        return $paymentService->downloadMemberPaymentReceipt($payment);
    }
}
