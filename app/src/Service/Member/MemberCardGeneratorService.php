<?php

namespace App\Service\Member;

use App\DTO\MemberRequestDto;
use App\Helper\ImageGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param MemberRequestDto|null $memberDto
     * @return array|null
     */
    public function mapToCardViewModel(?MemberRequestDto $memberDto): ?array
    {
        $data['fullname'] = $memberDto->getLastName() . " " . $memberDto->getFirstName();
        $data['titre'] = $memberDto->getTitre();
        $data['matricule'] = $memberDto->getMatricule();
        $data['outputdir'] = "/var/www/html/public/members/" . $memberDto->getMatricule() . "/";
        if(!file_exists($data['outputdir'])) mkdir($data['outputdir'], 0777, true);
        $data['cardbg'] = "/var/www/html/public/assets/files/card_member_front.jpg";
        $data['photopath'] = $data['outputdir'] . $memberDto->getPhoto();
        $data['qrcodepath'] = $data['outputdir'] . $memberDto->getMatricule() . '_barcode.png' ;
        $data['cardpath'] = $data['outputdir'] . $memberDto->getMatricule() . '_card.png' ;
        $data['qrcodeurl'] = $this->container->getParameter('profile_url')  . "/" . $memberDto->getMatricule();
        $data['expiredate'] = "Expire le " . $memberDto->getSubscriptionExpireDate()->format('d/m/Y');
        $data['website'] = "www.synacvtcci.org";

        return $data;
    }

    /**
     * @param MemberRequestDto|null $memberDto
     * @return string|null
     */
    public function generate(?MemberRequestDto $memberDto): ?File
    {
        if(!$memberDto) return null;
        $cardData = $this->mapToCardViewModel($memberDto);
        $cardData['qrcodepath'] = $this->imageGenerator->generateBarCode($cardData['qrcodeurl'], $cardData['qrcodepath'], 50, 50);
        $userData['view_data'] = $cardData;
        $userData['twig_view'] = "admin/print/card.html.twig";
        return $this->imageGenerator->generate($userData);
    }


}
