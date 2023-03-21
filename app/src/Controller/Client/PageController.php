<?php

namespace App\Controller\Client;

use App\Entity\Member;
use App\Form\MemberRegistrationType;
use App\Helper\MemberHelper;
use App\Helper\PasswordHelper;
use App\Repository\MemberRepository;
use App\Traits\MobilePaymentTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    use MobilePaymentTrait;

    #[Route(path: '/', name: 'home')]
    public function home(Request $request): Response
    {
        return $this->render('frontend/pages/index.html.twig');
    }

    #[Route(path: '/register', name: 'register_member')]
    public function registerMember(Request $request,
                                   MemberRepository $memberRepository,
                                   MemberHelper $memberHelper,
                                   UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $member = new Member();
        $form = $this->createForm(MemberRegistrationType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            date_default_timezone_set('Africa/Abidjan');
            $member->setRoles(['ROLE_USER']);
            $date = new \DateTime('now');
            $member->setSubscriptionDate($date);

            $sexCode = "SY1";
            if($member->getSex() === "H") $sexCode = "SY1";
            if($member->getSex() === "F") $sexCode = "SY2";

            $expiredDate = $date->add(new \DateInterval("P1Y"));
            $member->setSubscriptionExpireDate(new \DateTime($expiredDate->format('Y-12-31')));

            $member->setPassword( $userPasswordHasher->hashPassword(
                $member,
                PasswordHelper::generate()
            ));
            $memberRepository->add($member, true);

            $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $member->getId());
            $member->setMatricule($matricule);

            if($form->get('photo')->getData()){
                $fileName = $memberHelper->uploadAsset($form->get('photo')->getData(), $member);
                if($fileName) $member->setPhoto($fileName);
            }

            if($form->get('photoPieceFront')->getData()){
                $fileName = $memberHelper->uploadAsset($form->get('photoPieceFront')->getData(), $member);
                if($fileName) $member->setPhotoPieceFront($fileName);
            }

            if($form->get('photoPieceBack')->getData()){
                $fileName = $memberHelper->uploadAsset($form->get('photoPieceBack')->getData(), $member);
                if($fileName) $member->setPhotoPieceBack($fileName);
            }

            if($form->get('photoPermisFront')->getData()){
                $fileName = $memberHelper->uploadAsset($form->get('photoPermisFront')->getData(), $member);
                if($fileName) $member->setPhotoPermisFront($fileName);
            }

            if($form->get('photoPermisBack')->getData()){
                $fileName = $memberHelper->uploadAsset($form->get('photoPermisBack')->getData(), $member);
                if($fileName) $member->setPhotoPermisBack($fileName);
            }

            $memberRepository->add($member, true);

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('frontend/member/new.html.twig', [
            'member' => $member,
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
