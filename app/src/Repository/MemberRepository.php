<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 *
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function add(Member $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Member $entity, bool $flush = false): void
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
            ->getResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)
            ;
    }

    public function getTotalMembers(): ?int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)
            ;
    }

    public function getLastest(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('m')
            ->orderBy('m.subscription_date', 'DESC')
            ->setMaxResults(12)
            ->getQuery()
            ->getResult()
            ;
    }
    public function setAutoIncrementToLast(int $value): ?int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "ALTER TABLE `member` AUTO_INCREMENT = $value";
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
            ->getResult()
        ;

        return $data;
    }

    public function getLastNDays(int $days){
        $to = new \DateTime();
        $temp = new \DateTime();
        $from = $temp->modify("-$days days");
        return $this->createQueryBuilder('m')
           // ->where('m.subscription_date BETWEEN :from AND :to')
//             ->andWhere('m.subscription_date >= :from')
//             ->andWhere('m.subscription_date <= :to')
//             ->andWhere('m.etape >= 3')
//            ->setParameter('from', $from->format('Y-m-d H:i:s'))
//            ->setParameter('to',  $to->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult()
            ;

    }

    public function getTotalGroupBySex(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.sex) AS total, m.sex AS sex')
            ->groupBy('m.sex')
            ->where('m.sex IS NOT NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getTotalGroupByActivity(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.activity) AS total, m.activity AS activity')
            ->groupBy('m.activity')
            ->where('m.activity IS NOT NULL')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getTotalGroupByActivityAndMonth(): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT COUNT(`m`.`activity`) AS `total`,  `m`.`activity` , MONTH( `m`.`subscription_date`) AS `month_number` FROM `member` AS `m`  WHERE `m`.`activity` IS NOT NULL GROUP BY MONTH(`m`.`subscription_date`), `m`.`activity`;";
        $stmt = $conn->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();
//        return $this->createQueryBuilder('m')
//            ->select('COUNT(m.activity) AS total, m.activity AS activity, MONTH(m.subscription_date) AS month')
//            ->groupBy('m.activity, m.subscription_date')
//            ->where('m.activity IS NOT NULL')
//            ->getQuery()
//            ->getResult()
//            ;
    }

    public function getTotalGroupByNationality(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.nationality) AS total, m.nationality AS nationality')
            ->groupBy('m.nationality')
            ->where('m.nationality IS NOT NULL')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getTotalGroupByCommune(): ?array
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.nationality) AS total, m.commune AS commune')
            ->groupBy('m.commune')
            ->where('m.commune IS NOT NULL')
            ->getQuery()
            ->getResult()
            ;
    }
}
