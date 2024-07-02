<?php

namespace App\Service\Payment;

use App\Entity\Artisan;
use App\Entity\Payment;
use App\Entity\User;
use App\Helper\FileHelper;
use App\Helper\ArtisanAssetHelper;
use App\Helper\PdfGenerator;
use App\Repository\ArtisanRepository;
use App\Repository\PaymentRepository;
use App\Service\ConfigurationService\ConfigurationService;
use App\Service\Artisan\ArtisanService;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;


class PaymentService
{
    public function __construct(private PdfGenerator $pdfGenerator,
                                private ArtisanRepository $artisanRepository,
                                private ArtisanAssetHelper $artisanAssetHelper,
                                private ConfigurationService $configurationService,
                                private PaymentRepository $paymentRepository)
    {}

    /**
     * @return string
     */
    public static function generateReference() {
        return str_replace("-", "", substr(Uuid::v4()->toRfc4122(), 0, 18));
    }

    /**
     * @param Payment|null $payment
     * @return PdfResponse
     */
    public function downloadArtisanPaymentReceipt(?Payment $payment) {
        set_time_limit(0);
        $content = $this->generatePaymentReceipt($payment);
        return new PdfResponse($content, 'recu_syndicat.pdf');
    }

     /**
     * @param Payment|null $payment
     * @return string|null
     */
    public function generatePaymentReceipt(?Payment $payment): ?string
    {
        try {
            $artisan = $payment->getPaymentFor();
            $folder = $this->artisanAssetHelper->getArtisanDir($artisan);

            $qrCodeData = $this->configurationService->getParameter('app.base_url') . "profile/" . $artisan->getReference();
            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            if(!file_exists($folder)) mkdir($folder, 0777, true);
            $barcode_file = $folder . "payment_barcode.png";
            file_put_contents($barcode_file, $content);


            $receipt_file = $folder . time() . uniqid() . ".pdf";
            $viewTemplate = 'admin/payment/payment-receipt-service-technique-pdf.html.twig';

            if($payment->getTarget()  === "FRAIS_SERVICE_TECHNIQUE") {
                $viewTemplate = 'admin/payment/payment-receipt-service-technique-pdf.html.twig';
                FileHelper::deleteExistingFile($folder . $artisan->getPaymentReceiptServiceTechniquePdf());
                $artisan->setPaymentReceiptServiceTechniquePdf(basename($receipt_file));
            }

            $this->artisanRepository->add($artisan, true);

            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['payment' => $payment]);
            file_put_contents($receipt_file, $content);

            FileHelper::deleteExistingFile($barcode_file);

            return $content ?? null;

        } catch(\Exception $e) {
            FileHelper::deleteExistingFile($barcode_file);
            FileHelper::deleteExistingFile($receipt_file);
        }
        return null;
    }

    /**
     * @param Payment $payment
     * @return void
     */
    public function store(Payment $payment): void
    {
         $this->paymentRepository->add($payment, true);
    }

    /**
     * @param Artisan|null $artisan
     * @param UserInterface|null $user
     * @param int|null $montant
     * @param string|null $reference
     * @param string|null $target
     * @param string|null $status
     * @param string|null $type
     * @param string|null $operateur
     * @return Payment
     */
    public function savePayment(?Artisan $artisan, ?UserInterface $user, ?int $montant, ?string $reference, ?string $target, ?string $status, ?string $type, ?string $operateur): Payment {
        $payment = new Payment();
        $payment->setUser($user)
                ->setReference($reference?:$this->generateReference())
                ->setType(strtoupper($type))
                ->setMontant($montant)
                ->setTarget($target)
                ->setPaymentFor($artisan)
                ->setStatus($status)
                ->setOperateur($operateur)
                ->setCodePaymentOperateur(null)
                ->setReceiptNumber($this->generateReference())
                ->setReceiptFile(null)
                ->setCreatedAt(new \DateTime('now'))
                ->setModifiedAt(new \DateTime('now'));
         $this->store($payment);
         return $payment;
    }


    /**
     * @param Artisan|null $artisan
     * @param UserInterface|null $user
     * @param int|null $montant
     * @param string|null $reference
     * @param string|null $target
     * @return Payment
     */
    public function cashIn(?Artisan $artisan, ?UserInterface $user, ?int $montant, ?string $reference, ?string $target): Payment {
        $payment = new Payment();
        $payment->setUser($user)
            ->setReference($reference?:$this->generateReference())
            ->setType("CASH")
            ->setMontant($montant)
            ->setTarget($target)
            ->setPaymentFor($artisan)
            ->setStatus('PAID')
            ->setOperateur(null)
            ->setCodePaymentOperateur(null)
            ->setReceiptNumber($this->generateReference())
            ->setReceiptFile(null)
            ->setCreatedAt(new \DateTime('now'))
            ->setModifiedAt(new \DateTime('now'));
        $this->store($payment);
        return $payment;
    }

    public function  findArtisanPaymentByTarget(Artisan $artisan, string $target): ?Payment
    {
        return  $this->paymentRepository->findOneBy(['payment_for' => $artisan, 'target' => $target]);

    }
}
