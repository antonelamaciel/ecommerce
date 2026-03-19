<?php

namespace App\Repository;

use App\Entity\CashMovement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CashMovement>
 *
 * @method CashMovement|null find($id, $lockMode = null, $lockVersion = null)
 * @method CashMovement|null findOneBy(array $criteria, array $orderBy = null)
 * @method CashMovement[]    findAll()
 * @method CashMovement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CashMovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CashMovement::class);
    }

    public function add(CashMovement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CashMovement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CashMovement[] Returns an array of CashMovement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CashMovement
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getTotalBalance(): float
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('SUM(CASE WHEN c.type = :ingress THEN c.amount ELSE -c.amount END)')
            ->setParameter('ingress', CashMovement::TYPE_INGRESS);

        return (float) $qb->getQuery()->getSingleScalarResult();
    }
}
