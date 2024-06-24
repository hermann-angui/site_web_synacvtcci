<?php

namespace App\Controller\Admin;

use App\Service\Member\MemberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/debug')]
class TestController extends AbstractController
{
    #[Route(path: '/test', name: 'debug_test')]
    public function test(Request $request, MemberService $memberService): Response
    {
        $memberService->generateAllPhotoThumbnails();
        return $this->json([]);
    }
}
