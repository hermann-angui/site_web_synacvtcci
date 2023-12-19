<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Payment;
use App\Helper\ActivityLogger;
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
    public function index(Request $request,
                          MemberRepository $memberRepository): Response
    {
        $members = $memberRepository->findAll();
        return $this->render('admin/pages/index.html.twig', ["members" => $members]);
    }

    #[Route(path: '/choose/{id}', name: 'admin_payment_choose')]
    public function searchMain(Member $member, Request $request): Response
    {
        if (in_array($member->getStatus(), ["PAID", "COMPLETED"])) {
            return $this->redirectToRoute('admin_index', ['id' => $member->getId()]);
        }
        return $this->render('admin/payment/choose.html.twig', ['member' => $member, 'montant' => $request->get('montant', 3500)]);
    }

    #[Route(path: '/cashin/{id}', name: 'admin_payment_cash')]
    public function cashin(Member $member,
                           Request $request,
                           PaymentService $paymentService,
                           MemberService $memberService,
                           ActivityLogger $activityLogger): Response
    {
        if ($member->getStatus() !== 'PAID') {
            $payment = new Payment();
            $payment->setUser($this->getUser())
                ->setReference(str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18)))
                ->setType('cash')
                ->setMontant($request->get('montant') ?? $this->getParameter('montant_frais'))
                ->setTarget("synacvtcci")
                ->setPaymentFor($member)
                ->setCodePaymentOperateur(null)
                ->setReceiptFile(null)
                ->setStatus("PAID");
            $paymentService->store($payment);

            $paymentService->generatePaymentReceipt($payment);
            $member->setStatus("PAID");
            if($payment->getMontant() > $this->getParameter('montant_frais')) $member->setHasPaidForSyndicat(true);
            $memberService->saveMember($member);

            $activityLogger->create($payment, "Paiement cash effectuée");

            return $this->redirectToRoute('payment_succes_page', ['id' => $payment->getId()]);
        }
        return $this->redirectToRoute('admin_index');
    }


    #[Route(path: '/do/{id}', name: 'do_payment')]
    public function doPayment(Member $member,
                              Request $request,
                              WaveService       $waveService,
                              PaymentRepository $paymentRepository): Response
    {
        $response = $waveService->makePayment($request->get('montant') ?? $this->getParameter('montant_frais'));
        if ($response) {
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
            $paymentRepository->add($payment, true);
            return $this->redirect($response->getWaveLaunchUrl());
        } else return $this->redirectToRoute('admin_index');
    }

    #[Route(path: '/successpage/{id}', name: 'payment_succes_page', methods: ['POST', 'GET'])]
    public function paymentSuccessPage(?Payment $payment, PaymentService $paymentService): Response
    {
        if (in_array($payment->getStatus(), ["COMPLETED","SUCCEEDED", "PAID", "CLOSED"])) {
            $paymentService->generatePaymentReceipt($payment);
            return $this->render('admin/payment/payment-success.html.twig', ['payment' => $payment]);
        }
        return $this->redirectToRoute('admin_index');
    }
}
