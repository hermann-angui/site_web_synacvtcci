<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class PageController extends AbstractController
{
    #[Route(path: '', name: 'admin_index')]
    public function index(Request $request, MemberRepository $memberRepository): Response
    {
        return $this->render('admin/pages/index.html.twig');
    }

    #[Route(path: '/dashboard', name: 'admin_dashboard')]
    public function dashboard(Request $request, MemberRepository $memberRepository): Response
    {
        return $this->render('admin/pages/dashboard.html.twig');
    }

    #[Route(path: '/generate/tracking_codes', name: 'admin_generate_tracking_codes')]
    public function generateTrackingCodes(Request $request, MemberRepository $memberRepository): Response
    {
        $member = $memberRepository->getLast();
        $from = (int)ltrim($member->getTrackingCode(), '0');
        $trackingCodes = array_map(function($num){
            return sprintf('%05d', $num);
        }, range(++$from , $from + 19 ));

        if($request->isMethod('POST')){
            foreach($trackingCodes as $trackingCode){
                $member = new Member();
                $member->setTrackingCode($trackingCode);
                $memberRepository->add($member, true);
            }
            return $this->redirectToRoute('admin_member_search');
        }else{
            return $this->render('admin/pages/generate_tracking_codes.html.twig', [
                'tracking_codes' => $trackingCodes
            ]);
        }
    }
}
