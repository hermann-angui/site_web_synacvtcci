<?php

namespace App\Controller\Admin;

use App\Entity\Artisan;
use App\Entity\Payment;
use App\Entity\Service;
use App\Helper\ActivityLogger;
use App\Repository\ArtisanRepository;
use App\Repository\PaymentRepository;
use App\Repository\ServiceRepository;
use App\Service\ConfigurationService\ConfigurationService;
use App\Service\Artisan\ArtisanService;
use App\Service\Payment\PaymentService;
use App\Service\Wave\WaveService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/payment/wave')]
class WaveController extends AbstractController
{
    #[Route(path: '', name: 'admin_wave_payment_checkout_webhook')]
    public function callbackWavePayment(Request $request, PaymentRepository $paymentRepository, ArtisanRepository $artisanRepository): Response
    {
        $payload = json_decode($request->getContent(), true);
        if (!empty($payload) && array_key_exists("data", $payload)) {
            $data = $payload['data'];
            if (!empty($data) && array_key_exists("client_reference", $data)) {
                $payment = $paymentRepository->findOneBy(["reference" => $data["client_reference"]]);
                if ($payment && (array_key_exists("payment_status", $data) && (strtoupper($data["payment_status"]) === "SUCCEEDED"))) {
                    $payment->setCodePaymentOperateur($data["transaction_id"]);
                    $payment->setStatus("PAID");
                    $paymentRepository->add($payment, true);
                }
            }
        }
        return $this->json($payload);
    }

    #[Route(path: '/checkout/{status}', name: 'admin_wave_payment_callback')]
    public function wavePaymentCheckoutStatusCallback($status, Request $request, ArtisanRepository $artisanRepository, PaymentRepository $paymentRepository): Response
    {
        $payment = $paymentRepository->findOneBy(["reference" => $request->get("ref")]);
        if ($payment && (strtoupper(trim($status)) === "SUCCESS")) {
                $payment->setStatus("PAID");
                $artisan = $payment->getPaymentFor();
                $paymentRepository->add($payment, true);
                $artisan->setHasPaidFraisEnrollement(true);
                $artisanRepository->add($artisan, true);
                return $this->redirectToRoute('admin_payment_success_page', ["id" => $payment->getId()]);
        }
        return $this->redirectToRoute('admin_index');
    }

    #[Route(path: '/make', name: 'make_wave_payment')]
    public function makeWavePayment(Request $request, WaveService $waveService, ArtisanService $artisanService, ServiceRepository $serviceRepository, ActivityLogger $activityLogger, PaymentService $paymentService): Response
    {
        $payload = $request->request->all();
        $service = $serviceRepository->find($payload['service_id']);
        $artisan = $artisanService->find($payload['artisan_id']);

        $response = $waveService->pay($service->getMontant());
        if ($response) {
            $payment = $paymentService->savePayment(
                $artisan,
                $this->getUser(),
                $response->getAmount(),
                $response->getClientReference(),
                $service->getId(),
                $response->getPaymentStatus(),
                'MOBILE_MONEY',
                "WAVE"
            );
            $activityLogger->create($payment, $service->getDescription());
            if($request->isXmlHttpRequest()) return $this->json($response->getWaveLaunchUrl());
            return $this->redirect($response->getWaveLaunchUrl());
        } else return $this->redirectToRoute('admin_index');
    }

}
