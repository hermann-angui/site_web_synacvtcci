<?php

namespace App\Controller\Admin;

use App\Entity\Child;
use App\Entity\Member;
use App\Entity\Villes;
use App\Form\MemberPhotoStepType;
use App\Form\MemberRegistrationType;
use App\Helper\ActivityLogger;
use App\Helper\DataTableHelper;
use App\Helper\FileUploadHelper;
use App\Repository\MemberRepository;
use App\Repository\VillesRepository;
use App\Service\Member\MemberService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/stats')]
class StatsController extends AbstractController
{
    #[Route('', name: 'admin_stats_index', methods: ['GET'])]
    public function getSexStats(Request $request, MemberRepository $memberRepository): Response
    {
        $stats = $memberRepository->getTotalGroupBySex();
        $sex_stat = [
            ["value" => 0, "name" => "Homme"],
            ["value" => 0, "name" => "Femme"],
        ];
       for($i = 0 ; $i < 2; $i++){
           foreach ($stats as $stat){
               if($stat['sex'] === "H" && $sex_stat[$i]['name'] === 'Homme'){
                   $sex_stat[$i]["value"] = $stat['total'];
               }
               if($stat['sex'] === "F" && $sex_stat[$i]['name'] === 'Femme'){
                   $sex_stat[$i]["value"] = $stat['total'];
               }
           }
       }
        unset($stats);

        $stats = $memberRepository->getTotalGroupByActivity();
        $activity_stat = array_map(function ($v){
            return [
                "name" => $v['activity'],
                "value" => $v['total'],
            ];
        }, $stats);
        unset($stats);

        $stats = $memberRepository->getTotalGroupByNationality();
        $nationality_stat = array_map(function ($v){
            return [
                "name" => $v["nationality"],
                "value" => $v["total"]
            ];
        }, $stats);
        unset($stats);

        $months = ['Janv', 'Fev', 'Mars', 'Avri', 'Mai', 'Jun', 'Juil', 'Aout', 'Sep', 'Oct', 'Nov', 'Dec'];
        $values = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        $t["CHAUFFEUR VTC"] =  $values;
        $t["CHAUFFEUR TAXI"] = $values;
        $t["CHAUFFEUR LIVREUR"] = $values;

        $stats = $memberRepository->getTotalGroupByActivityAndMonth();

        foreach ($stats as $stat){
            $t[$stat['activity']][$stat['month_number'] - 1 ] = $stat['total'];
        }

        return $this->json([
            "sex" => ["data" => $sex_stat, "legend" => array_column($sex_stat, 'name')],
            "activity" => ["data" => $activity_stat, "legend" => array_column($activity_stat, 'name')],
            'nationality' => ["data" => $nationality_stat, "legend" => array_column($nationality_stat, 'name')],
            'months' => $months,
            'vtc' =>  $t['CHAUFFEUR VTC'],
            'taxi' => $t['CHAUFFEUR TAXI'],
            'livreur' => $t['CHAUFFEUR LIVREUR'],
        ]);
    }

}
