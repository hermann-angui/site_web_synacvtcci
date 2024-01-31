<?php

namespace App\Controller\Client;

use App\Entity\Child;
use App\Entity\Member;
use App\Form\MemberOnlineRegistrationType;
use App\Form\MemberRegistrationType;
use App\Repository\MemberRepository;
use App\Service\Member\MemberService;
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
    public function success(Member $member, MemberService $memberService): Response
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
    public function registerMember (Request $request, MemberService $memberService): Response
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
    public function memberProfile(Request $request, MemberRepository $memberRepository): Response
    {
        $member = $memberRepository->findOneBy(["matricule" => $request->get("matricule")]);
        if($member)  return $this->render('admin/member/synacvtcci/public_profile.html.twig', ["member" => $member]);
        else return $this->redirectToRoute('home');
    }

    #[Route('/cnmci/{id}', name: 'member_cncmi_sticker', methods: ['GET'])]
    public function formCnmciShow($id, MemberRepository $memberRepository): Response
    {
        $member = $memberRepository->findOneBy(['code_sticker' => $id]);
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
    public function pdfGenerate(Member $member, MemberService $memberService): Response
    {
        set_time_limit(0);
        $content = $memberService->generateRegistrationReceipt($member);
        return new PdfResponse($content, 'recu_synacvtcci.pdf');
    }

    #[Route('/preinscription/{tracking_code}', name: 'presubscribe', methods: ['GET']), ]
    public function presubscribe (string $tracking_code, Request $request, MemberRepository $memberRepository): Response
    {
        date_default_timezone_set("Africa/Abidjan");

        $member = $memberRepository->findOneBy(['tracking_code' => $tracking_code]);
        $form = $this->createForm(MemberRegistrationType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $memberRepository->add($member, true);

            return $this->redirectToRoute('home');
        }

        return $this->renderForm('frontend/member/self_subscription.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }
}
