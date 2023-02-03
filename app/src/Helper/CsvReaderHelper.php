<?php

namespace App\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CsvReaderHelper
{
    protected ContainerInterface $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function read(string $file)
    {
        $context = [
            CsvEncoder::DELIMITER_KEY => ',',
            CsvEncoder::ENCLOSURE_KEY => '"',
            CsvEncoder::ESCAPE_CHAR_KEY => '\\',
            CsvEncoder::KEY_SEPARATOR_KEY => ',',
        ];
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        return $serializer->decode(file_get_contents($file), 'csv', $context);
    }
}



