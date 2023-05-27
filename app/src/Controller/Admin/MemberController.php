<?php

namespace App\Controller\Admin;

use App\DTO\ChildDto;
use App\DTO\MemberRequestDto;
use App\Entity\Child;
use App\Entity\Member;
use App\Form\MemberRegistrationEditType;
use App\Form\MemberRegistrationType;
use App\Helper\DataTableHelper;
use App\Helper\FileUploadHelper;
use App\Mapper\MemberMapper;
use App\Repository\MemberRepository;
use App\Service\Member\MemberService;
use Doctrine\DBAL\Connection;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

#[Route('admin/member')]
class MemberController extends AbstractController
{

    #[Route('', name: 'admin_member_index', methods: ['GET'])]
    public function index(Request $request, MemberRepository $memberRepository): Response
    {
        return $this->render('admin/member/index.html.twig');
    }

    #[Route('/pdf/{id}', name: 'admin_pdf', methods: ['GET'])]
    public function pdfGenerate(Request $request, Member $member, Pdf $knpSnappyPdf): Response
    {
        $html = $this->renderView('admin/pdf/public_profile.html.twig', array(
            'member'  => $member
        ));

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    #[Route('/new-subscription', name: 'admin_member_new_subscription', methods: ['GET'])]
    public function indexPending(Request $request, MemberRepository $memberRepository): Response
    {
        return $this->render('admin/member/new-subscription.html.twig');
    }

    #[Route('/new', name: 'admin_member_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MemberService $memberService): Response
    {
        $memberRequestDto = new MemberRequestDto();
        $form = $this->createForm(MemberRegistrationType::class, $memberRequestDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $memberRequestDto->setPhoto($form->get('photo')->getData());
            $memberRequestDto->setPhotoPieceFront($form->get('photoPieceFront')->getData());
            $memberRequestDto->setPhotoPieceBack($form->get('photoPieceBack')->getData());
            $memberRequestDto->setPhotoPermisFront($form->get('photoPermisFront')->getData());
            $memberRequestDto->setPhotoPermisBack($form->get('photoPermisBack')->getData());

            $data = $request->request->all();

            if(is_array($data) && isset($data['child_lastname']))
            {
                $count = count($data['child_lastname']);
                for($i = 0; $i < $count ; $i++){
                    $childDto =  new ChildDto();
                    $childDto->setLastName($data['child_lastname'][$i]);
                    $childDto->setFirstName($data['child_firstname'][$i]);
                    $childDto->setSex($data['child_sex'][$i]);
                    $childDto->setParent($memberRequestDto);
                    $memberRequestDto->addChild($childDto);
                }
            }

            $memberService->createMember($memberRequestDto);
            return $this->redirectToRoute('admin_member_index');

        }
        return $this->renderForm('admin/member/new.html.twig', [
            'member' => $memberRequestDto,
            'form' => $form,
        ]);
    }


    #[Route('/upload', name: 'admin_member_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request, FileUploadHelper $fileUploadHelper): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(0);
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
    public function import(Request $request, MemberService $memberService): Response
    {
        $memberService->createMemberFromFile();
        return $this->redirectToRoute('admin_member_index');
    }

    #[Route('/generate/new/card/{id}', name: 'admin_member_generate_card', methods: ['GET'])]
    public function generateCard(Member $member, MemberService $memberService, MemberRepository $memberRepository): Response
    {
        $memberRequestDto = $memberService->generateSingleMemberCard(MemberMapper::MapToMemberRequestDto($member));
        $member->setCardPhoto($memberRequestDto->getCardPhoto()->getFilename());
        $member->setModifiedAt(new \DateTime());
        $memberRepository->add($member, true);
        return $this->render('admin/member/show_card.html.twig', ['member' => $memberRequestDto]);
    }

    #[Route('/show/card/{id}', name: 'admin_member_show_card', methods: ['GET'])]
    public function showCard(Request $request,Member $member): Response
    {
        $memberRequestDto = MemberMapper::MapToMemberRequestDto($member);
         return $this->render('admin/member/show_card.html.twig', ['member' => $memberRequestDto]);
    }

    #[Route('/download/card/{id}', name: 'admin_member_download_card', methods: ['GET'])]
    public function downloadCard(Request $request, Member $member, MemberService $memberService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $memberRequestDto = MemberMapper::MapToMemberRequestDto($member);
        $memberService->generateSingleMemberCard($memberRequestDto);
        $zipFile = $memberService->archiveMemberCards([$memberRequestDto]);
        return $this->file($zipFile);
    }

    #[Route('/download/cards', name: 'admin_member_download_cards', methods: ['GET', 'POST'])]
    public function downloadMemberCards(Request $request, MemberService $memberService): Response
    {
        $from = $request->get("from_matricule");
        $to = $request->get("to_matricule");
        ini_set('max_execution_time', '-1');

        if(!empty($from) && !empty($to)){
            $from = (int)substr($from, -5);
            $to = (int) substr($to, -5);
            $ranges = range($from, $to);
            foreach($ranges as $matricule){
                $matricules[] = "SY12023" .   sprintf('%05d', $matricule);
            }
            $memberDtos = $memberService->generateMultipleMemberCards($matricules);
        }else{
            $memberDtos = $memberService->generateMultipleMemberCards();
        }

        $zipFile = $memberService->archiveMemberCards($memberDtos);
        return $this->file($zipFile);
    }

    #[Route('/download/sample', name: 'admin_member_sample_file', methods: ['GET'])]
    public function downloadSample(Request $request, MemberService $memberService): Response
    {
        $sampleRealPath = $memberService->generateSampleCsvFile();
        return $this->file($sampleRealPath, 'sample.csv');
    }


    #[Route('/new-subscription/datatable', name: 'admin_member_new_subscription_datatable', methods: ['GET'])]
    public function pendingDT(Request $request, Connection $connection, MemberRepository $memberRepository)
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
            $whereResult .= " matricule LIKE '%". $params['matricule'] . "%' AND";
        }
        if(!empty($params['driving_license_number'])) {
            $whereResult .= " driving_license_number LIKE '%". $params['driving_license_number']. "%' AND";
        }
        if(!empty($params['last_name'])) {
            $whereResult .= " last_name LIKE '%". $params['last_name']. "%' AND";
        }
        if(!empty($params['id_number'])) {
            $whereResult .= " id_number	LIKE '%". $params['id_number	'] . "%' AND ";
        }

