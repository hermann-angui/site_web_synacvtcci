<?php

namespace App\Service\Payment;

use App\Entity\Member;
use App\Entity\Payment;
use App\Helper\PdfGenerator;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Uid\Uuid;

class PaymentService
{
    public function __construct(private ContainerInterface  $container,
                                private PdfGenerator $pdfGenerator,
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
        $content = $this->generatePaymentSynacvtcciReceipt($payment);
        return new PdfResponse($content, 'recu_synacvtcci.pdf');
    }

     /**
     * @param Member|null $payment
     * @param array|null $payments
     * @return string|null
     */
    public function generatePaymentSynacvtcciReceipt(?Member $member, ?array $payments = [])
    {
        try {
            if(empty($payments)) $payments = $member->getPayments();
            $qrCodeData = $this->container->getParameter('app.baseurl') . "/profile/" . $member->getMatricule();

            $content = $this->pdfGenerator->generateBarCode($qrCodeData, 50, 50);
            $folder = $this->container->getParameter('app.member.dir') . $member->getReference() . '/';
            if(!file_exists($folder)) mkdir($folder, 0777, true);

            $barcode_file = $folder . "payment_barcode.png";
            file_put_contents($barcode_file, $content);

            $viewTemplate = 'admin/payment/payment-receipt-pdf.html.twig';
            $receipt_file = $folder . time() . uniqid() . ".pdf";
            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['member'=> $member, 'payments' => $payments]);
            file_put_contents($receipt_file, $content);

            if(file_exists($barcode_file)) \unlink($barcode_file);

            $member->setPaymentReceiptSynacvtcciPdf(basename($receipt_file));
            $this->memberRepository->add($member, true);

            foreach($payments as $payment){
                $payment->setReceiptFile($receipt_file);
                $this->paymentRepository->add($payment, true);
            }

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


    public function findOneBy(array $criteria, array|null $orderBy = null) : ?Payment
    {
        return $this->paymentRepository->findOneBy($criteria, $orderBy);
    }

    public function findBy(array $criteria, array|null $orderBy, $limit = null, $offset = null): ?array
    {
        return $this->paymentRepository->findBy( $criteria, $orderBy, $limit, $offset);
    }

    public function findAll(): ?array
    {
        return $this->paymentRepository->findAll();
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?Payment
    {
        return $this->paymentRepository->find($id, $lockMode, $lockVersion);
    }
}
