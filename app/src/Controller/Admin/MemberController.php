<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Form\MemberRegistrationType;
use App\Form\MemberType;
use App\Helper\CsvReaderHelper;
use App\Helper\FileUploadHelper;
use App\Helper\MemberHelper;
use App\Helper\PasswordHelper;
use App\Repository\MemberRepository;
use App\Service\MemberCardGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/member')]
class MemberController extends AbstractController
{
    #[Route('', name: 'admin_member_index', methods: ['GET'])]
    public function index(Request $request, MemberRepository $memberRepository): Response
    {
        $filter = $request->get('filter');

        if($filter==='chauffeurs') $members = $memberRepository->findMembresChauffeur();
        elseif($filter==='bureau') $members = $memberRepository->findMembresBureau();
        else $members = $memberRepository->findAll();

        return $this->render('admin/member/index.html.twig', ['members' => $members]);
    }

    #[Route('/new', name: 'admin_member_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        MemberRepository $memberRepository,
                        MemberHelper $memberHelper,
                        UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberRegistrationType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member->setRoles(['ROLE_USER']);
            $date = new \DateTime('now');
            $member->setSubscriptionDate($date);

            $sexCode = "SY1";
            if($member->getSex() === "M") $sexCode = "SY1";
            if($member->getSex() === "F") $sexCode = "SY2";

            $expiredDate = $date->add(new \DateInterval("P1Y"));
            $member->setSubscriptionExpireDate($expiredDate);

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

