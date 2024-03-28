<?php

namespace App\Controller\Admin;


use App\Entity\User;
use App\Form\UserFormType;
use App\Helper\DataTableHelper;
use App\Helper\UserHelper;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use App\Security\FormLoginAuthenticator;
use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'admin_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request                        $request,
                        UserPasswordHasherInterface    $userPasswordHasher,
                        UserAuthenticatorInterface     $userAuthenticator,
                        FormLoginAuthenticator         $authenticator,
                        EntityManagerInterface         $entityManager,
                        UserHelper                     $userHelper): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $roles[] = 'ROLE_USER';

            switch($form->get('role')->getData()){
                case 'ROLE_AGENT':
                    break;
                case 'ROLE_ADMIN':
                    $roles[] = 'ROLE_ADMIN';
                    break;
                case 'ROLE_SUPER_ADMIN':
                    $roles[] = 'ROLE_SUPER_ADMIN';
                    break;
                default:
                    break;
            }
            $user->setRoles($roles);
            $user->setCreatedAt(new \DateTime());
            $user->setModifiedAt(new \DateTime());

            $entityManager->persist($user);
            $entityManager->flush();


            $photo = $form->get('photo')->getData();
            if($photo){
                $fileName = $userHelper->uploadAsset($photo, $user->getId());
                if($fileName) $user->setPhoto($fileName);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }
        return $this->render('admin/user/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/dt', name: 'admin_user_dt', methods: ['GET'])]
    public function datatable(Request $request, Connection $connection, PaymentRepository $paymentRepository)
    {
        date_default_timezone_set("Africa/Abidjan");
        $params = $request->query->all();
        $paramDB = $connection->getParams();
        $table = 'user';
        $primaryKey = 'id';
        $columns = [
            [
                'db' => 'id',
                'dt' => 'DT_RowId',
                'formatter' => function( $d, $row ) {
                    return 'row_'.$d;
                }
            ],
            [
                'db' => 'photo',
                'dt' => 'photo',
            ],
            [
                'db' => 'email',
                'dt' => 'email',
            ],
            [
                'db' => 'roles',
                'dt' => 'roles',
            ],
            [
                'db' => 'prenoms',
                'dt' => 'prenoms',
            ],
            [
                'db' => 'nom',
                'dt' => 'nom',
            ],
            [
                'db'        => 'id',
                'dt'        => '',
                'formatter' => function($d, $row){
                    $id = $row['id'];
                    $content =  "<ul class='list-unstyled hstack gap-1 mb-0'>
                                  <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                    <a href='/admin/user/$id' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-eye-outline'></i></a>
                                  </li>
                                  <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                       <a href='/admin/user/$id/edit' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-pen'></i></a>
                                  </li>
                                  <li data-bs-toggle='tooltip' data-bs-placement='top' aria-label='View'>
                                       <a href='/admin/user/$id/delete' class='btn btn-sm btn-soft-primary'><i class='mdi mdi-pen'></i></a>
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

        $response = DataTableHelper::complex( $_GET, $sql_details, $table, $primaryKey, $columns);

        return new JsonResponse($response);
    }

}
