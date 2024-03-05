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

//    public function findOneBySomeField($value): ?Member
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
