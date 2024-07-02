<?php

namespace App\Service\Artisan;

use App\Entity\Artisan;
use App\Helper\ImageGenerator;
use App\Service\ConfigurationService\ConfigurationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class ArtisanCardGeneratorService
{
    /**
     * @param ContainerInterface $container
     * @param ImageGenerator $imageGenerator
     * @param ConfigurationService $configurationService
     */
    public function __construct(private ContainerInterface $container, private ImageGenerator $imageGenerator, private ConfigurationService $configurationService)
    {
    }

    /**
     * @param Artisan|null $artisan
     * @return array|null
     */
    public function mapToCnmciCardFrontViewModel(?Artisan $artisan): ?array
    {
        $data['last_name'] = $artisan->getLastName();
        $data['first_name'] =  $artisan->getFirstName();
        $data['metier'] = $artisan->getActivity();
        $data['birth_date'] = $artisan->getDateOfBirth()->format('d/m/y');
        $data['birth_place'] = $artisan->getBirthCity();
        $data['card_bg'] = "/var/www/html/public/assets/files/carte_cnmci_front.jpg";

        $data['numero_rm'] = $artisan->getCnmciNumeroRm();
        $data['numero_carte_professionnelle'] = $artisan->getCnmciNumeroCarteProfessionelle();
        $data['twig_view'] = "admin/card_tmpl/carte_cnmci_front.html.twig";
        $data['outputdir'] = "/var/www/html/public/artisans/" . $artisan->getReference() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);

        $data['photo_path']  =  $data['outputdir'] . $artisan->getPhoto();
        $data['card_path']   = $data['outputdir'] . $artisan->getReference() . '_card_cnmci_front.png' ;

        return $data;
    }


    /**
     * @param Artisan|null $artisan
     * @return array|null
     */
    public function mapToCnmciCardBackViewModel(?Artisan $artisan): ?array
    {
        $data['last_name'] = $artisan->getLastName();
        $data['first_name'] =  $artisan->getFirstName();
        $data['metier'] = $artisan->getActivity();
        $data['birth_date'] = $artisan->getDateOfBirth()->format('d/m/y');
        $data['birth_place'] = $artisan->getBirthCity();
        $data['card_bg'] = "/var/www/html/public/assets/files/carte_cnmci_back.jpg";

        $data['numero_rm'] = $artisan->getCnmciNumeroRm();
        $data['numero_carte_professionnelle'] = $artisan->getCnmciNumeroCarteProfessionelle();
        $data['twig_view'] = "admin/card_tmpl/carte_cnmci_back.html.twig";
        $data['outputdir'] = "/var/www/html/public/artisans/" . $artisan->getReference() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);

        $data['photo_path']  =  $data['outputdir'] . $artisan->getPhoto();
        $data['card_path']   = $data['outputdir'] . $artisan->getReference() . '_card_cnmci_back.png' ;

        return $data;
    }


    /**
     * @param Artisan|null $artisan
     * @return bool|null
     */
    public function generateCardCnmci(?Artisan $artisan): ?Artisan
    {
        if(!$artisan) return false;

        $cardData = $this->mapToCnmciCardFrontViewModel($artisan);
        $cardImage = $this->imageGenerator->generate($cardData);
        $artisan->setCnmciCardFrontImage($cardImage->getFilename());

        $cardData = $this->mapToCnmciCardBackViewModel($artisan);
        $cardImage = $this->imageGenerator->generate($cardData);
        $artisan->setCnmciCardBackImage($cardImage->getFilename());
        $artisan->setModifiedAt(new \DateTime());
        return $artisan;
    }

}