            return $this->redirectToRoute('admin_member_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/member/new.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }


    #[Route('/upload', name: 'admin_member_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request, FileUploadHelper $fileUploadHelper): Response
    {
       /* @var UploadedFile $file */
        if(!empty($file = $request->files->get('file'))){
            $mime = $file->getMimeType();
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
            if(in_array($mime, ['image/png','image/jpeg','image/jpg','image/gif','text/csv','text/plain'])){
                $fileUploadHelper->upload($file, $uploadDir,true);
            }
        }
        return $this->renderForm('admin/member/upload.html.twig');
    }

    #[Route('/import', name: 'admin_member_import', methods: ['GET', 'POST'])]
    public function import(Request $request,
                           MemberHelper $memberHelper,
                           MemberRepository $memberRepository,
                           UserPasswordHasherInterface $userPasswordHasher,
                           CsvReaderHelper $csvReaderHelper): Response
    {
        $finder = new Finder();
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
        $csvFiles = $finder->in($uploadDir)->name('*.csv');
        foreach($csvFiles as $file) {
           $rows =  $csvReaderHelper->read($file);
           foreach ($rows as $row){
               try{
                   $date = new \DateTime('now');
                   $expiredDate = $date->add(new \DateInterval("P1Y"));

                   $sexCode = "SY1";
                   if(!empty($row["SEXE"])) {
                       if($row["SEXE"] === "M") $sexCode = "SY1";
                       if($row["SEXE"] === "F") $sexCode = "SY2";
                   }

                   $member = new Member();

                   $member->setRoles(['ROLE_USER']);
                   if(array_key_exists("SEXE",$row)) $member->setSex(mb_strtoupper($row["SEXE"], 'UTF-8'));
                   if(array_key_exists("EMAIL",$row)) $member->setEmail(trim($row["EMAIL"]));
                   if(array_key_exists("NOM",$row)) $member->setLastName(mb_strtoupper(trim($row["NOM"]), 'UTF-8'));
                   if(array_key_exists("PRENOMS",$row)) $member->setFirstName(mb_strtoupper(trim($row["PRENOMS"]),'UTF-8'));
                   if(array_key_exists("DATE_NAISSANCE",$row)) $member->setDateOfBirth(new \DateTime($row["DATE_NAISSANCE"]));
                   if(array_key_exists("LIEU_NAISSANCE",$row)) $member->setBirthCity($row["LIEU_NAISSANCE"]);
                   if(array_key_exists("NUMERO_PERMIS",$row)) $member->setDrivingLicenseNumber($row["NUMERO_PERMIS"]);
                   if(array_key_exists("NUMERO_PIECE",$row)) $member->setIdNumber($row["NUMERO_PIECE"]);
                   if(array_key_exists("TYPE_PIECE",$row)) $member->setIdType($row["TYPE_PIECE"]);
                   if(array_key_exists("PAYS",$row)) $member->setCountry($row["PAYS"]);
                   if(array_key_exists("VILLE",$row)) $member->setCity(mb_strtoupper($row["VILLE"], 'UTF-8'));
                   if(array_key_exists("COMMUNE",$row)) $member->setCommune(mb_strtoupper($row["COMMUNE"], 'UTF-8'));
                   if(array_key_exists("MOBILE",$row)) $member->setMobile($row["MOBILE"]);
                   if(array_key_exists("FIXE",$row)) $member->setPhone($row["FIXE"]);
                   if(array_key_exists("TITRE",$row)) $member->setTitre($row["TITRE"]);

                   $member->setPassword( $userPasswordHasher->hashPassword(
                       $member,
                       PasswordHelper::generate()
                   ));

                   if(isset($row["DATE_SOUSCRIPTION"])) $member->setSubscriptionDate($date);
                   else $member->setSubscriptionDate(new \DateTime($row["DATE_SOUSCRIPTION"])) ;

                   if(isset($row["DATE_EXPIRATION_SOUSCRIPTION"])) $member->setSubscriptionExpireDate(new \DateTime($row["DATE_EXPIRATION_SOUSCRIPTION"])) ;
                   else $member->setSubscriptionExpireDate($expiredDate);

                   $memberRepository->add($member, true);

                   $matricule = $row["MATRICULE"];
                   if(!empty($matricule))$member->setMatricule($matricule);
                   else{
                       $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $member->getId());
                       $member->setMatricule($matricule);
                   }
                   $exist = $memberRepository->findOneBy(['matricule'=>$matricule]);
                   if(!$exist) {
                       if(!empty(($row["PHOTO"]))){
                           $photo = new File($uploadDir . $row["PHOTO"], false);
                           if(file_exists($photo->getPathname())) {
                               $fileName = $memberHelper->uploadAsset($photo, $member);
                               if($fileName) $member->setPhoto($fileName);
                           }
                       }

                       if(!empty($row["PHOTO_PIECE_RECTO"])){
                           $photo = new File($uploadDir . $row["PHOTO_PIECE_RECTO"], false);
                           if(file_exists($photo->getPathname())) {
                               $fileName = $memberHelper->uploadAsset($photo, $member);
                               if($fileName) $member->setPhotoPieceFront($fileName);
                           }
                       }

                       if(!empty($row["PHOTO_PIECE_VERSO"])){
                           $photo = new File($uploadDir . $row["PHOTO_PIECE_VERSO"], false);
                           if(file_exists($photo->getPathname())) {
                               $fileName = $memberHelper->uploadAsset($photo, $member);
                               if($fileName) $member->setPhotoPieceBack($fileName);
                           }
                       }

                       if(!empty($row["PHOTO_PERMIS_RECTO"])){
                           $photo = new File($uploadDir . $row["PHOTO_PERMIS_RECTO"], false);
                           if(file_exists($photo->getPathname())) {
                               $fileName = $memberHelper->uploadAsset($photo, $member);
                               if($fileName) $member->setPhotoPermisFront($fileName);
                           }
                       }

                       if(!empty($row["PHOTO_PERMIS_VERSO"])){
                           $photo = new File($uploadDir . $row["PHOTO_PERMIS_VERSO"], false);
                           if(file_exists($photo->getPathname())) {
                               $fileName = $memberHelper->uploadAsset($photo, $member);
                               if($fileName) $member->setPhotoPermisBack($fileName);
                           }
                       }
                       $memberRepository->add($member, true);
                   }else{
                       $memberRepository->remove($member, true);
                   }
               }
               catch(\Exception $e){
                   continue;
               }
           }
        }
        return $this->redirectToRoute('admin_member_index');
    }

