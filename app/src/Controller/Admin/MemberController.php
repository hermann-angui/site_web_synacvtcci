<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Form\MemberRegistrationType;
use App\Helper\CsvReaderHelper;
use App\Helper\DataTableHelper;
use App\Helper\FileUploadHelper;
use App\Helper\MemberHelper;
use App\Helper\PasswordHelper;
use App\Repository\MemberRepository;
use App\Service\MemberCardGeneratorService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $members = $memberRepository->findAll();

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
            $memberRepository->setAutoIncrementToLast($memberRepository->getLastRowId());
            $member->setRoles(['ROLE_USER']);
            date_default_timezone_set("Africa/Abidjan");
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
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(3600);
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

        set_time_limit(3600);
        $finder = new Finder();
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
        $csvFiles = $finder->in($uploadDir)->name('*.csv');
        foreach($csvFiles as $file) {
           $rows =  $csvReaderHelper->read($file);
           $memberRepository->setAutoIncrementToLast($memberRepository->getLastRowId());
           foreach ($rows as $row){
               try{
                   date_default_timezone_set("Africa/Abidjan");
                   $date = new \DateTime('now');

                   $sexCode = "SY1";
                   if(!empty($row["SEXE"])) {
                       if($row["SEXE"] === "M") $sexCode = "SY1";
                       if($row["SEXE"] === "F") $sexCode = "SY2";
                   }else{
                       throw new \Exception("Skip"); // Unable to determine sex so skip because it is not possible to generate matricule
                   }

                   $member = new Member();

                   $member->setRoles(['ROLE_USER']);
                   if(isset($row["SEXE"])) $member->setSex(mb_strtoupper($row["SEXE"], 'UTF-8'));
                   if(isset($row["EMAIL"])) $member->setEmail(trim($row["EMAIL"]));
                   if(isset($row["NOM"])) $member->setLastName(mb_strtoupper(trim($row["NOM"]), 'UTF-8'));
                   if(isset($row["COMPAGNIE"])) $member->setCompany(mb_strtoupper(trim($row["COMPAGNIE"]), 'UTF-8'));
                   if(isset($row["NATIONALITE"])) $member->setLastName(mb_strtoupper(trim($row["NATIONALITE"]), 'UTF-8'));
                   if(isset($row["PRENOMS"])) $member->setFirstName(mb_strtoupper(trim($row["PRENOMS"]),'UTF-8'));
                   if(isset($row["DATE_NAISSANCE"])) $member->setDateOfBirth(new \DateTime($row["DATE_NAISSANCE"]));
                   if(isset($row["LIEU_NAISSANCE"])) $member->setBirthCity(mb_strtoupper(trim($row["LIEU_NAISSANCE"])));
                   if(isset($row["NUMERO_PERMIS"])) $member->setDrivingLicenseNumber($row["NUMERO_PERMIS"]);
                   if(isset($row["NUMERO_PIECE"])) $member->setIdNumber($row["NUMERO_PIECE"]);
                   if(isset($row["TYPE_PIECE"])) $member->setIdType(mb_strtoupper(trim($row["TYPE_PIECE"])));
                   if(isset($row["PAYS"])) $member->setCountry(mb_strtoupper(trim($row["PAYS"])));
                   if(isset($row["VILLE"])) $member->setCity(mb_strtoupper($row["VILLE"], 'UTF-8'));
                   if(isset($row["COMMUNE"])) $member->setCommune(mb_strtoupper($row["COMMUNE"], 'UTF-8'));
                   if(isset($row["MOBILE"])) $member->setMobile($row["MOBILE"]);
                   if(isset($row["FIXE"])) $member->setPhone($row["FIXE"]);
                   if(isset($row["TITRE"])) $member->setTitre(mb_strtoupper(trim($row["TITRE"])));

                   $member->setPassword( $userPasswordHasher->hashPassword(
                       $member,
                       PasswordHelper::generate()
                   ));

                   if(array_key_exists("DATE_SOUSCRIPTION", $row)) {
                       if(empty($row["DATE_SOUSCRIPTION"])) $member->setSubscriptionDate($date);
                       else $member->setSubscriptionDate(new \DateTime($row["DATE_SOUSCRIPTION"])) ;
                   }

                   if(array_key_exists("DATE_EXPIRATION_SOUSCRIPTION", $row)){
                       $expiredDate = new \DateTime($row["DATE_SOUSCRIPTION"]);
                       $expiredDate = $expiredDate->add(new \DateInterval("P1Y"));
                       $expiredDate = $expiredDate->format('Y-12-31');
                       if(!empty($row["DATE_EXPIRATION_SOUSCRIPTION"])) $member->setSubscriptionExpireDate(new \DateTime($row["DATE_EXPIRATION_SOUSCRIPTION"])) ;
                       else $member->setSubscriptionExpireDate(new \DateTime($expiredDate));
                   }

                   $memberRepository->add($member, true);

                   $exist = null;
                   if(array_key_exists("MATRICULE", $row)) {
                       $matricule = $row["MATRICULE"];
                       if(!empty($matricule)) $member->setMatricule($matricule);
                       else{
                           $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $member->getId());
                           $member->setMatricule($matricule);
                       }
                       $exist = $memberRepository->findOneBy(['matricule'=>$matricule]);
                   }

                   if(!$exist) {
                       if(isset($row["PHOTO"]) && !empty($row["PHOTO"])){
                           $photo = new File($uploadDir . $row["PHOTO"], false);
                           if(file_exists($photo->getPathname())) {
                               $fileName = $memberHelper->uploadAsset($photo, $member);
                               if($fileName) $member->setPhoto($fileName);
                           }
                       }

                       if(isset($row["PHOTO_PIECE_RECTO"]) && !empty($row["PHOTO_PIECE_RECTO"])){
                           $photo = new File($uploadDir . $row["PHOTO_PIECE_RECTO"], false);
                           if(file_exists($photo->getPathname())) {
                               $fileName = $memberHelper->uploadAsset($photo, $member);
                               if($fileName) $member->setPhotoPieceFront($fileName);
                           }
                       }

                       if(isset($row["PHOTO_PIECE_VERSO"]) && !empty($row["PHOTO_PIECE_VERSO"])){
                           $photo = new File($uploadDir . $row["PHOTO_PIECE_VERSO"], false);
                           if(file_exists($photo->getPathname())) {
                               $fileName = $memberHelper->uploadAsset($photo, $member);
                               if($fileName) $member->setPhotoPieceBack($fileName);
                           }
                       }

                       if(isset($row["PHOTO_PERMIS_RECTO"]) && !empty($row["PHOTO_PERMIS_RECTO"])){
                           $photo = new File($uploadDir . $row["PHOTO_PERMIS_RECTO"], false);
                           if(file_exists($photo->getPathname())) {
                               $fileName = $memberHelper->uploadAsset($photo, $member);
                               if($fileName) $member->setPhotoPermisFront($fileName);
                           }
                       }

                       if(isset($row["PHOTO_PERMIS_VERSO"]) && !empty($row["PHOTO_PERMIS_VERSO"])){
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
        date_default_timezone_set("Africa/Abidjan");
        if($member){
            $cardImage = basename($memberCardGeneratorService->generate($member));
            $member->setCardPhoto($cardImage);
            $member->setModifiedAt(new \DateTime());
            $memberRepository->add($member,true);
            return $this->render('admin/member/show_card.html.twig', ['member' => $member]);
        } else {
            $members = $memberRepository->findAll();
            foreach($members as $member){
                date_default_timezone_set("Africa/Abidjan");
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
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(3600);
        $cardPhotoRealPath = $this->getParameter('kernel.project_dir') . "/public/members/" . $member->getMatricule() . "/" . $member->getCardPhoto();
        return $this->file($cardPhotoRealPath);
    }

    #[Route('/download/cards', name: 'admin_member_download_cards', methods: ['GET'])]
    public function downloadMemberCards(Request $request, MemberRepository $memberRepository, MemberCardGeneratorService $memberCardGeneratorService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(3600);
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
                if(is_file($cardPhotoRealPath)) $zipArchive->addFile($cardPhotoRealPath, $member->getCardPhoto() );
            }
            $zipArchive->close();
            return $this->file($zipFile);
        }
        return $this->file(null);
    }

    #[Route('/download/sample', name: 'admin_member_sample_file', methods: ['GET'])]
    public function downloadSample(Request $request): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $sampleRealPath = $this->getParameter('kernel.project_dir') . "/public/assets/files/sample.csv";

        if(!file_exists($sampleRealPath)) {
            $columns = [
                "TITRE",
                "MATRICULE",
                "NOM",
                "PRENOMS",
                "PHOTO",
                "SEXE",
                "EMAIL",
                "WHATSAPP",
                "COMPAGNIE",
                "DATE_NAISSANCE",
                "LIEU_NAISSANCE",
                "NUMERO_PERMIS",
                "NUMERO_PIECE",
                "TYPE_PIECE",
                "PAYS",
                "VILLE",
                "COMMUNE",
                "MOBILE",
                "FIXE",
                "QUARTIER",
                "DATE_SOUSCRIPTION",
                "DATE_EXPIRATION_SOUSCRIPTION",
                "PHOTO_PIECE_RECTO",
                "PHOTO_PIECE_VERSO",
                "PHOTO_PERMIS_RECTO",
                "PHOTO_PERMIS_VERSO"
            ];
            $fp = fopen($sampleRealPath, "w+");
            fputcsv($fp, $columns);
            fputcsv($fp, []);
            fclose($fp);

        }
        return $this->file($sampleRealPath, 'sample.csv');
    }

    #[Route('/datatable', name: 'admin_member_datatable', methods: ['GET'])]
    public function datatable(Request $request, Connection $connection, MemberRepository $memberRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'member';
        $primaryKey = 'id';
        $member = null;
        $columns = [
            [
                'db' => 'id',
                'dt' => 'id',
                'formatter' => function( $d, $row ) use ($memberRepository){
                    $member = $memberRepository->find($d);
                    $imageUrl = $member->getMatricule() . "/" .  $member->getPhoto();
                    $content = "<img src='/members/" . $imageUrl . "' alt='' class='avatar-md rounded-circle img-thumbnail'>";
                    return $content;
                }
            ],
            [
                'db' => 'matricule',
                'dt' => 'matricule',
            ],
            [
                'db' => 'last_name',
                'dt' => 'last_name',
            ],
            [
                'db' => 'first_name',
                'dt' => 'first_name',
            ],
            [
                'db' => 'subscription_date',
                'dt' => 'subscription_date'
            ],
            [
                'db' => 'subscription_expire_date',
                'dt' => 'subscription_expire_date'
            ],
            [
                'db' => 'driving_license_number',
                'dt' => 'driving_license_number'
            ],
            [
                'db' => 'id_number',
                'dt' => 'id_number'
            ],
            [
                'db' => 'id_type',
                'dt' => 'id_type'
            ],
            [
                'db' => 'mobile',
                'dt' => 'mobile'
            ],
            [
                'db'        => 'email',
                'dt'        => 'email',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<ul class='list-unstyled hstack gap-1 mb-0'>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                          <a href='/admin/member/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
                                      </li>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='Edit'>
                                         <a href='/admin/member/$id/edit' class='btn btn-sm btn-soft-info'><i class='mdi mdi-pencil-outline'></i></a>
                                      </li>
                                </ul>";
                    return $content;
                }
            ]
        ];

        $sql_details = array(
            'user' => $paramDB['user'],
            'pass' => $paramDB['password'],
            'db'   => $paramDB['dbname'],
            'host' => $paramDB['host']
        );

        $whereResult = '';
        if(!empty($params['matricule'])){
            $whereResult .= " matricule='". $params['matricule'] . "' AND";
        }
        if(!empty($params['driving_license_number'])) {
            $whereResult .= " driving_license_number='". $params['driving_license_number']. "' AND";
        }
        if(!empty($params['id_number'])) {
            $whereResult .= " id_number	='". $params['id_number	'] . "' AND";
        }

        $whereResult = substr_replace($whereResult,'',-strlen(' AND'));

        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
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
        date_default_timezone_set("Africa/Abidjan");
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
