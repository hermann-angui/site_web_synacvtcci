<?php

namespace App\Service\ConfigurationService;

use App\Entity\Configuration;
use App\Repository\ConfigurationRepository;
use Cassandra\Date;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 *
 */
class ConfigurationService
{
    public function __construct(private ConfigurationRepository $configurationRepository){}

    public function getParameter(string $name)
    {
       $configuration = $this->configurationRepository->findOneBy(['name' => $name]);

       if($configuration){
           return match ($configuration->getType()) {
               'int' => (int)$configuration?->getValue(),
               'float' => (float)$configuration?->getValue(),
               'datetime' => new \DateTime($configuration?->getValue()),
               'string' => (string) $configuration?->getValue(),
               default => $configuration?->getValue(),
           };
       }
       return null;
    }

}

