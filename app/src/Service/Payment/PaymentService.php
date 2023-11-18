<?php

namespace App\Service\Payment;

use App\Entity\Payment;
use App\Helper\PdfGenerator;
use App\Repository\PaymentRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Uid\Uuid;

class PaymentService
{
    private const WEBSITE_URL = "https://synacvtcci.org";
    private const MEDIA_DIR = "/var/www/html/public/members/";
    private const MONTANT = 10100;

    public function __construct(
        private PdfGenerator     $pdfGenerator,
        private PaymentRepository $paymentRepository)
    {
    }

    public static function generateReference() {
        $now = new \DateTime();
        $year = $now->format("y");
        return $year . strtoupper(substr(Uuid::v4()->toRfc4122(), 0, 8));
    }

    /**
     * @param Payment|null $payment
     * @param string $viewTemplate
     * @return PdfResponse
     */
    public function downloadMemberPaymentReceipt(?Payment $payment){
        set_time_limit(0);
        $content = $this->generatePaymentReceipt($payment);
        return new PdfResponse($content, 'recu_macaron.pdf');
    }

     /**
     * @param Payment|null $payment
     * @param string $viewTemplate
     * @return string|null
     */
    public function generatePaymentReceipt(?Payment $payment)
    {
        try {
            $qrCodeData = self::WEBSITE_URL . "/admin/payment/" . $payment->getId();
            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            $folder = self::MEDIA_DIR . $payment->getPaymentFor()->getReference() . '/';
            if(!file_exists($folder)) mkdir($folder, 0777, true);
            file_put_contents( $folder . "payment_barcode.png", $content);
            $viewTemplate = 'admin/payment/payment-receipt-pdf.html.twig';
            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['payment' => $payment]);
            file_put_contents($folder . "payment_receipt.pdf", $content);
            if(file_exists($folder . "payment_barcode.png")) \unlink($folder . "payment_barcode.png");
            return $content ?? null;

        }catch(\Exception $e){
            if(file_exists($folder . "payment_barcode.png")) \unlink($folder . "payment_barcode.png");
            if(file_exists($folder . "payment_receipt.pdf")) \unlink($folder . "payment_receipt.pdf");
        }
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
     * @param array $data
     * @return Payment
     * @throws \Exception
     */
    public function create($montant, $operateur = "WAVE", $type = "MOBILE_MONEY"): Payment {
        $payment = new Payment();
        $payment->setMontant($montant);
        $payment->setOperateur($operateur);
        $payment->setReceiptNumber($this->generateReference());
        $payment->setType(strtoupper($type));
        $this->paymentRepository->add($payment, true);
        return $payment;
    }

}
