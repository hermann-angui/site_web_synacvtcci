<?php

namespace App\Controller\Client;

use App\Entity\Payment;
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
                                                      PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->findOneBy(["reference" => $request->get("ref")]);
        if ($payment && (strtoupper(trim($status)) === "SUCCESS")) {
            $payment->setStatus("PAID");
            $paymentRepository->add($payment, true);
            $member = $payment->getPaymentFor();
            if ($member) {
                $member->setStatus("PAID");
                $memberRepository->add($member, true);
            }
            return $this->redirectToRoute('wave_success_page', ["id" => $payment->getId(), "status" => $status]);
        }
        if($this->getUser()) return $this->redirectToRoute('admin_index');
        else return $this->redirectToRoute('home');
    }

    #[Route(path: '/wave', name: 'wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request,
                                        PaymentRepository $paymentRepository,
                                        MemberRepository $memberRepository): Response
    {
        $payload = json_decode($request->getContent(), true);

        try {
            $path = "/var/www/html/var/log/wave_payment_checkout_webhook";
            if (!file_exists($path)) mkdir($path, 0777, true);
            $data = ["reference" => $request->get("ref"), "date" => date("Ymd H:i:s")];
            file_put_contents($path . "log_" . date("Ymd") . ".log", json_encode($data), FILE_APPEND);
        } catch (\Exception $e) {
        }

        if (!empty($payload) && array_key_exists("data", $payload)) {
            $data = $payload['data'];
            if (!empty($data) && array_key_exists("client_reference", $data)) {
                $payment = $paymentRepository->findOneBy(["reference" => $data["client_reference"]]);
                if ($payment && (array_key_exists("payment_status", $data) && (strtoupper($data["payment_status"]) === "SUCCEEDED"))) {
                    $payment->setCodePaymentOperateur($data["transaction_id"]);
                    $payment->setStatus("PAID");
                    $paymentRepository->add($payment, true);
                    $member = $payment->getPaymentFor();
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
    public function paymentSuccesPage(?Payment $payment): Response
    {
        if (in_array($payment->getStatus(), ["SUCCEEDED", "PAID", "CLOSED"])) {
            return $this->render('frontend/payment/payment-success.html.twig', ['payment' => $payment]);
        }

        if(!$this->getUser()) return $this->redirectToRoute('home');
        else return $this->redirectToRoute('admin_index');
    }

    #[Route('/receipt/download/{id}', name: 'download_payment_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Payment $payment, PaymentService $paymentService): Response
    {
        return $paymentService->downloadMemberPaymentReceipt($payment);
    }
}