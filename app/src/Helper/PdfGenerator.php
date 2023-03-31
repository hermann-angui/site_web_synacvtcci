<?php

namespace App\Helper;

use Knp\Snappy\Pdf;
use TCPDF2DBarcode;
use Twig\Environment;

class PdfGenerator
{

    protected $snappy;

    protected $twig;

    public function __construct(Pdf $snappy, Environment $twig){
        $this->snappy = $snappy;
        $this->twig = $twig;
    }
    public function generate($data)
    {
        $html = $this->twig->render($data['twig_view'], $data['view_data']);
      //  $output = $this->snappy->generateFromHtml($html);
    }

    public function generateBarCode($data, $outputFile, $width = 50, $height = 50)
    {
        $barCodeObj = new TCPDF2DBarcode($data, "QRCODE" );
        $barCodeImage = $barCodeObj->getBarcodePngData($width, $height);
        file_put_contents($outputFile, $barCodeImage);

        return $outputFile;
    }
}
