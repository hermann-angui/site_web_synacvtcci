<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Payment;
use App\Helper\ActivityLogger;
use App\Repository\MemberRepository;
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
    public function searchMain(Member $member, Request $request): Response
    {
        $viewData = [];
        $total = 0;
        foreach ($request->get('payFor') as $pay){
            if($pay === "FRAIS_SERVICE_TECHNIQUE") {
                $viewData["items"][] = [
                    "key" => "FRAIS_SERVICE_TECHNIQUE",
                    "title" => "Frais d'enrôlement",
                    "montant" => $this->getParameter('app.frais_service_technique'),
                ];
                $total+= (int)$this->getParameter('app.frais_service_technique');
            }
            elseif($pay === "FRAIS_ADHESION_SYNDICAT") {
                $viewData["items"][] = [
                    "key" => "FRAIS_ADHESION_SYNDICAT",
                    "title" => "Frais d'adhésion au syndicat",
                    "montant" => $this->getParameter('app.frais_adhesion_syndicat'),
                ];
                $total+= (int)$this->getParameter('app.frais_adhesion_syndicat');
            }
        }
        $viewData["total"] = $total;
        return $this->render('admin/payment/choose.html.twig', [
            'member' => $member,
            'payFor' => !empty($viewData) ? $viewData: null
        ]);
    }

    #[Route(path: '/do/{id}', name: 'do_payment')]
    public function doPayment(Member $member,
                              Request $request,
                              PaymentService $paymentService,
                              MemberService $memberService,
                              ActivityLogger $activityLogger,
                              WaveService $waveService): Response
    {
        $paymentInfos = $request->request->all();
        if($paymentInfos['paiement_mode'] === "cash"){
            $payments = [];
            foreach($paymentInfos['payfor'] as $payfor){
                $payment = new Payment();
                $payment->setUser($this->getUser())
                    ->setReference(str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18)))
                    ->setType('CASH')
                    ->setPaymentFor($member)
                    ->setCodePaymentOperateur(null)
                    ->setReceiptFile(null)
                    ->setStatus("PAID")
                    ->setTarget($payfor)
                    ->setMontant($paymentInfos["total"]);
                $paymentService->store($payment);
                $payments[] = $payment;
            }
            $member->setStatus("PAID");
            $memberService->saveMember($member);
            $activityLogger->create($payment, "Paiement cash effectuée");
            $paymentService->generatePaymentReceipt($member, $payments);
            return $this->redirectToRoute('payment_success_page', ['montant' => $payments]);

        }elseif($paymentInfos['paiement_mode'] === "mobile_money"){
            $response = $waveService->requestPayment($paymentInfos["total"]);
            if ($response) {
                foreach($paymentInfos['payfor'] as $payfor){
                    $payment = new Payment();
                    $payment->setUser($this->getUser());
                    $payment->setStatus(strtoupper($response->getPaymentStatus()));
                    $payment->setReference($response->getClientReference());
                    $payment->setOperateur("WAVE");
                    $payment->setMontant($response->getAmount());
                    $payment->setType("MOBILE_MONEY");
                    $payment->setReceiptNumber(PaymentService::generateReference());
                    $payment->setCreatedAt(new \DateTime('now'));
                    $payment->setModifiedAt(new \DateTime('now'));
                    $payment->setPaymentFor($member);
                    $payment->setTarget($payfor);
                    $paymentService->store($payment);
                }
                return $this->redirect($response->getWaveLaunchUrl());
            }
        }
        return $this->redirectToRoute('admin_index');
    }

    #[Route(path: '/successpage', name: 'payment_success_page', methods: ['POST', 'GET'])]
    public function paymentSuccessPage(Request $request): Response
    {
        return $this->render('admin/payment/payment-success.html.twig');
    }
}
