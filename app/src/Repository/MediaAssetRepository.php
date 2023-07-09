<?php

namespace App\Repository;

use App\Entity\MediaAsset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MediaAsset>
 *
 * @method MediaAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaAsset[]    findAll()
 * @method MediaAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaAssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaAsset::class);
    }

    public function add(MediaAsset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MediaAsset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
