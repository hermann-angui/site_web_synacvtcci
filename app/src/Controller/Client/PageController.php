<?php

namespace App\Controller\Client;

use App\Entity\Member;
use App\Form\MemberRegistrationType;
use App\Repository\MemberRepository;
use App\Service\Member\MemberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route(path: '/success', name: 'success')]
    public function success(Request $request): Response
    {
        $flashInfos = [
            "Suite à la bastonnade d'un chauffeur à ..",
            "La SYNACVTCCI apporte son assistance au chauffeur bastonné ..",
            "La SYNACVTCCI signe une convetion avec la maison d'assurance santé VITAS Santé",
        ];
        return $this->render('frontend/member/success.html.twig', ["flashInfos" => $flashInfos]);
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
        $form = $this->createForm(MemberRegistrationType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $result = $memberService->handleCnmciFormSubmit($request,$form, $member );
            return $this->redirectToRoute('success',[], Response::HTTP_SEE_OTHER);
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
        return $this->render('admin/member/synacvtcci/public_profile.html.twig', ["member" => $member]);
    }

    #[Route('/cnmci/{id}', name: 'member_cncmi_sticker', methods: ['GET'])]
    public function formCnmciShow($id, MemberRepository $memberRepository): Response
    {
        $member = $memberRepository->findOneBy(['codeSticker' => $id]);
        return $this->render('admin/member/cnmci/cnmci_show_sticker.html.twig', ['member' => $member]);
    }
}
