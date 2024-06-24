<?php

namespace App\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CsvReaderHelper
{
    public function read(string $file_path, string $separator = ',', string $enclosure = '"', string $escape = '\\')
    {
        $context = [
            CsvEncoder::DELIMITER_KEY => ',',
            CsvEncoder::ENCLOSURE_KEY => '"',
            CsvEncoder::ESCAPE_CHAR_KEY => '\\',
            CsvEncoder::KEY_SEPARATOR_KEY => ',',
        ];
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        return $serializer->decode(file_get_contents($file_path), 'csv', $context);
    }
}



