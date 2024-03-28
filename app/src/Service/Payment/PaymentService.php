<?php

namespace App\Service\Payment;

use App\Entity\Payment;
use App\Helper\PdfGenerator;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Uid\Uuid;

class PaymentService
{
    private const WEBSITE_URL = "https://synacvtcci.org";
    private const MEDIA_DIR = "/var/www/html/public/members/";

    public function __construct(private PdfGenerator $pdfGenerator,
                                private MemberRepository $memberRepository,
                                private PaymentRepository $paymentRepository)
    {}

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
        return new PdfResponse($content, 'recu_syndicat.pdf');
    }

     /**
     * @param Payment|null $payment
     * @param string $viewTemplate
     * @return string|null
     */
    public function generatePaymentReceipt(?Payment $payment)
    {
        try {
            $member = $payment->getPaymentFor();

            if (!$member->getMatricule()) {
                $date = new \DateTime('now');
                $sexCode = null;
                if($member->getSex() === "H") $sexCode = "SY1";
                elseif($member->getSex() === "F") $sexCode = "SY2";
                if($sexCode){
                    $matricule = sprintf('%s%s%05d', $sexCode, $date->format('Y'), $member->getId());
                    $member->setMatricule($matricule);
                    $this->memberRepository->add($member, true);
                }
            }

            $qrCodeData = self::WEBSITE_URL . "/profile/" . $member->getReference();

            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            $folder = self::MEDIA_DIR . $member->getReference() . '/';
            if(!file_exists($folder)) mkdir($folder, 0777, true);

            $barcode_file = $folder . "payment_barcode.png";
            file_put_contents($barcode_file, $content);

            $receipt_file = $folder . time() . uniqid() . ".pdf";
            $viewTemplate = 'admin/payment/payment-receipt-pdf.html.twig';

            if($payment->getTarget()  === "FRAIS_CARTE_SYNDICAT"){
                $viewTemplate = 'admin/payment/payment-receipt-carte-syndicat-pdf.html.twig';
                $member->setHasPaidForSyndicat(true);
                $member->setIsSyndicatMember(true);
                $member->setPaymentReceiptCarteSyndicatPdf(basename($receipt_file));
            }

            if($payment->getTarget()  === "FRAIS_SERVICE_TECHNIQUE"){
                $viewTemplate = 'admin/payment/payment-receipt-pdf.html.twig';
                $member->setPaymentReceiptSynacvtcciPdf(basename($receipt_file));
            }

            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['payment' => $payment]);
            file_put_contents($receipt_file, $content);

            if(file_exists($barcode_file)) \unlink($barcode_file);

            $member->setStatus("PAID");
            $this->memberRepository->add($member, true);

            return $content ?? null;

        }catch(\Exception $e){
            if(file_exists($barcode_file)) \unlink($barcode_file);
            if(file_exists($receipt_file)) \unlink($receipt_file);
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
