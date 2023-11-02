<?php

namespace App\Service\Member;

use App\Entity\Member;
use App\Helper\ImageGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 *
 */
class MemberCardGeneratorService
{
    /**
     * @var ImageGenerator
     */
    private ImageGenerator $imageGenerator;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     * @param ImageGenerator $imageGenerator
     */
    public function __construct(ContainerInterface $container, ImageGenerator $imageGenerator)
    {
        $this->container = $container;
        $this->imageGenerator = $imageGenerator;
    }

    /**
     * @param Member|null $member
     * @return array|null
     */
    public function mapToCardViewModel(?Member $member): ?array
    {
        $data['fullname'] = $member->getLastName() . " " . $member->getFirstName();
        $data['titre'] = $member->getTitre();
        $data['matricule'] = $member->getMatricule();
        $data['outputdir'] = "/var/www/html/public/members/" . $member->getReference() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);
        $data['cardbg'] = "/var/www/html/public/assets/files/card_member_front.jpg";
        $data['photopath'] =  $data['outputdir']. $member->getPhoto();
        $data['qrcodepath'] = $data['outputdir'] . $member->getReference() . '_barcode.png' ;
        $data['cardpath'] = $data['outputdir'] . $member->getReference() . '_card.png' ;
        $data['qrcodeurl'] = $this->container->getParameter('profile_url')  . "/" . $member->getReference();
        $data['expiredate'] = "Expire le " . $member->getSubscriptionExpireDate()->format('d/m/Y');
        $data['website'] = "www.synacvtcci.org";

        return $data;
    }

    /**
     * @param Member|null $member
     * @return string|null
     */
    public function generate(?Member $member): ?File
    {
        if(!$member) return null;
        $cardData = $this->mapToCardViewModel($member);
        $cardData['qrcodepath'] = $this->imageGenerator->generateBarCode($cardData['qrcodeurl'], $cardData['qrcodepath'], 50, 50);
        $userData['view_data'] = $cardData;
        $userData['twig_view'] = "admin/print/card.html.twig";
        return $this->imageGenerator->generate($userData);
    }


}
