<?php

namespace App\Controller\Admin;

use App\Entity\Artisan;
use App\Form\ArtisanType;
use App\Repository\ArtisanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/artisan')]
class ArtisanController extends AbstractController
{
    #[Route('/', name: 'admin_artisan_index', methods: ['GET'])]
    public function index(ArtisanRepository $artisanRepository): Response
    {
        return $this->render('admin/artisan/index.html.twig', [
            'artisans' => $artisanRepository->findAll(),
        ]);
    }

    #[Route('/register', name: 'admin_artisan_register', methods: ['GET'])]
    public function register(Request $request): Response
    {
        return $this->render('admin/artisan/show.html.twig');
    }

    #[Route('/new', name: 'admin_artisan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArtisanRepository $artisanRepository): Response
    {
        $artisan = new Artisan();
        $form = $this->createForm(ArtisanType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artisanRepository->add($artisan, true);

            return $this->redirectToRoute('admin_artisan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/artisan/new.html.twig', [
            'artisan' => $artisan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_artisan_show', methods: ['GET'])]
    public function show(artisan $artisan): Response
    {
        return $this->render('admin/artisan/show.html.twig', [
            'artisan' => $artisan,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_artisan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, artisan $artisan, ArtisanRepository $artisanRepository): Response
    {
        $form = $this->createForm(ArtisanType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artisanRepository->add($artisan, true);

            return $this->redirectToRoute('admin_artisan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/artisan/edit.html.twig', [
            'artisan' => $artisan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_artisan_delete', methods: ['POST'])]
    public function delete(Request $request, artisan $artisan, ArtisanRepository $artisanRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$artisan->getId(), $request->request->get('_token'))) {
            $artisanRepository->remove($artisan, true);
        }

        return $this->redirectToRoute('admin_artisan_index', [], Response::HTTP_SEE_OTHER);
    }
}
