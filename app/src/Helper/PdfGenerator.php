<?php

namespace App\Helper;

use Knp\Snappy\Pdf;
use TCPDF2DBarcode;
use Twig\Environment;

class PdfGenerator
{
    public function __construct(protected Pdf $pdf, protected Environment $twig){ }

    public function generatePdf(string $twigTemplate, array $viewData): ?string
    {
        $html = $this->twig->render($twigTemplate, $viewData);
        return $this->pdf->getOutputFromHtml($html);
    }

    public function generateBarCode($data, $width = 50, $height = 50)
    {
        $barCodeObj = new TCPDF2DBarcode($data, "QRCODE" );
        return $barCodeObj->getBarcodePngData($width, $height);
    }

}
