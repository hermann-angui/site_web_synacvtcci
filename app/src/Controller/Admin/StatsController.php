<?php

namespace App\Controller\Admin;

use App\Entity\Child;
use App\Entity\Artisan;
use App\Entity\Villes;
use App\Form\ArtisanPhotoStepType;
use App\Form\ArtisanRegistrationType;
use App\Helper\ActivityLogger;
use App\Helper\DataTableHelper;
use App\Helper\FileUploadHelper;
use App\Repository\ArtisanRepository;
use App\Repository\VillesRepository;
use App\Service\Artisan\ArtisanService;
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

#[Route('/stats')]
class StatsController extends AbstractController
{
    #[Route('', name: 'admin_stats_index', methods: ['GET'])]
    public function getSexStats(Request $request, ArtisanRepository $artisanRepository): Response
    {
        $stats = $artisanRepository->getTotalGroupBySex();

        foreach($stats as $stat) {
            if(in_array($stat['sex'], ['H', 'Homme'])) $key = "Homme (${stat['total']})";
            if(in_array($stat['sex'], ['F', 'Femme'])) $key = "Femme (${stat['total']})";
            $sex_stat[] = [
                "name" => $key,
                "value" => $stat['total'],
            ];
        }

        unset($stats);

        $stats = $artisanRepository->getTotalGroupByActivity();
        $activity_stat = array_map(function ($v){
            return [
                "name" =>  "${v['activity']} (${v['total']})",
                "value" => $v['total'],
            ];
        }, $stats);
        unset($stats);

        $stats = $artisanRepository->getTotalGroupByNationality();
        $nationality_stat = array_map(function ($v){
            return [
                "name" => "${v["nationality"]} (${v["total"]})",
                "value" => $v["total"]
            ];
        }, $stats);
        unset($stats);

        $months = ['Janv', 'Fev', 'Mars', 'Avri', 'Mai', 'Jun', 'Juil', 'Aout', 'Sep', 'Oct', 'Nov', 'Dec'];
        $values = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        $stats = $artisanRepository->getTotalGroupByActivityAndMonth();
        $conducteurs = [];
        foreach ($stats as $stat){
            if(!array_key_exists($stat['activity'], $conducteurs)) $conducteurs[$stat['activity']] = $values;
            $conducteurs[$stat['activity']][$stat['month_number'] - 1 ] = $stat['total'];
        }

        $stats_conducteurs = [];
        foreach($conducteurs as $k => $v){
            $sum = array_sum($v);
            $stats_conducteurs[] =  [
                'name' => substr($k, strlen("CONDUCTEUR ")) . " ($sum)",
                'type' =>  'bar',
                'label' => [
                    'show' => 'true',
                    'position' => 'inside'
               ],
                'emphasis'=> [
                    'focus' => 'series'
                ],
                'data' => $v
            ];
        }

        $payload = [
            "sex" => [
                "data" => $sex_stat,
                "legend" => array_column($sex_stat, 'name')
            ],
            "activity" => [
                "data" => $activity_stat,
                "legend" => array_column($activity_stat, 'name')
            ],
            'nationality' => [
                "data" => $nationality_stat,
                "legend" => array_column($nationality_stat, 'name')
            ],
            'months' => $months,
            "conducteurs" => $stats_conducteurs
        ];

        return $this->json($payload);
    }

}
