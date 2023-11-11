<?php

namespace App\Controller\Admin;

use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class PageController extends AbstractController
{
    #[Route(path: '', name: 'admin_index')]
    public function index(Request $request, MemberRepository $memberRepository): Response
    {
//        $members = $memberRepository->findAll();
//        return $this->render('admin/pages/index.html.twig', ["members" => $members]);
        return $this->render('admin/member/synacvtcci/index.html.twig');
    }
    #[Route(path: '/search', name: 'admin_index_search')]
    public function chooseMain(Request $request, MemberRepository $memberRepository): Response
    {
        $searchTerm = $request->get('searchTerm');
        if($searchTerm){
            $member = $memberRepository->findOneBy(['reference' => strtolower($searchTerm)]);
            if($member) return $this->redirectToRoute('admin_member_edit', ['id' => $member->getId()]);
            else{
                $data = ['result' => 'error'];
                return $this->render('admin/pages/search-index.html.twig', ["data" => $data]);
            }
        }
        return $this->render('admin/pages/search-index.html.twig',["data" => null]);
    }
}
