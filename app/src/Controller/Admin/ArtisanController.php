<?php

namespace App\Controller\Admin;

use App\Entity\Artisan;
use App\Form\ArtisanType;
use App\Helper\DataTableHelper;
use App\Repository\ArtisanRepository;
use App\Repository\MemberRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/artisan')]
class ArtisanController extends AbstractController
{

    #[Route('', name: 'admin_artisan_index', methods: ['GET'])]
    public function index(ArtisanRepository $artisanRepository): Response
    {
        return $this->render('admin/artisan/index.html.twig', [
            'artisans' => $artisanRepository->findAll(),
        ]);
    }

    #[Route('/datatable', name: 'admin_artisan_datatable', methods: ['GET'])]
    public function datatable(Request $request, Connection $connection, ArtisanRepository $artisanRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'artisan';
        $primaryKey = 'id';
        $artisan = null;
        $columns = [
            [
                'db' => 'id',
                'dt' => 'id',
                'formatter' => function( $d, $row ) use ($artisanRepository){
                    $member = $artisanRepository->find($d);
                    $imageUrl = $member->getReference() . "/" .  $member->getPhoto();
                    $content = "<img src='/artisan/" . $imageUrl . "' alt='' class='avatar-md rounded-circle img-thumbnail'>";
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
                                          <a href='/admin/artisan/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
                                      </li>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='Edit'>
                                         <a href='/admin/artisan/$id/edit' class='btn btn-sm btn-soft-success'><i class='mdi mdi-pencil-outline'></i></a>
                                      </li>
                                      <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='Supprimer'>
                                         <a href='/admin/artisan/$id/supprimer' class='btn btn-sm btn-soft-danger'><i class='mdi mdi-delete-alert-outline'></i></a>
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


    #[Route('/{id}', name: 'admin_artisan_show', methods: ['GET'])]
    public function show(Artisan $artisan): Response
    {
        return $this->render('admin/artisan/show.html.twig', [
            'artisan' => $artisan,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_artisan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Artisan $artisan, ArtisanRepository $artisanRepository): Response
    {
        $form = $this->createForm(ArtisanType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artisanRepository->add($artisan, true);

            return $this->redirectToRoute('app_artisan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/artisan/edit.html.twig', [
            'artisan' => $artisan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_artisan_delete', methods: ['POST'])]
    public function delete(Request $request, Artisan $artisan, ArtisanRepository $artisanRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$artisan->getId(), $request->request->get('_token'))) {
            $artisanRepository->remove($artisan, true);
        }

        return $this->redirectToRoute('app_artisan_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/new', name: 'admin_artisan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArtisanRepository $artisanRepository): Response
    {
        $artisan = new Artisan();
        $form = $this->createForm(ArtisanType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artisanRepository->add($artisan, true);

            return $this->redirectToRoute('app_artisan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/artisan/new.html.twig', [
            'artisan' => $artisan,
            'form' => $form,
        ]);
    }


}
