<?php

namespace App\Controller\Client;

use App\Entity\Child;
use App\Entity\Member;
use App\Entity\Payment;
use App\Form\MemberOnlineRegistrationType;
use App\Form\MemberRegistrationType;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use App\Service\Member\MemberService;
use App\Service\Payment\PaymentService;
use App\Service\Wave\WaveService;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{

    #[Route(path: '/', name: 'home')]
    public function home(Request $request): Response
    {
        $flashInfos = [
            "Suite à la bastonnade d'un chauffeur à ..",
            "La SYNACVTCCI apporte son assistance au chauffeur bastonné ..",
            "La SYNACVTCCI signe une convetion avec la maison d'assurance santé VITAS Santé",
        ];

        $imageTextActivities = [
            [
                "title"  => "",
                "description"  => "",
                "images" => ["","",""],
                "url" => ""
            ],
        ];

        $videoActivities = [
            [
                "title"  => "",
                "description"  => "",
                "url" => ""
            ],
        ];

        return $this->render('frontend/pages/index.html.twig',[
            "flashInfos" => $flashInfos,
            "videoActivities" => $videoActivities,
            "imageTextActivities" => $imageTextActivities,
        ]);
    }

    #[Route(path: '/success/{id}', name: 'success')]
    public function success(Member $member,
                            MemberService $memberService): Response
    {
        $flashInfos = [
            "Suite à la bastonnade d'un chauffeur à ..",
            "La SYNACVTCCI apporte son assistance au chauffeur bastonné ..",
            "La SYNACVTCCI signe une convetion avec la maison d'assurance santé VITAS Santé",
        ];
        $memberService->generateRegistrationReceipt($member);
        return $this->render('frontend/member/success.html.twig', ["flashInfos" => $flashInfos, 'member' => $member]);
    }

    #[Route(path: '/register', name: 'register_member')]
    public function registerMember (Request $request,
                                    MemberService $memberService): Response
    {
        $flashInfos = [
            "Suite à la bastonnade d'un chauffeur à ..",
            "La SYNACVTCCI apporte son assistance au chauffeur bastonné ..",
            "La SYNACVTCCI signe une convetion avec la maison d'assurance santé VITAS Santé",
        ];
        $member = new Member;
        $form = $this->createForm(MemberOnlineRegistrationType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $res = $this->handleFormCreation($request, $form, $member, $memberService);
            if($res) return $this->redirectToRoute('success',["id" => $member->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('frontend/member/register.html.twig', [
            "flashInfos" => $flashInfos,
            'member' => $member,
            'form' => $form
        ]);
    }

    #[Route(path: '/profile/{matricule}', name: 'public_member_profile')]
    public function memberProfile(Request $request,
                                  MemberRepository $memberRepository): Response
    {
        $member = $memberRepository->findOneBy(["matricule" => $request->get("matricule")]);
        if($member)  return $this->render('admin/member/synacvtcci/public_profile.html.twig', ["member" => $member]);
        else return $this->redirectToRoute('home');
    }

    #[Route('/cnmci/{id}', name: 'member_cncmi_sticker', methods: ['GET'])]
    public function formCnmciShow($id,
                                  MemberRepository $memberRepository): Response
    {
        $member = $memberRepository->findOneBy(['codeSticker' => $id]);
        return $this->render('admin/member/cnmci/cnmci_show_sticker.html.twig', ['member' => $member]);
    }

    private function handleFormCreation(Request $request,
                                        FormInterface $form,
                                        Member &$member,
                                        MemberService $memberService): Member {

        $images = [];

        if($form->has('photo'))  $images['photo'] = $form->get('photo')?->getData();
        if($form->has('photoPieceFront'))  $images['photoPieceFront'] = $form->get('photoPieceFront')?->getData();
        if($form->has('photoPieceBack'))  $images['photoPieceBack'] = $form->get('photoPieceBack')?->getData();
        if($form->has('photoPermisFront'))  $images['photoPermisFront'] = $form->get('photoPermisFront')?->getData();
        if($form->has('photoPermisBack'))  $images['photoPermisBack'] = $form->get('photoPermisBack')?->getData();

        if($form->has('paymentReceiptCnmci'))  $images['paymentReceiptCnmci'] = $form->get('paymentReceiptCnmci')?->getData();
        if($form->has('paymentReceiptSynacvtcciPdf'))  $images['paymentReceiptSynacvtcciPdf'] = $form->get('paymentReceiptSynacvtcciPdf')?->getData();

        $data = $request->request->all();
        if(isset($data['child'])){
            foreach($data['child'] as $childItem){
                $child=  new Child();
                $child->setLastName($childItem['lastname']);
                $child->setFirstName($childItem['firstname']);
                $child->setSex($childItem['sex']);
                $child->setMember($member);
                $member->addChild($child);
            }
        }
        $memberService->createMember($member, $images);
        return $member;
    }

    #[Route('/download/receipt/{id}', name: 'download_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Member $member,
                                MemberService $memberService): Response
    {
        set_time_limit(0);
        $content = $memberService->generateRegistrationReceipt($member);
        return new PdfResponse($content, 'recu_synacvtcci.pdf');
    }

    #[Route('/preinscription/{id}/confirmation', name: 'presubscribe_confirm')]
    public function presubscribeConfirmation (Member $member,
                                              Request $request,
                                              MemberRepository $memberRepository): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $montant_frais = $this->getParameter('montant_frais');
        $montant_souscription_syndicat = $this->getParameter('montant_souscription_syndicat');
        $montant = $request->get('payforsyndicat') ?  ($montant_souscription_syndicat + $montant_frais) : $montant_frais;
        return $this->render('frontend/member/self_subscription_confirmation.html.twig', [
            'id' => $member->getId(),
            'montant' => $montant
        ]);
    }


    #[Route('/preinscription/{tracking_code}', name: 'presubscribe'), ]
    public function presubscribe (string $tracking_code,
                                  Request $request,
                                  MemberRepository $memberRepository): Response
    {
        date_default_timezone_set("Africa/Abidjan");

        $member = $memberRepository->findOneBy(['tracking_code' => $tracking_code]);
        $form = $this->createForm(MemberRegistrationType::class, $member);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid()) {
            if(!in_array($member->getStatus(),['PAID', 'COMPLETED', 'SUCCESS'])){
                $mr = $request->request->all()['member_registration'];
                $payForSyndicat = (array_key_exists('payforsyndicat', $mr) ) ? $mr['payforsyndicat']: null;
            }
            $member->setTrackingCode($tracking_code);
            $memberRepository->add($member, true);
            return $this->redirectToRoute('presubscribe_confirm', ['id' => $member->getId(), 'payforsyndicat' => $payForSyndicat]);
        }

        return $this->renderForm('frontend/member/self_subscription.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }


    #[Route(path: '/do/{id}', name: 'do_public_payment')]
    public function doPayment(Member $member,
                              Request $request,
                              WaveService       $waveService,
                              PaymentRepository $paymentRepository): Response
    {
        if(in_array($member->getStatus(), ["COMPLETED","SUCCEEDED", "PAID", "CLOSED"])) return $this->redirectToRoute('home');
        $response = $waveService->makePayment($request->get('montant') ?? $this->getParameter('montant'));
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
        } else return $this->redirectToRoute('home');
    }

    #[Route(path: '/receipt/{id}', name: 'display_receipt', methods: ['POST', 'GET'])]
    public function showPaymentReceipt(?Payment $payment,
                                       PaymentService $paymentService): Response
    {
        if (in_array($payment->getStatus(), ["COMPLETED","SUCCEEDED", "PAID", "CLOSED"])) {
            $paymentService->generatePaymentReceipt($payment);
            return $this->render('admin/payment/receipt.html.twig', ['payment' => $payment]);
        }
        return $this->redirectToRoute('admin_index');
    }

}
