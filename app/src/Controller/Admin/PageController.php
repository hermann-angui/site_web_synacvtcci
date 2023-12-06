<?php

namespace App\Controller\Admin;

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
        $trackingCodes = [];
        $from = $request->get('from') ;
        $to = $request->get('to') ;
        if($from && $to){
            $from = (int)ltrim($from, '0');
            $to = (int)ltrim($to, '0');
            $trackingCodes = array_map(function($num){
                return sprintf('05d', $num);
            }, range($from, $to));
        }
        return $this->render('admin/pages/generate_tracking_codes.html.twig', ['tracking_codes' => $trackingCodes]);
    }
}
