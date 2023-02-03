<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    /**
     * @return Member[] Returns an array of Member objects
     */
    public function findMembresChauffeur(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.titre = :val')
            ->setParameter('val', "Chauffeur")
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Member[] Returns an array of Member objects
     */
    public function findMembresBureau(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.titre not like :val')
            ->setParameter('val', "%Chauffeur")
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
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
