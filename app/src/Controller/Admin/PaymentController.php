<?php

namespace App\Controller\Admin;

use App\Entity\Artisan;
use App\Entity\Payment;
use App\Helper\ActivityLogger;
use App\Repository\ArtisanRepository;
use App\Repository\ServiceRepository;
use App\Service\Artisan\ArtisanService;
use App\Service\Payment\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route(path: '', name: 'admin_payment_index')]
    public function index(Request $request, ArtisanRepository $artisanRepository): Response
    {
        $artisans = $artisanRepository->findAll();
        return $this->render('admin/pages/index.html.twig', ["artisans" => $artisans]);
    }

    #[Route(path: '/choose/{id}', name: 'admin_payment_choose')]
    public function searchMain(Artisan $artisan): Response
    {
        return $this->render('admin/payment/choose.html.twig', ['artisan' => $artisan]);
    }

    #[Route(path: '/cashin', name: 'admin_payment_cash')]
    public function cashin(Requestt $request, ServiceRepository $serviceRepository, PaymentService $paymentService, ArtisanService $artisanService, ActivityLogger $activityLogger): Response
    {
        $payload = $request->request->all();
        $service = $serviceRepository->find($payload['service_id']);
        $artisan = $artisanService->find($payload['artisan_id']);
        $payment = $paymentService->cashIn(
            $artisan,
            $this->getUser(),
            $service->getMontant(),
            null,
            $service->getId()
        );
        $paymentService->generatePaymentReceipt($payment);
        $activityLogger->create($payment, $service->getDescription());
        return $this->redirectToRoute('payment_success_page', ['id' => $payment->getId()]);
    }

    #[Route(path: '/receipt/{id}', name: 'artisan_display_receipt', methods: ['POST', 'GET'])]
    public function showPaymentReceipt(?Payment $payment, PaymentService $paymentService): Response
    {
        $paymentService->generatePaymentReceipt($payment);
        return $this->render('admin/payment/receipt.html.twig', ['payment' => $payment]);
    }

    #[Route(path: '/successpage/{id}', name: 'admin_payment_success_page', methods: ['POST', 'GET'])]
    public function paymentSuccessPage(?Payment $payment, PaymentService $paymentService, ArtisanRepository $artisanRepository): Response
    {
        $paymentService->generatePaymentReceipt($payment);
        $artisan = $payment->getPaymentFor();
        $artisan->setEtape(3);
        $artisanRepository->add($artisan, true);
        return $this->render('admin/payment/payment-success.html.twig', ['payment' => $payment]);
    }

    #[Route('/receipt/download/{id}', name: 'download_payment_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Payment $payment, PaymentService $paymentService, ActivityLogger $activityLogger): Response
    {
        $activityLogger->create($payment, "Téléchargement de reçu");
        return $paymentService->downloadArtisanPaymentReceipt($payment);
    }
}
