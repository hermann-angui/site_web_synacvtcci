<?php

namespace App\Controller\Admin;

use App\Entity\Child;
use App\Entity\Artisan;
use App\Entity\Villes;
use App\Form\ArtisanPhotoStepType;
use App\Form\ArtisanRegistrationType;
use App\Helper\ActivityLogger;
use App\Helper\DataTableHelper;
use App\Helper\FileUploadHelper;
use App\Repository\ArtisanRepository;
use App\Repository\VillesRepository;
use App\Service\Artisan\ArtisanService;
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

#[Route('/artisan')]
class ArtisanController extends AbstractController
{
    #[Route('', name: 'admin_artisan_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if(in_array("ROLE_AGENT", $this->getUser()->getRoles()))  {
            return $this->redirectToRoute('admin_index');
        } else {
            return $this->render('admin/artisan/index.html.twig');
        }
    }

    #[Route(path: '/search', name: 'admin_artisan_search')]
    public function chooseMain(Request $request): Response
    {
        return $this->render('admin/pages/search-index.html.twig');
    }

    #[Route(path: '/verificationlist', name: 'admin_artisan_verification_list')]
    public function verificationList(Request $request, ArtisanRepository $artisanRepository): Response
    {
        $artisans = $artisanRepository->findAll();
        return $this->render('admin/artisan/verification-list.html.twig', ["artisans" => $artisans]);
    }

    #[Route('/printdocs/{id}', name: 'admin_show_and_download_pdf', methods: ['GET'])]
    public function generateAllPdf(Artisan $artisan, ArtisanService $artisanService): Response
    {
        $outputFile = $artisanService->combinePdfsForPrint($artisan);
        return $this->file($outputFile, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/photostep', name: 'admin_artisan_photostep', methods: ['GET', 'POST'])]
    public function photoStep(Request $request, ArtisanService $artisanService, ActivityLogger $activityLogger): Response
    {
        $artisan = new Artisan;
        $form = $this->createForm(ArtisanPhotoStepType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $artisan->setEtape(1);
            $this->handleFormCreation($request, $form, $artisan, $artisanService);
            $activityLogger->create($artisan, "Création d'un nouveau dossier souscripteur et upload des fichiers (photo, scan des documents d'identités et reçu orange money)");
            return $this->redirectToRoute('admin_artisan_recapitulatif', ['id' => $artisan->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('admin/artisan/etape-photo.html.twig', [
            'artisan' => $artisan,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'admin_artisan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArtisanService $artisanService): Response
    {
        $artisan = new Artisan;
        $form = $this->createForm(ArtisanRegistrationType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->handleFormCreation($request, $form, $artisan, $artisanService);
            $artisanService->saveArtisan($artisan);
            return $this->redirectToRoute('admin_payment_choose', ['id' => $artisan->getId()], Response::HTTP_SEE_OTHER);

        }
        return $this->renderForm('admin/artisan/new.html.twig', [
            'artisan' => $artisan,
            'form' => $form,
        ]);
    }

    #[Route('/upload', name: 'admin_artisan_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request, FileUploadHelper $fileUploadHelper): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        set_time_limit(0);
        /* @var UploadedFile $file */
        if(!empty($file = $request->files->get('file'))) {
            $mime = $file->getMimeType();
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
            if(in_array($mime, ['image/png','image/jpeg','image/jpg','image/gif','text/csv','text/plain'])){
                $fileUploadHelper->upload($file, $uploadDir);
            }
        }
        return $this->renderForm('admin/artisan/upload.html.twig');
    }

    #[Route('/import', name: 'admin_artisan_import', methods: ['GET', 'POST'])]
    public function import(Request $request, ArtisanService $artisanService): Response
    {
        $artisanService->createArtisanFromFile();
        return $this->redirectToRoute('admin_artisan_index');
    }

    #[Route('/generate/new/card/{id}', name: 'admin_artisan_generate_card', methods: ['GET'])]
    public function generateCard(Artisan $artisan, ArtisanService $artisanService): Response
    {
        $artisan = $artisanService->generateSingleCnmciCard($artisan);
        return $this->redirectToRoute('admin_artisan_show_card', ['id' => $artisan->getId()]);
    }

    #[Route('/show/card/{id}', name: 'admin_artisan_show_card', methods: ['GET'])]
    public function showCard(Request $request, Artisan $artisan): Response
    {
        return $this->render('admin/artisan/show_card.html.twig', ['artisan' => $artisan]);
    }

    #[Route('/download/card/{id}', name: 'admin_artisan_download_card', methods: ['GET'])]
    public function downloadCard(Request $request, Artisan $artisan, ArtisanService $artisanService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $artisanService->generateSingleCnmciCard($artisan);
        $zipFile = $artisanService->archiveArtisanCards([$artisan]);
        return $this->file($zipFile, 'fichier_carte.zip');
    }

    #[Route('/download/cards', name: 'admin_artisan_download_cards', methods: ['GET', 'POST'])]
    public function downloadArtisanCards(Request $request, ArtisanService $artisanService): Response
    {
        ini_set('max_execution_time', '-1');
        $artisans = $artisanService->generateMultipleArtisanCards();
        $zipFile = $artisanService->archiveArtisanCards($artisans);
        return $this->file($zipFile);
    }

    #[Route('/download/sample', name: 'admin_artisan_sample_file', methods: ['GET'])]
    public function downloadSample(Request $request, ArtisanService $artisanService): Response
    {
        $sampleRealPath = $artisanService->generateSampleCsvFile();
        return $this->file($sampleRealPath, 'sample.csv');
    }

    #[Route('/adherents/synacvtcci', name: 'admin_adherents_synacvtcci', methods: ['GET'])]
    public function  showListAdherentsSynacvtcci(Request $request): Response
    {
        return $this->render('admin/artisan/adherents-list.html.twig');
    }

    #[Route('/adherents/taxi', name: 'admin_adherents_taxi', methods: ['GET'])]
    public function  showListAdherentsTaxi(Request $request): Response
    {
        return $this->render('admin/artisan/adherents-list.html.twig');
    }

    #[Route('/adherents/livreurs', name: 'admin_adherents_livreurs', methods: ['GET'])]
    public function showListAdherentsLivreurs(Request $request): Response
    {
        return $this->render('admin/artisan/adherents-list.html.twig');
    }

    #[Route('/adherents/dt', name: 'admin_adherents_list_dt', methods: ['GET'])]
    public function ListAdherentsDT(Request $request, Connection $connection, ArtisanRepository $artisanRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'artisan';
        $primaryKey = 'id';
        $columns = [
            [
                'db' => 'photo',
                'dt' => 'photo',
                'formatter' => function( $d, $row ){
                    $imageUrl = $row['reference'] . "/" . $d;
                    $content = "<div class='avatar-md img-fluid rounded-circle'><img src='/artisans/$imageUrl' alt='' class='img-fluid d-block rounded-circle'></div>";
                    return $content;
                }
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
                'dt' => 'subscription_date',
                'formatter' => function ($d, $row) {
                    return date_format(new \DateTime($d), 'd/m/Y');
                }
            ],
            [
                'db'        => 'id',
                'dt'        => '',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<div class='d-flex gap-2 flex-wrap'>
                                    <div class='btn-group'>
                                        <button class='btn btn-info dropdown-toggle btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                            <small></small><i class='mdi mdi-menu'></i>
                                        </button>
                                        <div class='dropdown-menu' style=''>
                                            <a class='dropdown-item' href='/artisan/$id'><i class='mdi mdi-eye'></i> Fiche Artisan</a>
                                            <a class='dropdown-item' href='/artisan/cnmci/$id'><i class='mdi mdi-eye'></i> Fiche CNMCI</a>
                                         </div>
                                    </div>
                                 </div>";
                    return $content;
                }
            ],
            [
                'db' => 'reference',
                'dt' => 'reference'
            ],

        ];

        $sql_details = array(
            'user' => $paramDB['user'],
            'pass' => $paramDB['password'],
            'db'   => $paramDB['dbname'],
            'host' => $paramDB['host']
        );

        $whereResult = null;
        if(!empty($params['activity'])){
            $whereResult = " activity ='". $params['activity'] . "' AND ";
        }
        $response = DataTableHelper::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/pending-subscription/datatable', name: 'admin_artisan_pending_souscripteur_datatable', methods: ['GET'])]
    public function pendingDT(Request $request, Connection $connection, ArtisanRepository $artisanRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'artisan';
        $primaryKey = 'id';
        $columns = [
            [
                'db' => 'id',
                'dt' => 'id',
                'formatter' => function( $d, $row ) use ($artisanRepository){
                    $imageUrl = $row['reference'] . "/" . $row['photo'];
                    $content = "<div class='avatar-md img-fluid rounded-circle'><img src='/artisans/$imageUrl' alt='' class='img-fluid d-block rounded-circle'></div>";
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
                'db'        => 'id',
                'dt'        => '',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<a class='btn btn-primary btn-sm btn-rounded waves-effect waves-light' href='/artisan/$id/edit'><i class='mdi mdi-pen'></i> Traiter le dossier</a>";
                    return $content;
                }
            ],
            [
                'db' => 'reference',
                'dt' => 'reference'
            ],
            [
                'db' => 'photo',
                'dt' => 'photo'
            ],
        ];

        $sql_details = array(
            'user' => $paramDB['user'],
            'pass' => $paramDB['password'],
            'db'   => $paramDB['dbname'],
            'host' => $paramDB['host']
        );
        $whereResult= " etape = 1 ";
        $response = DataTableHelper::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/datatable', name: 'admin_artisan_datatable', methods: ['GET'])]
    public function ListArtisanDT(Request $request, Connection $connection)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'artisan';
        $primaryKey = 'id';
        $columns = [
            [
                'db' => 'photo',
                'dt' => 'photo',
                'formatter' => function( $d, $row) {
                    $imageUrl = $row['reference'] . "/" . $d;
                    $content = "<div class='avatar-md img-fluid rounded-circle'><img src='/artisans/$imageUrl' alt='' class='img-fluid d-block rounded-circle'></div>";
                    return $content;
                }
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
                'db'        => 'id',
                'dt'        => '',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<div class='d-flex gap-2 flex-wrap'>
                                    <div class='btn-group'>
                                        <button class='btn btn-info dropdown-toggle btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                            <small></small><i class='mdi mdi-menu'></i>
                                        </button>
                                        <div class='dropdown-menu' style=''>
                                            <a class='dropdown-item' href='/artisan/$id'><i class='mdi mdi-eye'></i> Fiche Artisan</a>
                                            <a class='dropdown-item' href='/artisan/cnmci/$id'><i class='mdi mdi-eye'></i> Fiche CNMCI</a>
                                            <a class='dropdown-item' href='/artisan/$id/edit'><i class='mdi mdi-pen'></i> Editer</a>";
                    $content.= "</div></div></div> ";
                    return $content;
                }
            ],
            [
                'db' => 'reference',
                'dt' => 'reference'
            ],
        ];

        $sql_details = array(
            'user' => $paramDB['user'],
            'pass' => $paramDB['password'],
            'db'   => $paramDB['dbname'],
            'host' => $paramDB['host']
        );

        $whereResult = '';
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
    
    #[Route('/{id}', name: 'admin_artisan_show', methods: ['GET'])]
    public function show(Artisan $artisan): Response
    {
        return $this->render('admin/artisan/show.html.twig', ['artisan' => $artisan,]);
    }

    #[Route('/recap/{id}', name: 'admin_artisan_recapitulatif', methods: ['GET'])]
    public function recapitulatif(Artisan $artisan): Response
    {
        return $this->render('admin/artisan/recapitulatif.html.twig', ['artisan' => $artisan]);
    }

    #[Route('/{id}/edit', name: 'admin_artisan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Artisan $artisan, ArtisanService $artisanService, VillesRepository $villesRepository, ActivityLogger $activityLogger): Response
    {
        date_default_timezone_set("Africa/Abidjan");

        $form = $this->createForm(ArtisanRegistrationType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = [];
            if($form->has('photo'))  $images['photo'] = $form->get('photo')?->getData();
            if($form->has('photoPieceFront'))  $images['photoPieceFront'] = $form->get('photoPieceFront')?->getData();
            if($form->has('photoPieceBack'))  $images['photoPieceBack'] = $form->get('photoPieceBack')?->getData();
            if($form->has('photoPermisFront'))  $images['photoPermisFront'] = $form->get('photoPermisFront')?->getData();
            if($form->has('photoPermisBack'))  $images['photoPermisBack'] = $form->get('photoPermisBack')?->getData();

            if($form->has('paymentReceiptCnmciPdf'))  $images['paymentReceiptCnmciPdf'] = $form->get('paymentReceiptCnmciPdf')?->getData();

            $birth_city_other =  $form->get("birth_city_other")->getData();
            if($birth_city_other) {
                $artisan->setBirthCity(strtoupper($birth_city_other));
                $exist = $villesRepository->findOneBy(['name' => strtoupper($birth_city_other)]);
                if(!$exist) {
                    $ville = new Villes();
                    $ville->setName(strtoupper($birth_city_other));
                    $villesRepository->add($ville, true);
                }
            }

            if($artisan->getEtape() === 1) $artisan->setEtape(2);
            $artisanService->updateArtisan($artisan, $images);
            $activityLogger->update($artisan, "Mise à jour des données du souscripteur");

            return $this->redirectToRoute('admin_artisan_recapitulatif', ['id' => $artisan->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/artisan/edit.html.twig', [
            'artisan' => $artisan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_artisan_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Artisan $artisan, ArtisanRepository $artisanRepository): Response
    {
        if ( false /* $this->isCsrfTokenValid('delete'.$artisan->getId(), $request->request->get('_token')) */ ) {
            $artisanRepository->remove($artisan, true);
            $fileName = "/var/www/html/public/artisans/" . $artisan->getReference() . "/";
            if(file_exists($fileName)) {
                $fs =  new Filesystem();
                $fs->remove($fileName);
            }
        }
        return $this->redirectToRoute('admin_artisan_index', [], Response::HTTP_SEE_OTHER);
    }

    private function handleFormCreation(Request $request, FormInterface $form, Artisan &$artisan, ArtisanService $artisanService): Artisan {

        $images = [];

        if($form->has('photo'))  $images['photo'] = $form->get('photo')?->getData();
        if($form->has('photoPieceFront'))  $images['photoPieceFront'] = $form->get('photoPieceFront')?->getData();
        if($form->has('photoPieceBack'))  $images['photoPieceBack'] = $form->get('photoPieceBack')?->getData();
        if($form->has('photoPermisFront'))  $images['photoPermisFront'] = $form->get('photoPermisFront')?->getData();
        if($form->has('photoPermisBack'))  $images['photoPermisBack'] = $form->get('photoPermisBack')?->getData();

        if($form->has('paymentReceiptCnmciPdf'))  $images['paymentReceiptCnmciPdf'] = $form->get('paymentReceiptCnmciPdf')?->getData();
        if($form->has('paymentReceiptSyndicatPdf'))  $images['paymentReceiptSyndicatPdf'] = $form->get('paymentReceiptSyndicatPdf')?->getData();
        if($form->has('scanDocumentIdentitePdf'))  $images['scanDocumentIdentitePdf'] = $form->get('scanDocumentIdentitePdf')?->getData();
        if($form->has('mergedDocumentsPdf'))  $images['mergedDocumentsPdf'] = $form->get('mergedDocumentsPdf')?->getData();

        $data = $request->request->all();
        if(!empty($data) && isset($data['child'])){
            foreach($data['child'] as $childItem){
                $child=  new Child();
                $child->setLastName($childItem['lastname']);
                $child->setFirstName($childItem['firstname']);
                $child->setSex($childItem['sex']);
                $child->setArtisan($artisan);
                $artisan->addChild($child);
            }
        }
        $artisanService->createArtisan($artisan, $images);
        return $artisan;
    }

}
