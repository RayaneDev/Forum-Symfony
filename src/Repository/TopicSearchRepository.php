<?php

namespace App\Repository;

use App\Entity\TopicSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TopicSearch|null find($id, $lockMode = null, $lockVersion = null)
 * @method TopicSearch|null findOneBy(array $criteria, array $orderBy = null)
 * @method TopicSearch[]    findAll()
 * @method TopicSearch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TopicSearchRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TopicSearch::class);
    }

    // /**
    //  * @return TopicSearch[] Returns an array of TopicSearch objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TopicSearch
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
