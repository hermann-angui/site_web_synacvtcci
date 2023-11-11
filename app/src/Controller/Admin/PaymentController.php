<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Payment;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use App\Service\Member\MemberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        if(in_array($member->getStatus() , ["PAYED", "COMPLETED"])){
            return $this->redirectToRoute('admin_member_cncmi_edit', ['id' => $member->getId()]);
        }
        return $this->render('admin/payment/choose.html.twig', ['member' => $member]);
    }

    #[Route(path: '/cashin/{id}', name: 'admin_payment_cash')]
    public function cashin(Member $member, PaymentRepository $paymentRepository, MemberRepository $memberRepository): Response
    {
        if(!in_array($member->getStatus() , ["PAYED", "COMPLETED"])){
            $payment = new Payment();
            $payment->setUser($this->getUser())
                ->setReference(str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18)))
                ->setType('cash')
                ->setMontant(3500)
                ->setTarget("synacvtcci")
                ->setPaymentFor($member)
                ->setCodePaymentOperateur(null)
                ->setReceiptFile(null)
                ->setStatus("PAYED");
            $paymentRepository->add($payment, true);

            $member->setStatus("PAYED");
            $memberRepository->add($member, true);

        }
        return $this->redirectToRoute('admin_member_cncmi_edit', ['id' => $member->getId()]);
    }




}
