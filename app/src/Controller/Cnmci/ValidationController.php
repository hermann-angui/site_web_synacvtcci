<?php

namespace App\Controller\Cnmci;

use App\Entity\Artisan;
use App\Service\Artisan\ArtisanService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cnmci/validate')]
class ValidationController extends AbstractController
{
     #[Route('/paiement-souscription/batch', name: 'cnmci_validate_payment_batch', methods: ['GET', 'POST'])]
    public function validateBatchPayment(Request $request, ArtisanService $artisanService): Response
    {
        if(!empty($request->get('ids'))) $artisanService->validatePaymentBatch($request->get('ids'));
        return $this->json('SUCCESS');
    }

    #[Route('/enrolement-cnmci/batch', name: 'cnmci_validation_enrolement_batch', methods: ['POST'])]
    public function validateEnrolementBatch(Request $request, ArtisanService $artisanService): Response
    {
        if(!empty($request->get('ids'))) $artisanService->validateEnrolementBatch($request->get('ids'));
        return $this->json('SUCCESS');
    }

    #[Route('/enrolement-cnmci', name: 'cnmci_validation_enrolement', methods: ['POST', 'GET'])]
    public function validateEnrolement(Request $request, ArtisanService $artisanService): Response
    {
        $response = $artisanService->validateEnrolement(
            intval($request->get('artisan_id')),
            $request->get('cnmci_numero_rm'),
            $request->get('cnmci_carte_professionelle')
        );
        if($response) return $this->json('SUCCESS');
        else return $this->json('FAILED');
    }

    #[Route('/reject-enrolement-cnmci', name: 'cnmci_reject_enrolement', methods: ['POST', 'GET'])]
    public function rejectEnrolement(Request $request, ArtisanService $artisanService): Response
    {
        $response = $artisanService->rejectEnrolement(
            intval($request->get('artisan_enrolement_reject_id')),
            $request->get('reason_reject_enrolement')
        );
        if($response) return $this->json('SUCCESS');
        else return $this->json('FAILED');
    }

    #[Route('/reject-paiement-cnmci', name: 'cnmci_reject_payment', methods: ['POST', 'GET'])]
    public function rejectPayment(Request $request, ArtisanService $artisanService): Response
    {
        $response = $artisanService->rejectPayment(
            intval($request->get('artisan_payment_reject_id')),
            $request->get('reason_reject_payment')
        );
        if($response) return $this->json('SUCCESS');
        else return $this->json('FAILED');
    }

    #[Route('/paiement-enrolement/{id}', name: 'cnmci_validate_payment', methods: ['GET', 'POST'])]
    public function validatePayment(Artisan $artisan, ArtisanService $artisanService): Response
    {
        if($artisan && !$artisan->getIsPaymentValidated()) $artisanService->validatePayment($artisan);
        return $this->json('SUCCESS');
    }

    #[Route('/generate/virtual-cnmci-carte/{id}', name: 'cnmci_generate_virtual-cnmci-carte', methods: ['GET', 'POST'])]
    public function generateCnmciCard(Artisan $artisan, ArtisanService $artisanService): Response
    {
        $artisanService->generateSingleCnmciCard($artisan);
      //  if($artisan && $artisan->getIsInscriptionValidated()) $artisanService->generateSingleCnmciCard($artisan);
        return $this->json('SUCCESS');
    }
}
