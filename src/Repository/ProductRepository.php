<?php

namespace App\Repository;

use App\Entity\Product;
use App\Model\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**Requête custom pour la barre de recherche :
     * Récupérations des produits filtrés par mots-clés et/ou catégories
     * 
     * @return Product[] Returns an array of Product objects
     */
    public function findWithSearch(Search $search): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p', 'c', 'b')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.bundles', 'b')
            ->orderBy('p.id', 'DESC')
        ;
        
        if (!empty($search->getCategories())) {
            $query = $query
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->getCategories())
            ;    
        }

        if (!empty($search->getSubcategories())) {
            $query = $query
                ->leftJoin('p.subcategories', 's')
                ->addSelect('s')
                ->andWhere('s.id IN (:subcategories)')
                ->setParameter('subcategories', $search->getSubcategories())
            ;
        }

        if (!empty($search->getString())) {
            $query = $query
                ->andWhere('p.name LIKE :string OR p.subtitle LIKE :string')
                ->setParameter('string', "%{$search->getString()}%")
            ;    
        }

        return $query->getQuery()->getResult();
    }

}
