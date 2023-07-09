<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\MediaAsset;
use App\Form\ArticleType;
use App\Helper\FileUploadHelper;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'admin_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('admin/article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_article_new', methods: ['GET','POST'])]
    public function new(Request $request, ArticleRepository $articleRepository, FileUploadHelper $fileUploadHelper): Response
    {
        if($request->isMethod("POST")){
            $files = $request->files->all();
            if(!empty($files)){
                foreach($files["article"]["images"] as $file){
                   $mediaAsset = new MediaAsset();
                   $mediaAsset->setFile($fileName);
                   $mediaAsset->setArticle($fileName);
                   $mediaAsset->setDescription($fileName);
                   $mediaAsset->setName($fileName);
                   $fileName = $fileUploadHelper->upload($file, "/var/www/html/public/media");
                }
            }

            // $articleRepository->add($article, true);
            return $this->redirectToRoute('admin_article_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('admin/article/new.html.twig');
    }

    #[Route('/{id}', name: 'admin_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('admin/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->add($article, true);

            return $this->redirectToRoute('admin_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $articleRepository->remove($article, true);
        }

        return $this->redirectToRoute('admin_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
