<?php

namespace App\Service\Member;

use App\DTO\MemberRequestDto;
use App\Helper\MemberAssetHelper;
use Knp\Snappy\Pdf;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MemberReceiptGeneratorService
{
    /**
     * @param MemberAssetHelper ;
     */
    protected MemberAssetHelper $memberAssetHelper;

    /**
     * @var Pdf
     */
    protected Pdf $pdfGenerator;

    public function __construct(ContainerInterface $container, MemberAssetHelper $memberAssetHelper, Pdf $pdfGenerator)
    {
        $this->container = $container;
        $this->memberAssetHelper = $memberAssetHelper;
        $this->pdfGenerator = $pdfGenerator;
    }

    public function generate(?MemberRequestDto $memberRequestDto)
    {
        $binDir = $this->container->get('kernel')->getProjectDir();
        $userData['twig_view'] = $binDir . "/templates/frontend/member/receipt.html.twig";
        $userData['member'] = $memberRequestDto;
        $this->pdfGenerator->generate($userData);
    }


}
