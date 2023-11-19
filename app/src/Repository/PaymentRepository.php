<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 *
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function add(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findTotalDaily() {
        $now = new \DateTime('now');
        $param = $now->format('Y-m-d');

        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(p)')
            ->where($qb->expr()->eq('DATE(p.created_at)', ':start', ':end'))
            ->setParameter('start', $param)
        ;
        return $qb->getQuery()->getSingleScalarResult();

    }

    public function findTotalWeekly() {
        $start = date("Y-m-d 00:00:00", strtotime('monday this week'));
        $end = date("Y-m-d 00:00:00", strtotime('sunday this week'));
        $qb = $this->createQueryBuilder('p');
            $qb->select('COUNT(p)')
            ->where($qb->expr()->between('p.created_at', ':start', ':end'))
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findTotalMonthly() {
        $start = date("Y-m-d 00:00:00", strtotime('first day of this month'));
        $end = date("Y-m-d 00:00:00", strtotime('first day of this month'));
        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(p)')
            ->where($qb->expr()->between('p.created_at', ':start', ':end'))
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findTotalYear() {
        $start = date("Y-m-d 00:00:00", strtotime('first day of january this year'));
        $end = date("Y-m-d 00:00:00", strtotime('last day of december this year'));
        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(p)')
            ->where($qb->expr()->between('p.created_at', ':start', ':end'))
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findTotalEachMonth(){
        $result = $this->createQueryBuilder('p')
            ->select('MONTH(p.created_at) as mois_num, count(p) AS total')
            ->groupBy('mois_num')
            ->getQuery();

       // $c = $result->getSQL();
        return $result->getResult();
    }

}
