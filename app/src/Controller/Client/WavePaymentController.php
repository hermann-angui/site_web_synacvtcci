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
                                                      PaymentRepository $paymentRepository): Response
    {
        $payments = $paymentRepository->findBy(["reference" => $request->get("ref")]);
        if ($payments && (strtoupper(trim($status)) === "SUCCESS")) {
            foreach ($payments as $payment){
                $payment->setStatus("PAID");
                $paymentRepository->add($payment, true);
                $member = $payment->getPaymentFor();
                if ($member) {
                    $member->setStatus("PAID");
                    if($payment->getTarget() == 'FRAIS_ADHESION_SYNDICAT') $member->setHasPaidForSyndicat(true);
                    $memberRepository->add($member, true);
                }
            }
            return $this->redirectToRoute('wave_payment_success_page');
        }
        if($this->isGranted("ROLE_USER")) return $this->redirectToRoute('admin_index');
        else return $this->redirectToRoute('home');
    }

    #[Route(path: '/wave', name: 'wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request,
                                        PaymentRepository $paymentRepository,
                                        MemberRepository $memberRepository): Response
    {
        $payload = json_decode($request->getContent(), true);

        if (!empty($payload) && array_key_exists("data", $payload)) {
            $data = $payload['data'];
            if (!empty($data) && array_key_exists("client_reference", $data)) {
                $payments = $paymentRepository->findBy(["reference" => $data["client_reference"]]);
                foreach ($payments as $payment){
                    if ($payment && (array_key_exists("payment_status", $data) && (strtoupper($data["payment_status"]) === "SUCCEEDED"))) {
                        $payment->setCodePaymentOperateur($data["transaction_id"]);
                        $payment->setStatus("PAID");
                        $paymentRepository->add($payment, true);
                        $member = $payment->getPaymentFor();
                        if ($member) {
                            $member->setStatus("PAID");
                            if($payment->getTarget() == 'FRAIS_ADHESION_SYNDICAT') $member->setHasPaidForSyndicat(true);
                            $memberRepository->add($member, true);
                        }
                    }
                }
            }
        }
        return $this->json($payload);
    }


    #[Route(path: '/successpage', name: 'wave_payment_success_page', methods: ['POST', 'GET'])]
    public function paymentSuccessPage(Request $request): Response
    {
        return $this->render('admin/payment/payment-success.html.twig');
    }
    #[Route('/receipt/download/{id}', name: 'download_payment_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Payment $payment,
                                PaymentService $paymentService,
                                ActivityLogger $activityLogger): Response
    {
        $activityLogger->create($payment, "Téléchargement du reçu de paiement.");
        return $paymentService->downloadMemberPaymentReceipt($payment);
    }
}
