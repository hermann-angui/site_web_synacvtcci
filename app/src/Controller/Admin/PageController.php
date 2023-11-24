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

}
