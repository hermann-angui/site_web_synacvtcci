<?php

namespace App\Service;

use App\Entity\Member;
use App\Helper\MemberHelper;
use Knp\Snappy\Pdf;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RecuGeneratorService
{
    /**
     * @param MemberHelper $memberHelper;
     */
    protected MemberHelper $memberHelper;

    /**
     * @var $pdfGenerator
     */
    protected Pdf $pdfGenerator;

    public function __construct(ContainerInterface $container, MemberHelper $memberHelper, Pdf $pdfGenerator)
    {
        $this->container = $container;
        $this->memberHelper = $memberHelper;
        $this->pdfGenerator = $pdfGenerator;
    }

    public function generate(?Member $member)
    {
        $binDir = $this->container->get('kernel')->getProjectDir();
        $userData['twig_view'] = "frontend/member/receipt.html.twig";
        $userData['member'] = $member;
       // return $this->pdfGenerator->generate($userData);
    }


}
