<?php

namespace App\Service;

use App\Entity\Member;
use App\Helper\ImageGenerator;
use App\Helper\MemberHelper;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MemberCardGeneratorService
{
    /**
     * @param MemberHelper $memberHelper;
     */
    protected MemberHelper $memberHelper;

    /**
     * @var ImageGenerator
     */
    protected ImageGenerator $imageGenerator;

    public function __construct(ContainerInterface $container, MemberHelper $memberHelper, ImageGenerator $imageGenerator)
    {
        $this->container = $container;
        $this->memberHelper = $memberHelper;
        $this->imageGenerator = $imageGenerator;
    }

    public function mapToCardViewModel(?Member $member): ?array
    {
        $data['fullname'] = $member->getLastName() . " " . $member->getFirstName();
        $data['titre'] = $member->getTitre();
        $data['matricule'] = $member->getMatricule();
        $data['outputdir'] = "/var/www/html/public/members/" . $member->getMatricule() . "/";
        $data['cardbg'] = "/var/www/html/public/assets/files/card_member_front.jpg";
        $data['photopath'] = $data['outputdir'] . $member->getPhoto();
        $data['qrcodepath'] = $data['outputdir'] . $member->getMatricule() . '_barcode.png' ;
        $data['cardpath'] = $data['outputdir'] . $member->getMatricule() . '_card.png' ;
        $data['qrcodeurl'] = $this->container->getParameter('profile_url')  . "/" . $member->getMatricule();
        $data['expiredate'] = "Expire le " . $member->getSubscriptionExpireDate()->format('d/m/Y');
        $data['website'] = "www.synacvtcci.org";

        return $data;
    }

    public function generate(?Member $member)
    {
        if(!$member) return null;
        $cardData = $this->mapToCardViewModel($member);
        $cardData['qrcodepath'] = $this->imageGenerator
                            ->generateBarCode(
                                $cardData['qrcodeurl'],
                                $cardData['qrcodepath'],
                                50,
                                50
                            );
        $userData['view_data'] = $cardData;
        $userData['twig_view'] = "admin/print/card.html.twig";
        return $this->imageGenerator->generate($userData);
    }


}