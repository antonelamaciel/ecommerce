<?php

namespace App\Repository;

use App\Entity\Personalize;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Personalize>
 *
 * @method Personalize|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personalize|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personalize[]    findAll()
 * @method Personalize[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonalizeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personalize::class);
    }

//    /**
//     * @return Personalize[] Returns an array of Personalize objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Personalize
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
