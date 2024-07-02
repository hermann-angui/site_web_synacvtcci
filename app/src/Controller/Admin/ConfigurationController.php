<?php

namespace App\Controller\Admin;

use App\Entity\Configuration;
use App\Form\ConfigurationType;
use App\Helper\DataTableHelper;
use App\Repository\ConfigurationRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/configuration')]
class ConfigurationController extends AbstractController
{
    #[Route('/', name: 'admin_configuration_index', methods: ['GET'])]
    public function index(ConfigurationRepository $configurationRepository): Response
    {
        return $this->render('admin/configuration/index.html.twig', [
            'configurations' => $configurationRepository->findAll(),
        ]);
    }

    #[Route('/dt', name: 'admin_configuration_dt', methods: ['GET'])]
    public function configurationDT(Request $request, Connection $connection, ConfigurationRepository $configurationRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'configuration';
        $primaryKey = 'id';
        $artisan = null;
        $columns = [
            [
                'db' => 'id',
                'dt' => 'id',
            ],
            [
                'db' => 'name',
                'dt' => 'name',
            ],
            [
                'db' => 'value',
                'dt' => 'value',
            ],
            [
                'db' => 'type',
                'dt' => 'type',
            ],
            [
                'db' => 'description',
                'dt' => 'description',
            ],
            [
                'db'        => 'id',
                'dt'        => '',
                'formatter' => function($d, $row) {
                    $id = $row['id'];
                    $content =  "<div class='d-flex justify-content-center'>
                                    <a href='/configuration/$id/edit' class='btn btn-success btn-sm mx-1'><i class='fa fa-pen'></i></a>
                                    <a href='/configuration/$id/delete' class='btn btn-danger btn-sm'><i class='fa fa-trash'></i></a>
                                 </div>";
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
        $response = DataTableHelper::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);

        return new JsonResponse($response);
    }

    #[Route('/new', name: 'admin_configuration_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ConfigurationRepository $configurationRepository): Response
    {
        $configuration = new Configuration();
        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $configurationRepository->add($configuration, true);

            return $this->redirectToRoute('admin_configuration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/configuration/new.html.twig', [
            'configuration' => $configuration,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_configuration_show', methods: ['GET'])]
    public function show(Configuration $configuration): Response
    {
        return $this->render('admin/configuration/show.html.twig', [
            'configuration' => $configuration,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_configuration_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Configuration $configuration, ConfigurationRepository $configurationRepository): Response
    {
        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $configurationRepository->add($configuration, true);

            return $this->redirectToRoute('admin_configuration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/configuration/edit.html.twig', [
            'configuration' => $configuration,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_configuration_delete', methods: ['POST'])]
    public function delete(Request $request, Configuration $configuration, ConfigurationRepository $configurationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$configuration->getId(), $request->request->get('_token'))) {
            $configurationRepository->remove($configuration, true);
        }

        return $this->redirectToRoute('admin_configuration_index', [], Response::HTTP_SEE_OTHER);
    }
}
