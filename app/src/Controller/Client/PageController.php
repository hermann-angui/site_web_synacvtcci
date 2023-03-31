<?php

namespace App\Controller\Client;

use App\Entity\Child;
use App\Entity\Member;
use App\Form\MemberRegistrationType;
use App\Helper\MemberHelper;
use App\Helper\PasswordHelper;
use App\Repository\ChildRepository;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Snappy\Pdf;

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
    public function registerMember (Request $request,
                                   MemberRepository $memberRepository,
                                   ChildRepository $childRepository,
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

            if($form->get('quartier')->getData()){
                $member->setQuartier($form->get('quartier')->getData());
            }

            if($form->get('commune')->getData()){
                $member->setQuartier($form->get('commune')->getData());
            }

            if($form->get('partner_first_name')->getData()){
                $member->setPartnerFirstName($form->get('partner_first_name')->getData());
            }

            if($form->get('partner_last_name')->getData()){
                $member->setPartnerLastName($form->get('partner_last_name')->getData());
            }

            if($form->get('whatsapp')->getData()){
                $member->setWhatsapp($form->get('whatsapp')->getData());
            }

            if($form->get('company')->getData()){
                $member->setCompany($form->get('company')->getData());
            }

            if($form->get('photoPermisBack')->getData()){
                $fileName = $memberHelper->uploadAsset($form->get('photoPermisBack')->getData(), $member);
                if($fileName) $member->setPhotoPermisBack($fileName);
            }

            $cl = $request->get('child_lastname');
            if(is_array($cl)){
                $count = count($cl);
                for($i =0; $i < $count ; $i++){
                    $child =  new Child();
                    $child->setLastName($request->get('child_lastname')[$i]);
                    $child->setFirstName($request->get('child_firstname')[$i]);
                    $child->setSex($request->get('child_sex')[$i]);
                    $child->setParent($member);
                    $childRepository->add($child);
                }
            }

            $memberRepository->add($member, true);

            return $this->redirectToRoute('success',[], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('frontend/member/register.html.twig', [
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
