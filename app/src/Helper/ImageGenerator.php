<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\File\File;
use TCPDF2DBarcode;

class ImageGenerator extends ImageRenderer
{
    public function generate($data): ?File
    {
        $html = $this->twig->render($data['twig_view'], $data);
        $output = $this->snappy->getOutputFromHtml($html);
        file_put_contents($data['card_path'], $output);
        return new File($data['card_path']);
    }

    public function generateBarCode($data, $outputFile, $color = [0,0,0], $width = 50, $height = 50): ?string
    {
        $barCodeObj = new TCPDF2DBarcode($data, "QRCODE" );
        $barCodeImage = $barCodeObj->getBarcodePngData($width, $height, $color);
        file_put_contents($outputFile, $barCodeImage);
        return $outputFile;
    }
}
