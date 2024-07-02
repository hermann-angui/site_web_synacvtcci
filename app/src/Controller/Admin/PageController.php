<?php

namespace App\Controller\Admin;

use App\Entity\Child;
use App\Entity\Artisan;
use App\Entity\Payment;
use App\Form\ArtisanRegistrationType;
use App\Repository\ArtisanRepository;
use App\Service\ConfigurationService\ConfigurationService;
use App\Service\Artisan\ArtisanService;
use App\Service\Payment\PaymentService;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PageController extends AbstractController
{
    #[Route(path: '', name: 'admin_index')]
    public function index(Request $request, Security $security): Response
    {
        if($security->isGranted('ROLE_DIRECTEUR_CNMCI')) {
            return $this->redirectToRoute('cnmci_stats');
        }
        return $this->render('admin/pages/index.html.twig');
    }

    #[Route(path: '/dashboard', name: 'admin_dashboard')]
    public function dashboard(Request $request): Response
    {
        return $this->render('admin/pages/dashboard.html.twig');
    }

    #[Route(path: '/generate/tracking_codes', name: 'admin_generate_tracking_codes')]
    public function generateTrackingCodes(Request $request, ArtisanRepository $artisanRepository): Response
    {
        $trackingCodes = [];
        $from = $request->get('from') ;
        $to = $request->get('to') ;
        if($from && $to){
            $from = (int)ltrim($from, '0');
            $to = (int)ltrim($to, '0');
            $trackingCodes = array_map(function($num){
                return sprintf('05d', $num);
            }, range($from, $to));
        }
        return $this->render('admin/pages/generate_tracking_codes.html.twig', ['tracking_codes' => $trackingCodes]);
    }

    #[Route(path: '/profile/{reference}', name: 'public_artisan_profile')]
    public function artisanProfile(Request $request, ArtisanRepository $artisanRepository): Response
    {
        $artisan = $artisanRepository->findOneBy(["reference" => $request->get("reference")]);
        if($artisan)  return $this->render('admin/artisan/public_profile.html.twig', ["artisan" => $artisan]);
        else return $this->redirectToRoute('home');
    }

    private function handleFormCreation(Request $request, FormInterface $form, Artisan &$artisan, ArtisanService $artisanService): Artisan {

        $images = [];

        if($form->has('photo'))  $images['photo'] = $form->get('photo')?->getData();
        if($form->has('photoPieceFront'))  $images['photoPieceFront'] = $form->get('photoPieceFront')?->getData();
        if($form->has('photoPieceBack'))  $images['photoPieceBack'] = $form->get('photoPieceBack')?->getData();
        if($form->has('photoPermisFront'))  $images['photoPermisFront'] = $form->get('photoPermisFront')?->getData();
        if($form->has('photoPermisBack'))  $images['photoPermisBack'] = $form->get('photoPermisBack')?->getData();

        if($form->has('paymentReceiptCnmci'))  $images['paymentReceiptCnmci'] = $form->get('paymentReceiptCnmci')?->getData();
        if($form->has('paymentReceiptSyndicatPdf'))  $images['paymentReceiptSyndicatPdf'] = $form->get('paymentReceiptSyndicatPdf')?->getData();

        $data = $request->request->all();
        if(isset($data['child'])){
            foreach($data['child'] as $childItem){
                $child=  new Child();
                $child->setLastName($childItem['lastname']);
                $child->setFirstName($childItem['firstname']);
                $child->setSex($childItem['sex']);
                $child->setArtisan($artisan);
                $artisan->addChild($child);
            }
        }
        $artisanService->createArtisan($artisan, $images);
        return $artisan;
    }

    #[Route('/download/receipt/{id}', name: 'download_receipt_pdf', methods: ['GET'])]
    public function pdfGenerate(Artisan $artisan, ArtisanService $artisanService): Response
    {
        set_time_limit(0);
        $content = $artisanService->generateOnlineRegistrationReceipt($artisan);
        return new PdfResponse($content, 'recu_inscriptoin.pdf');
    }

    #[Route('/download/syndicat/receipt/{id}', name: 'download_payment_receipt_carte_syndicat_pdf', methods: ['GET'])]
    public function downloadReceiptCarteSyndiact(?Payment $payment, PaymentService $paymentService): Response
    {
        set_time_limit(0);
        $content = $paymentService->generatePaymentReceipt($payment);
        return new PdfResponse($content, 'recu_payment.pdf');
    }

    #[Route('/preinscription/{tracking_code}', name: 'presubscribe', methods: ['GET']), ]
    public function presubscribe (string $tracking_code, Request $request, ArtisanRepository $artisanRepository): Response
    {
        date_default_timezone_set("Africa/Abidjan");

        $artisan = $artisanRepository->findOneBy(['tracking_code' => $tracking_code]);
        $form = $this->createForm(ArtisanRegistrationType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artisanRepository->add($artisan, true);

            return $this->redirectToRoute('home');
        }

        return $this->renderForm('frontend/artisan/self_subscription.html.twig', [
            'artisan' => $artisan,
            'form' => $form,
        ]);
    }

    #[Route('/checkvalidity/{cnmci_numero_rm}', name: 'check_validity', methods: ['GET']), ]
    public function checkValidityByRmNumber (string $cnmci_numero_rm, ArtisanRepository $artisanRepository,ConfigurationService $configurationService): Response
    {
        date_default_timezone_set("Africa/Abidjan");

        $artisan = $artisanRepository->findOneBy(['cnmci_numero_rm' => $cnmci_numero_rm]);
        if($artisan) {
            return $this->json([
                'success' => true,
                'image_url' => $configurationService->getParameter('app.base_url') . 'artisans/' . $artisan->getReference() . '/' . $artisan->getCnmciCardPhoto()
            ]);
        } else {
            return $this->json([
                'error' => true,
                'image_url' => $configurationService->getParameter('app.base_url') . "assets/files/carte_cnmci_fake.jpg"
            ]);
        }
    }

    #[Route('/showcnmci/{id}', name: 'check_validity', methods: ['GET']), ]
    public function showCnmci (Artisan $artisan, ConfigurationService $configurationService): Response
    {
        date_default_timezone_set("Africa/Abidjan");
        if($artisan) {
            return $this->json([
                'success' => true,
                'image_url' => $configurationService->getParameter('app.base_url') . 'artisans/' . $artisan->getReference() . '/' . $artisan->getCnmciCardPhoto()
            ]);
        } else {
            return $this->json([
                'error' => true,
                'image_url' => $configurationService->getParameter('app.base_url') . "assets/files/carte_cnmci_fake.jpg"
            ]);
        }
    }
}
