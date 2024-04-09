<?php

namespace App\Service\Payment;

use App\Entity\Member;
use App\Entity\Payment;
use App\Entity\User;
use App\Helper\PdfGenerator;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use App\Service\ConfigurationService\ConfigurationService;
use App\Service\Member\MemberService;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;


class PaymentService
{
    private const MEDIA_DIR = "/var/www/html/public/members/";

    public function __construct(private PdfGenerator $pdfGenerator, private MemberRepository $memberRepository, private ConfigurationService $configurationService, private PaymentRepository $paymentRepository)
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
    public function downloadMemberPaymentReceipt(?Payment $payment) {
        set_time_limit(0);
        $content = $this->generatePaymentReceipt($payment);
        return new PdfResponse($content, 'recu_syndicat.pdf');
    }

     /**
     * @param Payment|null $payment
     * @return mixed
     */
    public function generatePaymentReceipt(?Payment $payment)
    {
        try {
            $member = $payment->getPaymentFor();

            if (!$member->getMatricule()) {
                $matricule = MemberService::generateMatricule($member);
                $member->setMatricule($matricule);
                $this->memberRepository->add($member, true);
            }

            $qrCodeData = $this->configurationService->getParameter('app.base_url') . "profile/" . $member->getReference();

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
                $viewTemplate = 'admin/payment/payment-receipt-service-technique-pdf.html.twig';
                $member->setPaymentReceiptServiceTechniquePdf(basename($receipt_file));
            }

            $this->memberRepository->add($member, true);

            $content = $this->pdfGenerator->generatePdf($viewTemplate, ['payment' => $payment]);
            file_put_contents($receipt_file, $content);

            if(file_exists($barcode_file)) \unlink($barcode_file);
            return "Here ";
            //return $content;

        }catch(\Exception $e){
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
            if(file_exists($barcode_file)) \unlink($barcode_file);
            if(file_exists($receipt_file)) \unlink($receipt_file);
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
     * @param Member|null $member
     * @param UserInterface|null $user
     * @param int|null $montant
     * @param string|null $reference
     * @param string|null $target
     * @param string|null $status
     * @param string|null $type
     * @param string|null $operateur
     * @return Payment
     */
    public function create(?Member $member, ?UserInterface $user, ?int $montant, ?string $reference, ?string $target, ?string $status, ?string $type, ?string $operateur): Payment {
        $payment = new Payment();
        $payment->setUser($user)
                ->setReference($reference?:$this->generateReference())
                ->setType(strtoupper($type))
                ->setMontant($montant)
                ->setTarget($target)
                ->setPaymentFor($member)
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


}
