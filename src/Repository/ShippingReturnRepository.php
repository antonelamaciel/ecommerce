<?php

namespace App\Repository;

use App\Entity\ShippingReturn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShippingReturn|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingReturn|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingReturn[]    findAll()
 * @method ShippingReturn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingReturnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingReturn::class);
    }
}
