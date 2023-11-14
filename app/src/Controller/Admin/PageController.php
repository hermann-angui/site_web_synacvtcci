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
        if(in_array("ROLE_AGENT", $this->getUser()->getRoles() ))  {
            return $this->redirectToRoute('admin_index_agent');
        } else {
            $members = $memberRepository->findAll();
            return $this->render('admin/pages/index.html.twig', ["members" => $members]);
        }
    }

    #[Route(path: '/agent', name: 'admin_index_agent')]
    public function indexAgent(Request $request, MemberRepository $memberRepository): Response
    {
            return $this->render('admin/pages/agent-index.html.twig');
    }



}
