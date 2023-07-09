<?php

namespace App\Controller\Client;

use App\DTO\ChildDto;
use App\DTO\MemberRequestDto;
use App\Entity\Artisan;
use App\Form\ArtisanType;
use App\Form\MemberRegistrationType;
use App\Repository\MemberRepository;
use App\Service\Artisan\ArtisanService;
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
        return $this->render('frontend/member/success.html.twig');
    }

    #[Route(path: '/register', name: 'register_member')]
    public function registerMember (Request $request, MemberService $memberService): Response
    {
        $flashInfos = [
            "Suite à la bastonnade d'un chauffeur à ..",
            "La SYNACVTCCI apporte son assistance au chauffeur bastonné ..",
            "La SYNACVTCCI signe une convetion avec la maison d'assurance santé VITAS Santé",
        ];
        $memberRequestDto = new MemberRequestDto();
        $form = $this->createForm(MemberRegistrationType::class, $memberRequestDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $memberRequestDto->setPhoto($form->get('photo')->getData());
            $memberRequestDto->setPhotoPieceFront($form->get('photoPieceFront')->getData());
            $memberRequestDto->setPhotoPieceBack($form->get('photoPieceBack')->getData());
            $memberRequestDto->setPhotoPermisFront($form->get('photoPermisFront')->getData());
            $memberRequestDto->setPhotoPermisBack($form->get('photoPermisBack')->getData());

            $data = $request->request->all();

            if(is_array($data) && isset($data['child_lastname'])) {
                $count = count($data['child_lastname']);
                for($i = 0; $i < $count ; $i++){
                    $childDto =  new ChildDto();
                    $childDto->setLastName($data['child_lastname'][$i]);
                    $childDto->setFirstName($data['child_firstname'][$i]);
                    $childDto->setSex($data['child_sex'][$i]);
                    $childDto->setParent($memberRequestDto);
                    $memberRequestDto->addChild($childDto);
                }
            }
            $memberRequestDto->setStatus("PENDING");
            $memberService->createMember($memberRequestDto);

            return $this->redirectToRoute('success',[], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('frontend/member/register.html.twig', [
            "flashInfos" => $flashInfos,
            'member' => $memberRequestDto,
            'form' => $form,
        ]);
    }

    #[Route(path: '/artisan/register/{step}', name: 'register_artisan')]
    public function registerArtisan (int $step, Request $request, ArtisanService $artisanService): Response
    {
        $flashInfos = [
            "Suite à la bastonnade d'un chauffeur à ..",
            "La SYNACVTCCI apporte son assistance au chauffeur bastonné ..",
            "La SYNACVTCCI signe une convetion avec la maison d'assurance santé VITAS Santé",
        ];
        return $this->renderForm("frontend/artisan/register-step-${step}.html.twig", [
            "flashInfos" => $flashInfos
        ]);
    }

    #[Route(path: '/artisan/ajax_register/{step}', name: 'ajax_register_artisan')]
    public function ajaxRegisterArtisan (int $step, Request $request, ArtisanService $artisanService): Response
    {
        $data = $request->request->all();
        $files = $request->files->all();
        return $this->json('');
    }
    #[Route(path: '/profile/{matricule}', name: 'public_member_profile')]
    public function memberProfile(Request $request, MemberRepository $memberRepository): Response
    {
        $member = $memberRepository->findOneBy(["matricule" => $request->get("matricule")]);
        return $this->render('admin/member/public_profile.html.twig', ["member" => $member]);
    }
}
