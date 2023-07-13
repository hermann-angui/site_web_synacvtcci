<?php

namespace App\Repository;

use App\Entity\Artisan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artisan>
 *
 * @method Artisan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Artisan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Artisan[]    findAll()
 * @method Artisan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artisan::class);
    }

    public function add(Artisan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Artisan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Artisan[] Returns an array of Artisan objects
     */
    public function findMembresChauffeur(): ?array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.titre in (:val)')
            ->setParameter('val', "Membre,Chauffeur")
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Artisan[] Returns an array of Artisan objects
     */
    public function findMembresBureau(): ?array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.titre not in (:val)')
            ->setParameter('val', "Membre,Chauffeur")
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getLastRowId(): ?int
    {
        return $this->createQueryBuilder('m')
            ->select('MAX(m.id)')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)
            ;
    }
    public function setAutoIncrementToLast(int $value): ?int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "ALTER TABLE `artisan` AUTO_INCREMENT = $value";
        $stmt = $conn->prepare($sql);
        return $stmt->executeQuery()->rowCount();

    }

//    public function findOneBySomeField($value): ?Artisan
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
