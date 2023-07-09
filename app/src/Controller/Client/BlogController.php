<?php

namespace App\Controller\Client;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blog')]
class BlogController extends AbstractController
{
    #[Route('/', name: 'blog_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('frontend/blog/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'blog_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('frontend/blog/show.html.twig', [
            'article' => $article,
        ]);
    }

}
