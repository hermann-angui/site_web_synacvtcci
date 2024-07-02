<?php

namespace App\Repository;

use App\Entity\Child;
use App\Entity\Crm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Crm>
 *
 * @method Crm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Crm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Crm[]    findAll()
 * @method Crm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Crm::class);
    }

    public function add(Crm $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Crm $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