    #[Route('/generate/new/card/{id}', name: 'admin_member_generate_card', methods: ['GET'])]
    public function generateCard(Member $member,
                                 MemberRepository $memberRepository,
                                 MemberCardGeneratorService $memberCardGeneratorService): Response
    {
        if($member){
            $cardImage = basename($memberCardGeneratorService->generate($member));
            $member->setCardPhoto($cardImage);
            $member->setModifiedAt(new \DateTime());
            $memberRepository->add($member,true);
            return $this->render('admin/member/show_card.html.twig', ['member' => $member]);
        } else {
            $members = $memberRepository->findAll();
            foreach($members as $member){
                $cardImage = basename($memberCardGeneratorService->generate($member));
                $member->setCardPhoto($cardImage);
                $member->setModifiedAt(new \DateTime());
                $memberRepository->add($member,true);
            }
            return $this->render('admin/member/show_card.html.twig', ['members' => $members]);
        }
    }

    #[Route('/show/card/{id}', name: 'admin_member_show_card', methods: ['GET'])]
    public function showCard(Member $member): Response
    {
         return $this->render('admin/member/show_card.html.twig', ['member' => $member]);
    }

    #[Route('/download/card/{id}', name: 'admin_member_download_card', methods: ['GET'])]
    public function downloadCard(Member $member): Response
    {
        $cardPhotoRealPath = $this->getParameter('kernel.project_dir') . "/public/members/" . $member->getMatricule() . "/" . $member->getCardPhoto();
        return new BinaryFileResponse($cardPhotoRealPath);
    }

    #[Route('/download/cards', name: 'admin_member_download_cards', methods: ['GET'])]
    public function downloadMemberCards(Request $request, MemberRepository $memberRepository, MemberCardGeneratorService $memberCardGeneratorService): Response
    {
        $zipArchive = new \ZipArchive();
        $zipFile = $this->getParameter('kernel.project_dir') . '/public/members/tmp/members.zip';
        if(file_exists($zipFile)) unlink($zipFile);
        if($zipArchive->open($zipFile, \ZipArchive::CREATE) === true)
        {
            $members = $memberRepository->findAll();
            foreach($members as $member)
            {
                if(empty($member->getPhoto())) continue;
                $cardImage = basename($memberCardGeneratorService->generate($member));
                $member->setCardPhoto($cardImage);
                $member->setModifiedAt(new \DateTime());
                $memberRepository->add($member,true);

                $cardPhotoRealPath = $this->getParameter('kernel.project_dir') . "/public/members/" . $member->getMatricule() . "/" . $member->getCardPhoto();
                if(is_file($cardPhotoRealPath)) $zipArchive->addFile($cardPhotoRealPath, $member->getMatricule() );
            }
            $zipArchive->close();
            return new BinaryFileResponse($zipFile);
        }
        return new BinaryFileResponse(null);
    }

    #[Route('/download/sample', name: 'admin_member_sample_file', methods: ['GET'])]
    public function downloadSample(Request $request): Response
    {
        $sampleRealPath = $this->getParameter('kernel.project_dir') . "/public/assets/files/sample.csv";
        return new BinaryFileResponse($sampleRealPath);
    }


    #[Route('/{id}', name: 'admin_member_show', methods: ['GET'])]
    public function show(Member $member): Response
    {
        return $this->render('admin/member/show.html.twig', [
            'member' => $member,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_member_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,
                         Member $member,
                         MemberHelper $memberHelper,
                         MemberRepository $memberRepository): Response
    {
        $form = $this->createForm(MemberRegistrationType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($form->get('photo')->getData()){
                $fileName = $memberHelper->uploadAsset($form->get('photo')->getData(), $member);
                if($fileName) $member->setPhoto($fileName);
            }

            if($sex = $form->get('sex')->getData()){
                $sexChar = substr( $member->getMatricule(),2, 1);
                if($sexChar == '1' && $sex = 'F') $member->setMatricule(str_replace('SY1', 'SY2',$member->getMatricule() ));
                if($sexChar == '2' && $sex = 'H') $member->setMatricule(str_replace('SY2', 'SY1',$member->getMatricule() ));
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

            return $this->redirectToRoute('admin_member_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/member/edit.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_member_delete', methods: ['POST'])]
    public function delete(Request $request, Member $member, MemberRepository $memberRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token'))) {
            $memberRepository->remove($member, true);
        }

        return $this->redirectToRoute('admin_member_index', [], Response::HTTP_SEE_OTHER);
    }
}
