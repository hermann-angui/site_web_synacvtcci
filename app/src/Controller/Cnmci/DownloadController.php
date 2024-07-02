<?php

namespace App\Controller\Cnmci;

use App\Entity\Artisan;
use App\Helper\ActivityLogger;
use App\Repository\ArtisanRepository;
use App\Service\Artisan\ArtisanService;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cnmci/telecharger')]
class DownloadController extends AbstractController
{
    #[Route('/photo/{id}', name: 'cnmci_download_photo', methods: ['GET', 'POST'])]
    public function downloadPhoto(Request $request, Artisan $artisan): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $imageUrl = $this->getParameter("kernel.project_dir") . "/public/artisans/" . $artisan->getReference() . "/" . basename($artisan->getPhoto());
        $info = new SplFileInfo($imageUrl);
        $outputFile = $artisan->getReference() . '_' . $artisan->getLastName() . ' ' . $artisan->getFirstName() . '.' . $info->getExtension();
        return $this->file($imageUrl, $outputFile);
    }

    #[Route('/fiche-pdf/{id}', name: 'cnmci_download_cnmci_pdf', methods: ['GET'])]
    public function downloadCnmciPdf(Artisan $artisan, ArtisanService $artisanService, ActivityLogger $activityLogger): Response {
        $activityLogger->create($artisan, "Téléchargement fiche de la chambre nationale de métier");
        return $artisanService->downloadCNMCIPdf($artisan);
    }

    #[Route('/adherents', name: 'cnmci_download_list', methods: ['POST', 'GET'])]
    public function downloadList(Request $request, ArtisanRepository $artisanRepository, ArtisanService $artisanService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $from = $request->get('date_from');
        $to = $request->get('date_to');
        $artisans = $artisanRepository->findAdherentsFromTo($from, $to);
        if (!$artisans) return $this->json(null);
        $file = $artisanService->generateAdherentListXlsxFile($artisans);
        $outputFile = $artisanService->archiveArtisanDocuments($artisans, $file);
        return $this->file($outputFile, 'liste_adherents.zip');
    }

    #[Route('/matrice', name: 'cnmci_download_matrice', methods: ['POST', 'GET'])]
    public function downloadMatriceEncaissement(Request $request, ArtisanRepository $artisanRepository, ArtisanService $artisanService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        ini_set('max_execution_time', '-1');
        $from = $request->get('date_from');
        $to = $request->get('date_to');
        $artisans = $artisanRepository->findAdherentsFromTo($from, $to);
        if (!$artisans) return $this->json(null);
        $fileXls = $artisanService->generateMatriceEncaissementXlsxFile($artisans);
        return $this->file($fileXls, basename($fileXls));
    }


}
