<?php

namespace App\Service\Artisan;;
use App\Entity\Artisan;
use App\Helper\ArtisanAssetHelper;
use App\Helper\CsvReaderHelper;
use App\Repository\ChildRepository;
use App\Repository\ArtisanRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ArtisanService
{
    private ArtisanReceiptGeneratorService $artisanReceiptGeneratorService;
    private ArtisanRepository $artisanRepository;
    private ChildRepository $childRepository;

    private ContainerInterface $container;
    private CsvReaderHelper $csvReaderHelper;
    private ArtisanAssetHelper $artisanAssetHelper;

    public function __construct(
        ContainerInterface             $container,
        ArtisanReceiptGeneratorService $artisanReceiptGeneratorService,
        ArtisanRepository               $artisanRepository,
        ChildRepository                $childRepository,
        UserPasswordHasherInterface    $userPasswordHasher,
        CsvReaderHelper                $csvReaderHelper,
        ArtisanAssetHelper             $artisanAssetHelper)
    {
        $this->artisanReceiptGeneratorService = $artisanReceiptGeneratorService;
        $this->artisanRepository = $artisanRepository;
        $this->childRepository = $childRepository;
        $this->container = $container;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->csvReaderHelper = $csvReaderHelper;
        $this->artisanAssetHelper = $artisanAssetHelper;
    }


    /**
     * @param Artisan $artisan
     * @return void
     */

    public function saveArtisanImages(Artisan $artisanRequestDto): Artisan
    {
        if ($artisanRequestDto->getPhoto()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhoto(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhoto($fileName);
        }

        if ($artisanRequestDto->getPhotoPieceFront()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhotoPieceFront(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhotoPieceFront($fileName);
        }

        if ($artisanRequestDto->getPhotoPieceBack()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhotoPieceBack(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhotoPieceBack($fileName);
        }

        if ($artisanRequestDto->getPhotoPieceBack()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhotoPieceBack(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhotoPermisFront($fileName);
        }

        if ($artisanRequestDto->getPhotoPermisBack()) {
            $fileName = $this->artisanAssetHelper->uploadAsset($artisanRequestDto->getPhotoPermisBack(), $artisanRequestDto->getMatricule());
            if ($fileName) $artisanRequestDto->setPhotoPermisBack($fileName);
        }

        return $artisanRequestDto;
    }


    /**
     * @param $row
     * @param string $uploadDir
     * @param Artisan $artisan
     * @return void
     */
    public function storeAsset($row, string $uploadDir, Artisan $artisan): void
    {
        if (isset($row) && !empty($row)) {
            $photo = new File($uploadDir . $row, false);
            if (file_exists($photo->getPathname())) {
                $fileName = $this->artisanAssetHelper->uploadAsset($photo, $artisan->getMatricule());
                if ($fileName) $artisan->setPhoto($fileName);
            }
        }
    }


    public function store(Artisan $artisan){
        $this->artisanRepository->add($artisan);
    }

}
