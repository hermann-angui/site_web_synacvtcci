<?php

namespace App\Helper;

use TCPDF2DBarcode;

class ImageGenerator extends ImageRenderer
{
    public function generate($data)
    {
        $html = $this->twig->render($data['twig_view'], $data['view_data']);
        $output = $this->snappy->getOutputFromHtml($html);
        file_put_contents($data['view_data']['cardpath'], $output);
        return $data['view_data']['cardpath'];
    }

    public function generateBarCode($data, $outputFile, $width = 50, $height = 50)
    {
        $barCodeObj = new TCPDF2DBarcode($data, "QRCODE" );
        $barCodeImage = $barCodeObj->getBarcodePngData($width, $height);
        file_put_contents($outputFile, $barCodeImage);

        return $outputFile;
    }
}