        $whereResult.= " status='PENDING'";
        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
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
                                         <a href='/admin/member/$id/edit' class='btn btn-sm btn-soft-success'><i class='mdi mdi-pencil-outline'></i></a>
                                      </li>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='Supprimer'>
                                         <a href='/admin/member/$id/supprimer' class='btn btn-sm btn-soft-danger'><i class='mdi mdi-delete-alert-outline'></i></a>
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
            $whereResult .= " matricule LIKE '%". $params['matricule'] . "%' AND";
        }
        if(!empty($params['driving_license_number'])) {
            $whereResult .= " driving_license_number LIKE '%". $params['driving_license_number']. "%' AND";
        }
        if(!empty($params['last_name'])) {
            $whereResult .= " last_name LIKE '%". $params['last_name']. "%' AND";
        }
        if(!empty($params['id_number'])) {
            $whereResult .= " id_number	LIKE '%". $params['id_number	'] . "%' AND";
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
    public function edit(Request $request, Member $member, MemberService $memberService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $memberRequestDto = MemberMapper::MapToMemberRequestDto($member);
        $form = $this->createForm(MemberRegistrationEditType::class, $memberRequestDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('photo')->getData())            $memberRequestDto->setPhoto($form->get('photo')->getData());
            if($form->get('photoPieceFront')->getData())  $memberRequestDto->setPhotoPieceFront($form->get('photoPieceFront')->getData());
            if($form->get('photoPieceBack')->getData())   $memberRequestDto->setPhotoPieceBack($form->get('photoPieceBack')->getData());
            if($form->get('photoPermisFront')->getData()) $memberRequestDto->setPhotoPermisFront($form->get('photoPermisFront')->getData());
            if($form->get('photoPermisBack')->getData())  $memberRequestDto->setPhotoPermisBack($form->get('photoPermisBack')->getData());

            $member->setStatus($memberRequestDto->getStatus());
            $member->setCompany($memberRequestDto->getCompany());
            $member->setTitre($memberRequestDto->getTitre());
            $member->setEmail($memberRequestDto->getEmail());
            $member->setCommune($memberRequestDto->getCommune());
            $member->setDateOfBirth($memberRequestDto->getDateOfBirth());
            $member->setDrivingLicenseNumber($memberRequestDto->getDrivingLicenseNumber());
            $member->setBirthCity($memberRequestDto->getBirthCity());
            $member->setIdType($memberRequestDto->getIdType());
            $member->setCountry($memberRequestDto->getCountry());
            $member->setCity($memberRequestDto->getCity());
            $member->setMobile($memberRequestDto->getMobile());
            $member->setPartnerLastName($memberRequestDto->getPartnerLastName());
            $member->setPhone($memberRequestDto->getPhone());
            $member->setFirstName($memberRequestDto->getFirstName());
            $member->setLastName($memberRequestDto->getLastName());
            $member->setQuartier($memberRequestDto->getQuartier());
            $member->setWhatsapp($memberRequestDto->getWhatsapp());
            $member->setNationality($memberRequestDto->getNationality());

            $data = $request->request->all();
            if(is_array($data) && isset($data['child_lastname'])) {
                $count = count($data['child_lastname']);
                for($i = 0; $i < $count ; $i++){
                    $child =  new Child();
                    $child->setLastName(strtoupper($data['child_lastname'][$i]));
                    $child->setFirstName(strtoupper($data['child_firstname'][$i]));
                    $child->setSex(strtoupper($data['child_sex'][$i]));
                    $child->setParent($member);
                    $member->addChild($child);
                }
            }
            $result = $memberService->saveMemberImages($memberRequestDto);
            if($result->getPhoto()) $member->setPhoto($result->getPhoto()->getFilename());
            if($result->getPhotoPermisFront()) $member->setPhotoPermisFront($result->getPhotoPermisFront()->getFilename());
            if($result->getPhotoPermisBack()) $member->setPhotoPermisBack($result->getPhotoPermisBack()->getFilename());
            if($result->getPhotoPieceFront()) $member->setPhotoPieceFront($result->getPhotoPieceFront()->getFilename());
            if($result->getPhotoPieceBack()) $member->setPhotoPieceBack($result->getPhotoPieceBack()->getFilename());

            $memberService->storeMember($member);

            return $this->redirectToRoute('admin_member_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/member/edit.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_member_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Member $member, MemberRepository $memberRepository): Response
    {
        if ( true /* $this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token')) */ ) {
            $memberRepository->remove($member, true);
            $fileName = "/var/www/html/public/members/" . $member->getMatricule() . "/";
            if(file_exists($fileName)) {
                $fs =  new Filesystem();
                $fs->remove($fileName);
            }
        }
        return $this->redirectToRoute('admin_member_index', [], Response::HTTP_SEE_OTHER);
    }



}
