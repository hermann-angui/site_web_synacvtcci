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
    public function mapToSynacvtcciCardViewModel(?Member $member): ?array
    {
        $data['fullname'] = $member->getLastName() . " " . $member->getFirstName();
        $data['titre'] = $member->getActivity();
        $data['matricule'] = $member->getMatricule();
        $data['outputdir'] = "/var/www/html/public/members/" . $member->getReference() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);

        switch($member->getActivity()){
            case "CHAUFFEUR VTC":
                $data['cardbg'] = "/var/www/html/public/assets/files/carte_synacvtcci_front.jpg";
                $data['twig_view'] = "admin/print/carte_synacvtcci.html.twig";
                $data['website']    = "www.synacvtcci.org";
                $data['expiredate'] = "Expire le " . $member->getSubscriptionExpireDate()->format('d/m/Y');
                $data['qrcode_color'] = [14, 119, 12];
                break;
            case "CHAUFFEUR LIVREUR":
                $data['cardbg'] = "/var/www/html/public/assets/files/carte_falci_front.jpg";
                $data['twig_view'] = "admin/print/carte_falci.html.twig";
                $data['expiredate'] = "Expire le " . $member->getSubscriptionExpireDate()->format('d/m/Y');
                $data['qrcode_color'] = [0, 0, 0];
                break;
            case "CHAUFFEUR TAXI":
                $data['cardbg'] = "/var/www/html/public/assets/files/carte_taxi_front.jpg";
                $data['twig_view'] = "admin/print/carte_taxi.html.twig";
                $data['expiredate'] = "Expire le " . $member->getSubscriptionExpireDate()->format('d/m/Y');
                $data['qrcode_color'] = [14, 119, 12];
                break;
        }

        $data['photo_path']  =  $data['outputdir'] . $member->getPhoto();
        $data['qrcode_path'] = $data['outputdir'] . $member->getReference() . '_barcode.png' ;
        $data['card_path']   = $data['outputdir'] . $member->getReference() . '_card.png' ;
        $data['qrcode_url']  = $this->configurationService->getParameter('app.base_url')  . "profile/" . $member->getReference();

        return $data;
    }

    /**
     * @param Member|null $member
     * @return array|null
     */
    public function mapToCnmciCardViewModel(?Member $member): ?array
    {
        $data['last_name'] = $member->getLastName();
        $data['first_name'] =  $member->getFirstName();
        $data['metier'] = $member->getActivity();
        $data['birth_date'] = $member->getDateOfBirth()->format('d/m/y');
        $data['birth_place'] = $member->getBirthCity();

        $data['numero_rm'] = $member->getCnmciNumeroRm();
        $data['numero_carte_professionnelle'] = $member->getCnmciNumeroCarteProfessionelle();
        $data['twig_view'] = "admin/print/carte_cnmci.html.twig";
        $data['outputdir'] = "/var/www/html/public/members/" . $member->getReference() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);

        $data['photo_path']  =  $data['outputdir'] . $member->getPhoto();
        $data['card_path']   = $data['outputdir'] . $member->getReference() . '_card_cnmci.png' ;

        return $data;
    }



    /**
     * @param Member|null $member
     * @return string|null
     */
    public function generateCardSynacvtcci(?Member $member): ?File
    {
        if(!$member) return null;
        $cardData = $this->mapToSynacvtcciCardViewModel($member);
        $cardData['qrcode_path'] = $this->imageGenerator->generateBarCode($cardData['qrcode_url'], $cardData['qrcode_path'], $cardData['qrcode_color'],50, 50);
        return $this->imageGenerator->generate($cardData);
    }

    /**
     * @param Member|null $member
     * @return string|null
     */
    public function generateCardCnmci(?Member $member): ?File
    {
        if(!$member) return null;
        $cardData = $this->mapToCnmciCardViewModel($member);
        return $this->imageGenerator->generate($cardData);
    }

}
