<?php

namespace App\Repository;

use App\Entity\Artisan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr;
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

    public function getLastRowId(): ?int
    {
        return $this->createQueryBuilder('m')
            ->select('MAX(m.id)')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    public function getTotalArtisans(): ?int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    public function getLastest(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('m')
            ->orderBy('m.subscription_date', 'DESC')
            ->setMaxResults(12)
            ->getQuery()
            ->getResult();
    }

    public function setAutoIncrementToLast(int $value): ?int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "ALTER TABLE `artisan` AUTO_INCREMENT = $value";
        $stmt = $conn->prepare($sql);
        return $stmt->executeQuery()->rowCount();

    }

    public function findAdherentsFromTo($date_start, $date_end): ?array
    {
        $data = $this->createQueryBuilder('m')
            ->where('m.subscription_date BETWEEN :from AND :to')
            // ->andWhere('m.subscription_date >= :from')
            // ->andWhere('m.subscription_date <= :to')
            ->setParameter('from', $date_start)
            ->setParameter('to', $date_end)
            ->getQuery()
            ->getResult();

        return $data;
    }

    public function getTotalGroupBySex(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.sex) AS total, m.sex AS sex')
            ->groupBy('m.sex')
            ->where('m.sex IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function getTotalGroupByActivity(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.activity) AS total, m.activity AS activity')
            ->groupBy('m.activity')
            ->where('m.activity IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function getTotalGroupByActivityAndMonth(): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT COUNT(`m`.`activity`) AS `total`,  `m`.`activity` , MONTH( `m`.`subscription_date`) AS `month_number` FROM `artisan` AS `m`  WHERE `m`.`activity` IS NOT NULL GROUP BY MONTH(`m`.`subscription_date`), `m`.`activity`;";
        $stmt = $conn->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function getTotalGroupByNationality(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.nationality) AS total, m.nationality AS nationality')
            ->groupBy('m.nationality')
            ->where('m.nationality IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function getTotalGroupByCommune(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.nationality) AS total, m.commune AS commune')
            ->groupBy('m.commune')
            ->where('m.commune IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function validatePaymentByIds(array $ids)
    {
        $params = implode(',', $ids);
        $res =  $this->createQueryBuilder('m')
            ->update()
            ->set('m.is_payment_validated', 1)
            ->set('m.status', "'PAIEMENT_VALIDER'")
            ->where("m.id IN ($params)")
            ->getQuery()
            ->getScalarResult();
        return $res;
    }

    public function validateEnrolementByIds(array $ids)
    {
        $params = implode(',', $ids);
        $res =  $this->createQueryBuilder('m')
            ->update()
            ->set('m.is_inscription_validated', 1)
            ->set('m.status', "'ENROLEMENT_VALIDER'")
            ->where("m.id IN ($params)")
            ->andWhere('m.is_payment_validated = 1')
            ->getQuery()
            ->getScalarResult();
        return $res;
    }
}
