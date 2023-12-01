<?php

namespace App\Repository;

use App\Entity\Communes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Communes>
 *
 * @method Communes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Communes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Communes[]    findAll()
 * @method Communes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommunesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Communes::class);
    }

    public function add(Communes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Communes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Communes[] Returns an array of Communes objects
     */
    public function findAllNames(): array
    {
        $result =  $this->createQueryBuilder('c')
            ->select('c.name')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
        $d = array_column($result, "name");
        return array_combine($d, $d);
    }

//    public function findOneBySomeField($value): ?Communes
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
