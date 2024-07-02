<?php

namespace App\Repository;

use App\Entity\Contravention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contravention>
 *
 * @method Contravention|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contravention|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contravention[]    findAll()
 * @method Contravention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContraventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contravention::class);
    }

    public function add(Contravention $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Contravention $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
