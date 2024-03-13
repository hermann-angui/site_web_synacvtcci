<?php

namespace App\Controller\Client;

use App\Entity\Payment;
use App\Helper\ActivityLogger;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use App\Service\Payment\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/payment')]
class WavePaymentController extends AbstractController
{
    #[Route(path: '/wave/checkout/{status}', name: 'wave_payment_callback')]
    public function wavePaymentCheckoutStatusCallback($status,
                                                      Request $request,
                                                      MemberRepository $memberRepository,
                                                      PaymentService $paymentService,
                                                      PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->findOneBy(["reference" => $request->get("ref")]);
        if ($payment && (strtoupper(trim($status)) === "SUCCESS")) {
            $payment->setStatus("PAID");
            $paymentRepository->add($payment, true);
            $member = $payment->getPaymentFor();
            $paymentService->generatePaymentReceipt($payment);
            if ($member) {
                $member->setStatus("PAID");
                $memberRepository->add($member, true);
            }
            return $this->redirectToRoute('wave_success_page', ["id" => $payment->getId(), "status" => $status]);
        }
        if($this->isGranted("ROLE_USER")) return $this->redirectToRoute('admin_index');
        else return $this->redirectToRoute('home');
    }

    #[Route(path: '/wave', name: 'wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request,
                                        PaymentRepository $paymentRepository,
                                        PaymentService $paymentService,
                                        MemberRepository $memberRepository): Response
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
                    $member = $payment->getPaymentFor();
                    $paymentService->generatePaymentReceipt($payment);
                    if ($member) {
                        $member->setStatus("PAID");
                        $memberRepository->add($member, true);
                    }
                }
            }
        }
        return $this->json($payload);
    }


    #[Route(path: '/successpage/{id}', name: 'wave_success_page', methods: ['POST', 'GET'])]
    public function paymentSuccesPage(?Payment $payment, PaymentService $paymentService): Response
    {
        if (in_array($payment->getStatus(), ["SUCCEEDED", "PAID", "CLOSED", "COMPLETED"])) {
            return $this->render('frontend/payment/payment-success.html.twig', ['payment' => $payment]);
        }
        if(!$payment->getPaymentFor()?->getPaymentReceiptSynacvtcciPdf()) $paymentService->generatePaymentReceipt($payment);

        if($this->isGranted('ROLE_USER')) return $this->redirectToRoute('admin_index');
        else return $this->redirectToRoute('home');
    }

    #[Route('/receipt/download/{id}', name: 'download_payment_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Payment $payment,
                                PaymentService $paymentService,
                                ActivityLogger $activityLogger): Response
    {
        $activityLogger->create($payment, "Téléchargement de reçu");
        return $paymentService->downloadMemberPaymentReceipt($payment);
    }
}
