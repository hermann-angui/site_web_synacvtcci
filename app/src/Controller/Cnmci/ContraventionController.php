<?php
namespace App\Controller\Cnmci;

use App\Entity\Contravention;
use App\Form\ContraventionType;
use App\Helper\DataTableHelper;
use App\Repository\ContraventionRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contravention')]
class ContraventionController extends AbstractController
{
    #[Route('/list', name: 'cnmci_list_contravention', methods: ['GET', 'POST'])]
    public function contraventionList(Request $request, ContraventionRepository $contraventionRepository): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        return $this->renderForm('contravention/list.html.twig');
    }

    #[Route('/new', name: 'cnmci_new_contravention', methods: ['GET', 'POST'])]
    public function contravention(Request $request, ContraventionRepository $contraventionRepository): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $contravention = new Contravention();
        $form = $this->createForm(ContraventionType::class, $contravention);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contraventionRepository->add($contravention, true);
            if($request->isXmlHttpRequest()) return $this->json([$this->generateUrl('cnmci_list_contravention')]);
            return $this->redirectToRoute('cnmci_list_contravention');
        }
        return $this->renderForm('contravention/new.html.twig', [
            'contravention' => $contravention,
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'cnmci_contravention_edit', methods: ['GET', 'POST'])]
    public function contraventionEdit(Request $request, Contravention $contravention, ContraventionRepository $contraventionRepository): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        $form = $this->createForm(ContraventionType::class, $contravention);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contraventionRepository->add($contravention, true);
            if($request->isXmlHttpRequest()) return $this->json([$this->generateUrl('cnmci_list_contravention')]);
            return $this->redirectToRoute('cnmci_list_contravention');
        }
        return $this->renderForm('contravention/new.html.twig', [
            'contravention' => $contravention,
            'form' => $form,
        ]);
    }

    #[Route('/dt', name: 'cnmci_contravention_datatable', methods: ['GET', 'POST'])]
    public function contraventionDT(Request $request, Connection $connection)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'contravention';
        $primaryKey = 'id';
        $columns = [
            [
                'db' => 'contravention_numero',
                'dt' => 'contravention_numero'
            ],
            [
                'db' => 'nom_controleur',
                'dt' => 'nom_controleur',
            ],
            [
                'db' => 'infraction_type',
                'dt' => 'infraction_type',
            ],
            [
                'db' => 'infraction_date',
                'dt' => 'infraction_date',
                'formatter' => function ($d, $row) {
                    return date_format(new \DateTime($d), 'd/m/Y');
                }
            ],
            [
                'db' => 'infraction_lieu',
                'dt' => 'infraction_lieu'
            ],
            [
                'db' => 'is_paid',
                'dt' => 'is_paid',
                'formatter' => function ($d, $row) {
                    if($d) $content = sprintf("<span class='badge rounded-pill badge-soft-success font-size-12'>%s</span>", "PAYE");
                    else $content = sprintf("<span class='badge rounded-pill badge-soft-warning font-size-12'>%s</span>", "EN ATTENTE");
                    return $content;
                }
            ],
            [
                'db' => 'vehicule_immatriculation',
                'dt' => 'vehicule_immatriculation'
            ],
            [
                'db' => 'vehicule_marque',
                'dt' => 'vehicule_marque'
            ],
        ];

        $sql_details = [
            'user' => $paramDB['user'],
            'pass' => $paramDB['password'],
            'db' => $paramDB['dbname'],
            'host' => $paramDB['host']
        ];

        $whereResult = null;
        if(!empty($params['search_numero_contravention']))  $whereResult = " contravention_numero LIKE '" . $params['search_numero_contravention'] . "%'";
        if(isset($params['filtre'])) {
            $whereResult = $whereResult ? " AND " : '';
            if($params['filtre'] === '0' || $params['filtre'] === 'on') $whereResult .= "(is_paid = 0 OR is_paid IS NULL) ";
            else $whereResult .= "is_paid = 1";
        }

        $response = DataTableHelper::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult);
        return new JsonResponse($response);
    }

    #[Route('/{id}', name: 'cnmci_contravention_show', methods: ['GET', 'POST'])]
    public function contraventionShow(Contravention $contravention): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        return $this->render('contravention/show.html.twig', ['contravention' => $contravention]);
    }

}
