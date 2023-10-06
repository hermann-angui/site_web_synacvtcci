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
        $members = $memberService->getAllMembers();

        foreach($members as $member){
            if (empty($member->getReference())){
                $member->setReference(
                    str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18))
                );
                $memberService->save($member);
            }

            $sourceDir =  "/var/www/html/public/members/" . $member->getMatricule() . "/";
            $destDir =  "/var/www/html/public/members/" . $member->getReference() . "/";

            if(!file_exists($sourceDir)) continue;
            if(!file_exists($destDir)) mkdir($destDir, 0777, true);

            $f = new Finder();
            $files = $f->in($sourceDir)->name('*.*')->files();
            foreach($files as $file) {
                $fs = new Filesystem();
                $fs->copy($file->getRealPath(), $destDir . '/' . $file->getFilename());
                if($file->getFilename() === $member->getPhoto()) {
                    // $thumbnail = new File($destDir . substr($member->getPhoto(), 0,-4) . "_thumbnail." . $file->getExtension(),false );
                    $thumbnail = new File($destDir . $member->getPhoto(),false );
                    $memberService->createThumbnail($thumbnail, $member, 100, 100);
                }
            }
        }
        return $this->json([]);
    }
}
