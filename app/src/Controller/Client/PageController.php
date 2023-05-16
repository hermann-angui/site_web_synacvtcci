<?php

namespace App\Controller\Client;

use App\DTO\ChildDto;
use App\DTO\MemberRequestDto;
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
        return $this->render('frontend/pages/index.html.twig');
    }

    #[Route(path: '/success', name: 'success')]
    public function success(Request $request): Response
    {
        return $this->render('frontend/member/success.html.twig');
    }

    #[Route(path: '/register', name: 'register_member')]
    public function registerMember (Request $request, MemberService $memberService): Response
    {
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

            if(is_array($data) && isset($data['child_lastname']))
            {
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
            'member' => $memberRequestDto,
            'form' => $form,
        ]);
    }

    #[Route(path: '/profile/{matricule}', name: 'public_member_profile')]
    public function memberProfile(Request $request, MemberRepository $memberRepository): Response
    {
        $member = $memberRepository->findOneBy(["matricule" => $request->get("matricule")]);
        return $this->render('admin/member/public_profile.html.twig', ["member" => $member]);
    }
}
