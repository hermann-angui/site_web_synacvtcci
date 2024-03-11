<?php

namespace App\Controller\Admin;

use App\Entity\Child;
use App\Entity\Member;
use App\Entity\Villes;
use App\Form\MemberPhotoStepType;
use App\Form\MemberRegistrationType;
use App\Helper\ActivityLogger;
use App\Helper\DataTableHelper;
use App\Helper\FileUploadHelper;
use App\Repository\ChildRepository;
use App\Repository\MemberRepository;
use App\Repository\VillesRepository;
use App\Service\Member\MemberService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/member')]
class MemberController extends AbstractController
{
    #[Route('', name: 'admin_member_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if(in_array("ROLE_AGENT", $this->getUser()->getRoles() ))  {
            return $this->redirectToRoute('admin_index_agent');
        } else {
            return $this->render('admin/member/synacvtcci/index.html.twig');
        }
    }

    #[Route(path: '/search', name: 'admin_member_search')]
    public function chooseMain(Request $request): Response
    {
        return $this->render('admin/pages/search-index.html.twig');
    }

    #[Route(path: '/verificationlist', name: 'admin_member_verification_list')]
    public function verificationList(Request $request, MemberRepository $memberRepository): Response
    {
        $members = $memberRepository->findBy(['status' => ['PAID','COMPLETED']]);
        return $this->render('admin/member/verification-list.html.twig', ["members" => $members]);
    }

    #[Route('/cnmci/{id}', name: 'admin_member_cncmi_show', methods: ['GET'])]
    public function formCnmciShow(Request $request, Member $member, MemberService $memberService): Response
    {
        if(!in_array($member->getStatus() , ["PAID", "COMPLETED"])){
            return $this->redirectToRoute('admin_member_show', ['id' => $member->getId()]);
        }
        return $this->render('admin/member/cnmci/cnmci_show.html.twig', ['member' => $member]);
    }

    #[Route('/cnmci/{id}/edit', name: 'admin_member_cncmi_edit', methods: ['GET','POST'])]
    public function cnmciEdit(Member $member,
                              Request  $request,
                              MemberService $memberService,
                              ActivityLogger $activityLogger): Response
    {
        if($request->getMethod() === "GET"){
            return $this->render('admin/member/cnmci/cnmci_edit.html.twig', ['member' => $member]);
        }elseif($request->getMethod() === "POST") {
            $memberService->createCnmiOrUpdate($member, $request->request->all(), 1);
            $memberService->generateCNMCIPdf($member, "admin/pdf/cnmci.html.twig");
            $activityLogger->update($member, "Mise à jour des données du formulaire de la chambre nationale de métiers");
            return $this->redirectToRoute('admin_member_cncmi_show', ['id' => $member->getId()]);
        }
        return $this->render('admin/member/cnmci/cnmci_show.html.twig', ['member' => $member]);
    }


    #[Route('/printdocs/{id}', name: 'admin_show_and_download_pdf', methods: ['GET'])]
    public function generateAllPdf(Member $member, MemberService $memberService): Response
    {
        $outputFile = $memberService->combinePdfsForPrint($member);
        return $this->file($outputFile, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/cnmci-pdf/{id}', name: 'admin_download_cnmci_pdf', methods: ['GET'])]
    public function downloadCnmciPdf(Member $member,
                                     MemberService $memberService,
                                    ActivityLogger $activityLogger): Response {
        $activityLogger->create($member, "Téléchargement fiche de la chambre nationale de métier");
        return $memberService->downloadCNMCIPdf($member, "admin/pdf/cnmci.html.twig");
    }

    #[Route('/photostep', name: 'admin_member_photostep', methods: ['GET', 'POST'])]
    public function photoStep(Request $request,
                              MemberService $memberService,
                              ActivityLogger $activityLogger): Response
    {
        $member = new Member;
        $form = $this->createForm(MemberPhotoStepType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->handleFormCreation($request, $form, $member, $memberService);
            $member->setStatus("PHOTO_VALID");
            $memberService->saveMember($member);
            $activityLogger->create($member, "Création d'un nouveau dossier souscripteur et upload des fichiers (photo, scan des documents d'identités et reçu orange money)");
            return $this->redirectToRoute('admin_member_recapitulatif', ['id' => $member->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('admin/member/etape-photo.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'admin_member_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        MemberService $memberService): Response
    {
        $member = new Member;
        $form = $this->createForm(MemberRegistrationType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->handleFormCreation($request, $form, $member, $memberService);
            if($member->getStatus() === "PENDING" || $member->getStatus() === "PHOTO_VALID" || $member->getStatus() === "INFORMATION_VALIDATED"){
                $member->setStatus("INFORMATION_VALIDATED");
                $memberService->saveMember($member);
                return $this->redirectToRoute('admin_payment_choose', ['id' => $member->getId()], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('admin_member_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('admin/member/new.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

        #[Route('/upload', name: 'admin_member_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request,
                           FileUploadHelper $fileUploadHelper): Response
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
        return $this->renderForm('admin/member/synacvtcci/upload.html.twig');
    }

    #[Route('/import', name: 'admin_member_import', methods: ['GET', 'POST'])]
    public function import(Request $request,
                           MemberService $memberService): Response
    {
        $memberService->createMemberFromFile();
        return $this->redirectToRoute('admin_member_index');
    }

    #[Route('/generate/new/card/{id}', name: 'admin_member_generate_card', methods: ['GET'])]
    public function generateCard(Member $member,
                                 MemberService $memberService,
                                 MemberRepository $memberRepository): Response
    {
        $member = $memberService->generateSingleMemberCard($member);
        return $this->render('admin/member/synacvtcci/show_card.html.twig', ['member' => $member]);
    }

    #[Route('/show/card/{id}', name: 'admin_member_show_card', methods: ['GET'])]
    public function showCard(Request $request,
                             Member $member): Response
    {
        return $this->render('admin/member/synacvtcci/show_card.html.twig', ['member' => $member]);
    }

    #[Route('/download/card/{id}', name: 'admin_member_download_card', methods: ['GET'])]
    public function downloadCard(Request $request,
                                 Member $member,
                                 MemberService $memberService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $memberService->generateSingleMemberCard($member);
        $zipFile = $memberService->archiveMemberCards([$member]);
        return $this->file($zipFile, 'fichier_carte.zip');
    }

    #[Route('/download/cards', name: 'admin_member_download_cards', methods: ['GET', 'POST'])]
    public function downloadMemberCards(Request $request,
                                        MemberService $memberService): Response
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
            $members = $memberService->generateMultipleMemberCards($matricules);
        }
        else{
            $members = $memberService->generateMultipleMemberCards();
        }
        $zipFile = $memberService->archiveMemberCards($members);
        return $this->file($zipFile);
    }

    #[Route('/download/sample', name: 'admin_member_sample_file', methods: ['GET'])]
    public function downloadSample(Request $request,
                                   MemberService $memberService): Response
    {
        $sampleRealPath = $memberService->generateSampleCsvFile();
        return $this->file($sampleRealPath, 'sample.csv');
    }

    #[Route('/cards/list', name: 'admin_cards_list', methods: ['GET'])]
    public function showCardsList(Request $request): Response
    {
        return $this->render('admin/member/synacvtcci/cards-list.html.twig');
    }

    #[Route('/cardslist/dt', name: 'admin_cards_list_dt', methods: ['GET'])]
    public function cardsListDT(Request $request,
                                Connection $connection,
                                MemberRepository $memberRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'member';
        $primaryKey = 'id';
        $columns = [
            [
                'db' => 'id',
                'dt' => 'id',
                'formatter' => function( $d, $row ) use ($memberRepository){
                    $member = $memberRepository->find($d);
                    $imageUrl = $member->getMatricule() . "/" .  $member->getReference() . "_card.png";
                    $content = "<img src='/members/" . $imageUrl . "' alt='' class='avatar-md rounded-2 img-thumbnail'>";
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
        if(!empty($params['last_name'])) {
            $whereResult .= " last_name LIKE '%". $params['last_name']. "%' AND";
        }
        $whereResult = substr_replace($whereResult,'',-strlen(' AND'));
        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/new-subscription/datatable', name: 'admin_member_new_subscription_datatable', methods: ['GET'])]
    public function pendingDT(Request $request,
                              Connection $connection,
                              MemberRepository $memberRepository)
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
                    $imageUrl = $member->getReference() . "/" .  $member->getPhoto();
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
                'db'        => 'email',
                'dt'        => 'email',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<div class='d-flex gap-2 flex-wrap'>
                                    <div class='btn-group'>
                                        <button class='btn btn-info dropdown-toggle btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                            <small></small><i class='mdi mdi-menu'></i>
                                        </button>
                                        <div class='dropdown-menu' style=''>
                                            <a class='dropdown-item' href='/admin/member/$id'><i class='mdi mdi-eye'></i> Fiche SYNACVTCCI</a>
                                            <a class='dropdown-item' href='/admin/member/cnmci/$id'><i class='mdi mdi-eye'></i> Fiche CNMCI</a>
                                            <a class='dropdown-item' href='/admin/member/$id/edit'><i class='mdi mdi-pen'></i> Editer</a>
                                        </div>
                                    </div>
                                </div> ";
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
        $whereResult= " status IN ('PENDING', 'PHOTO_VALID')";
        $response = DataTableHelper::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/datatable', name: 'admin_member_datatable', methods: ['GET'])]
    public function datatable(Request $request,
                              Connection $connection,
                              MemberRepository $memberRepository)
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
                    $imageUrl = $member->getReference() . "/" . basename($member->getPhoto());
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
                'db'        => 'email',
                'dt'        => 'email',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<div class='d-flex gap-2 flex-wrap'>
                                    <div class='btn-group'>
                                        <button class='btn btn-info dropdown-toggle btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                            <small></small><i class='mdi mdi-menu'></i>
                                        </button>
                                        <div class='dropdown-menu' style=''>
                                            <a class='dropdown-item' href='/admin/member/$id'><i class='mdi mdi-eye'></i> Fiche SYNACVTCCI</a>
                                            <a class='dropdown-item' href='/admin/member/cnmci/$id'><i class='mdi mdi-eye'></i> Fiche CNMCI</a>
                                            <a class='dropdown-item' href='/admin/member/$id/edit'><i class='mdi mdi-pen'></i> Editer</a>
                                            <a class='dropdown-item' href='/admin/payment/carte/synacvtcci/$id'><i class='mdi mdi-cash'></i> Payer la carte SYNACVTCI</a>
                                        </div>
                                    </div>
                                </div> ";
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
            $whereResult .= " id_number	LIKE '%". $params['id_number'] . "%' AND";
        }

      //  $whereResult.= " status='VALIDATED'";
      $whereResult = substr_replace($whereResult,'',-strlen(' AND'));
        $response = DataTableHelper::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/pending/souscripteur', name: 'admin_member_pending_souscripteur_datatable', methods: ['GET'])]
    public function pendingSouscripteur(Request $request,
                                        Connection $connection,
                                        MemberRepository $memberRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'member';
        $primaryKey = 'id';
        $columns = [
            [
                'db' => 'id',
                'dt' => 'id',
                'formatter' => function( $d, $row ) use ($memberRepository){
                    $member = $memberRepository->find($d);
                    $imageUrl = $member->getReference() . "/" .  $member->getPhoto();
                    $content = "<img src='/members/" . $imageUrl . "' alt='' class='avatar-md rounded-circle img-thumbnail'>";
                    return $content;
                }
            ],
            [
                'db' => 'tracking_code',
                'dt' => 'tracking_code',
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
                'db' => 'status',
                'dt' => 'status'
            ],
            [
                'db'        => 'matricule',
                'dt'        => 'matricule',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<div class='d-flex gap-2 flex-wrap'>
                                   <a class='btn btn-primary btn-sm btn-rounded' href='/admin/member/$id/edit'><i class='mdi mdi-eye'></i>Traiter</a>                                    
                                </div> ";
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

        $whereResult = null;

        if(!empty($params['searchTerm'])) {
            $whereResult .= " tracking_code LIKE '%". $params['searchTerm']. "%' AND ";
        }

        $whereResult.= " status IN ('PHOTO_VALID') ";
        $response = DataTableHelper::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult, null);

        return new JsonResponse($response);
    }

    #[Route('/{id}', name: 'admin_member_show', methods: ['GET'])]
    public function show(Member $member): Response
    {
        return $this->render('admin/member/show.html.twig', ['member' => $member,]);
    }

    #[Route('/recap/{id}', name: 'admin_member_recapitulatif', methods: ['GET'])]
    public function recapitulatif(Member $member): Response
    {
        return $this->render('admin/member/recapitulatif.html.twig', ['member' => $member]);
    }

    #[Route('/{id}/edit', name: 'admin_member_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,
                         Member $member,
                         MemberService $memberService,
                         VillesRepository $villesRepository,
                         ChildRepository $childRepository,
                        ActivityLogger $activityLogger): Response
    {
        date_default_timezone_set("Africa/Abidjan");

        $form = $this->createForm(MemberRegistrationType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $images = [];
            if($form->has('photo'))  $images['photo'] = $form->get('photo')?->getData();
            if($form->has('photoPieceFront'))  $images['photoPieceFront'] = $form->get('photoPieceFront')?->getData();
            if($form->has('photoPieceBack'))  $images['photoPieceBack'] = $form->get('photoPieceBack')?->getData();
            if($form->has('photoPermisFront'))  $images['photoPermisFront'] = $form->get('photoPermisFront')?->getData();
            if($form->has('photoPermisBack'))  $images['photoPermisBack'] = $form->get('photoPermisBack')?->getData();

            if($form->has('paymentReceiptCnmciPdf'))  $images['paymentReceiptCnmciPdf'] = $form->get('paymentReceiptCnmciPdf')?->getData();
            if($form->has('paymentReceiptSynacvtcciPdf'))  $images['paymentReceiptSynacvtcciPdf'] = $form->get('paymentReceiptSynacvtcciPdf')?->getData();
            if($form->has('scanDocumentIdentitePdf'))  $images['scanDocumentIdentitePdf'] = $form->get('scanDocumentIdentitePdf')?->getData();
            if($form->has('mergedDocumentsPdf'))  $images['mergedDocumentsPdf'] = $form->get('mergedDocumentsPdf')?->getData();

          //  $data = $request->request->all();
/*
            $memberChildren = $member->getChildren();
            if(isset($data['child'])){
                foreach($data['child'] as $childItem){
//                    $child = $childRepository->findOneBy([
//                        'first_name' => ,
//                        'last_name' => ,
//                        'sex' => ,
//                    ]);
                    $found = array_filter($memberChildren->toArray(), function($child) use($childItem){
                        return ($child->getLastName() === $childItem['lastname'] && $child->getFirstName() === $childItem['firstname']) ;
                    });

                    $found = array_values($found);
                    if(!empty($found)) {
                        $found[0]->setLastName($childItem['lastname']);
                        $found[0]->setFirstName($childItem['firstname']);
                        $found[0]->setSex($childItem['sex']);
                    }else{
                        $child =  new Child();
                        $child->setLastName($childItem['lastname']);
                        $child->setFirstName($childItem['firstname']);
                        $child->setSex($childItem['sex']);
                        $child->setMember($member);
                        $member->addChild($child);
                    }
                }
            }
*/
            $birth_city_other =  $form->get("birth_city_other")->getData();
            if($birth_city_other) {
                $member->setBirthCity(strtoupper($birth_city_other));
                $exist = $villesRepository->findOneBy(['name' => strtoupper($birth_city_other)]);
                if(!$exist) {
                    $ville = new Villes();
                    $ville->setName(strtoupper($birth_city_other));
                    $villesRepository->add($ville, true);
                }
            }

            $memberService->updateMember($member, $images);
            $activityLogger->update($member, "Mise à jour des données du souscripteur");

            if($member->getStatus() === "PHOTO_VALID" || $member->getStatus() === "PENDING" || $member->getStatus() === "INFORMATION_VALIDATED"){
                $member->setStatus("INFORMATION_VALIDATED");
                $memberService->saveMember($member);
            }
            return $this->redirectToRoute('admin_member_recapitulatif', ['id' => $member->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/member/edit.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_member_delete', methods: ['GET','POST'])]
    public function delete(Request $request,
                           Member $member,
                           MemberRepository $memberRepository): Response
    {
        if ( false /* $this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token')) */ ) {
            $memberRepository->remove($member, true);
            $fileName = "/var/www/html/public/members/" . $member->getReference() . "/";
            if(file_exists($fileName)) {
                $fs =  new Filesystem();
                $fs->remove($fileName);
            }
        }
        return $this->redirectToRoute('admin_member_index', [], Response::HTTP_SEE_OTHER);
    }

    private function handleFormCreation(Request $request,
                                        FormInterface $form,
                                        Member &$member,
                                        MemberService $memberService): Member {

        $images = [];

        if($form->has('photo'))  $images['photo'] = $form->get('photo')?->getData();
        if($form->has('photoPieceFront'))  $images['photoPieceFront'] = $form->get('photoPieceFront')?->getData();
        if($form->has('photoPieceBack'))  $images['photoPieceBack'] = $form->get('photoPieceBack')?->getData();
        if($form->has('photoPermisFront'))  $images['photoPermisFront'] = $form->get('photoPermisFront')?->getData();
        if($form->has('photoPermisBack'))  $images['photoPermisBack'] = $form->get('photoPermisBack')?->getData();

        if($form->has('paymentReceiptCnmciPdf'))  $images['paymentReceiptCnmciPdf'] = $form->get('paymentReceiptCnmciPdf')?->getData();
        if($form->has('paymentReceiptSynacvtcciPdf'))  $images['paymentReceiptSynacvtcciPdf'] = $form->get('paymentReceiptSynacvtcciPdf')?->getData();
        if($form->has('scanDocumentIdentitePdf'))  $images['scanDocumentIdentitePdf'] = $form->get('scanDocumentIdentitePdf')?->getData();
        if($form->has('mergedDocumentsPdf'))  $images['mergedDocumentsPdf'] = $form->get('mergedDocumentsPdf')?->getData();

        $data = $request->request->all();
        if(isset($data['child'])){
            foreach($data['child'] as $childItem){
                $child=  new Child();
                $child->setLastName($childItem['lastname']);
                $child->setFirstName($childItem['firstname']);
                $child->setSex($childItem['sex']);
                $child->setMember($member);
                $member->addChild($child);
            }
        }
        $memberService->createMember($member, $images);
        return $member;
    }

}
