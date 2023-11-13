<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Payment;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
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
        if (in_array($member->getStatus(), ["PAID", "COMPLETED"])) {
            return $this->redirectToRoute('admin_index', ['id' => $member->getId()]);
        }
        return $this->render('admin/payment/choose.html.twig', ['member' => $member]);
    }

    #[Route(path: '/cashin/{id}', name: 'admin_payment_cash')]
    public function cashin(Member $member, PaymentRepository $paymentRepository, MemberRepository $memberRepository): Response
    {
        if (!in_array($member->getStatus(), ["PAID", "COMPLETED"])) {
            $payment = new Payment();
            $payment->setUser($this->getUser())
                ->setReference(str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18)))
                ->setType('cash')
                ->setMontant(3500)
                ->setTarget("synacvtcci")
                ->setPaymentFor($member)
                ->setCodePaymentOperateur(null)
                ->setReceiptFile(null)
                ->setStatus("PAID");
            $paymentRepository->add($payment, true);

            $member->setStatus("PAID");
            $memberRepository->add($member, true);

        }
       // return $this->redirectToRoute('admin_member_index', ['id' => $member->getId()]);
        return $this->redirectToRoute('admin_index', ['id' => $member->getId()]);
    }


    #[Route(path: '/do/{id}', name: 'do_payment')]
    public function doPayment(Request           $request, Member $member,
                              WaveService       $waveService,
                              MemberRepository  $memberRepository,
                              PaymentRepository $paymentRepository): Response
    {
        $response = $waveService->makePayment($member);
        if ($response) {
            $payment = new Payment();
            $payment->setStatus(strtoupper($response->getPaymentStatus()));
            $payment->setReference($response->getClientReference());
            $payment->setOperateur("WAVE");
            $payment->setMontant($response->getAmount());
            $payment->setType("MOBILE_MONEY");
            $payment->setReceiptNumber(PaymentService::generateReference());
            $payment->setCreatedAt(new \DateTime('now'));
            $payment->setModifiedAt(new \DateTime('now'));
            $payment->setPaymentFor($member);
            $paymentRepository->add($payment, true);
            return $this->redirect($response->getWaveLaunchUrl());
        } else return $this->redirectToRoute('admin_index');
    }

    #[Route(path: '/wave/checkout/{status}', name: 'wave_payment_callback')]
    public function wavePaymentCheckoutStatusCallback($status,
                                                      Request $request,
                                                      PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->findOneBy(["reference" => $request->get("ref")]);
        if ($payment && (strtoupper(trim($status)) === "SUCCESS")) {
            try {
                $path = "/var/www/html/var/log/wave_payment_callback/$status/";
                if (!file_exists($path)) mkdir($path, 0777, true);
                $data = ["reference" => $request->get("ref"), "date" => date("Ymd H:i:s")];
                file_put_contents($path . "log_" . date("Ymd") . ".log", json_encode($data), FILE_APPEND);
            } catch (\Exception $e) {
            }
            return $this->redirectToRoute('payment_succes_page', ["id" => $payment->getId(), "status" => $status]);
        }
        return $this->redirectToRoute('home');
    }

    #[Route(path: '/wave', name: 'wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request, PaymentRepository $paymentRepository, MemberRepository $memberRepository): Response
    {
        $payload = json_decode($request->getContent(), true);
        if (!empty($payload) && array_key_exists("data", $payload)) {
            $data = $payload['data'];
            if (!empty($data) && array_key_exists("client_reference", $data)) {
                $payment = $paymentRepository->findOneBy(["reference" => $data["client_reference"]]);
                if ($payment) {
                    if (array_key_exists("payment_status", $data) && (strtoupper($data["payment_status"]) === "SUCCEEDED")) {
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
        }
        return $this->json($payload);
    }

    #[Route(path: '/receipt/{id}', name: 'member_display_receipt', methods: ['POST', 'GET'])]
    public function showPaymentReceipt(?Payment $payment, PaymentService $paymentService): Response
    {
        if (in_array($payment->getStatus(), ["SUCCEEDED", "PAID", "CLOSED"])) {
            $paymentService->generatePaymentReceipt($payment->getPaymentFor());
            return $this->render('admin/payment/receipt.html.twig', ['payment' => $payment]);
        }
        return $this->redirectToRoute('admin_index');
    }

    #[Route(path: '/successpage', name: 'payment_succes_page', methods: ['POST', 'GET'])]
    public function paymentSuccesPage(?Payment $payment, PaymentService $paymentService): Response
    {
        if (in_array($payment->getStatus(), ["SUCCEEDED", "PAID", "CLOSED"])) {
            $paymentService->generatePaymentReceipt($payment->getPaymentFor());
            return $this->render('admin/payment/payment-success.html.twig', ['payment' => $payment]);
        }
        return $this->redirectToRoute('admin_member_search');
    }
}
