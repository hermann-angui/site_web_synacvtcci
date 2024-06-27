<?php

namespace App\Service\Member;

use App\Entity\Member;
use App\Helper\ImageGenerator;
use App\Service\ConfigurationService\ConfigurationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class MemberCardGeneratorService
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
     * @param Member|null $member
     * @return array|null
     */
    public function mapToCnmciCardFrontViewModel(?Member $member): ?array
    {
        $data['last_name'] = $member->getLastName();
        $data['first_name'] =  $member->getFirstName();
        $data['metier'] = $member->getActivity();
        $data['birth_date'] = $member->getDateOfBirth()->format('d/m/y');
        $data['birth_place'] = $member->getBirthCity();
        $data['card_bg'] = "/var/www/html/public/assets/files/carte_cnmci_front.jpg";

        $data['numero_rm'] = $member->getCnmciNumeroRm();
        $data['numero_carte_professionnelle'] = $member->getCnmciNumeroCarteProfessionelle();
        $data['twig_view'] = "admin/card_tmpl/carte_cnmci_front.html.twig";
        $data['outputdir'] = "/var/www/html/public/members/" . $member->getReference() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);

        $data['photo_path']  =  $data['outputdir'] . $member->getPhoto();
        $data['card_path']   = $data['outputdir'] . $member->getReference() . '_card_cnmci_front.png' ;

        return $data;
    }


    /**
     * @param Member|null $member
     * @return array|null
     */
    public function mapToCnmciCardBackViewModel(?Member $member): ?array
    {
        $data['last_name'] = $member->getLastName();
        $data['first_name'] =  $member->getFirstName();
        $data['metier'] = $member->getActivity();
        $data['birth_date'] = $member->getDateOfBirth()->format('d/m/y');
        $data['birth_place'] = $member->getBirthCity();
        $data['card_bg'] = "/var/www/html/public/assets/files/carte_cnmci_back.jpg";

        $data['numero_rm'] = $member->getCnmciNumeroRm();
        $data['numero_carte_professionnelle'] = $member->getCnmciNumeroCarteProfessionelle();
        $data['twig_view'] = "admin/card_tmpl/carte_cnmci_back.html.twig";
        $data['outputdir'] = "/var/www/html/public/members/" . $member->getReference() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);

        $data['photo_path']  =  $data['outputdir'] . $member->getPhoto();
        $data['card_path']   = $data['outputdir'] . $member->getReference() . '_card_cnmci_back.png' ;

        return $data;
    }


    /**
     * @param Member|null $member
     * @return bool|null
     */
    public function generateCardCnmci(?Member $member): ?Member
    {
        if(!$member) return false;

        $cardData = $this->mapToCnmciCardFrontViewModel($member);
        $cardImage = $this->imageGenerator->generate($cardData);
        $member->setCnmciCardFrontImage($cardImage->getFilename());

        $cardData = $this->mapToCnmciCardBackViewModel($member);
        $cardImage = $this->imageGenerator->generate($cardData);
        $member->setCnmciCardBackImage($cardImage->getFilename());
        $member->setModifiedAt(new \DateTime());
        return $member;
    }

}
