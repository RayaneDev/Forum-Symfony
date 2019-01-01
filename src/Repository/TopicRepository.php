<?php

namespace App\Repository;

use App\Entity\Topic;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Topic|null find($id, $lockMode = null, $lockVersion = null)
 * @method Topic|null findOneBy(array $criteria, array $orderBy = null)
 * @method Topic[]    findAll()
 * @method Topic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TopicRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Topic::class);
    }


    public function findByAuthor(string $author, Category $category)
    {
        return $this->createQueryBuilder('t')
                    ->join('t.user', 'u')
                        ->addSelect('u')
                        ->andWhere('u.pseudo = :pseudo')
                        ->setParameter('pseudo', $author)
                        ->andWhere('t.category = :category')
                        ->setParameter('category', $category)
                        ->orderBy('t.id', 'DESC')
                        ->getQuery()
                        ->getResult()
        ; 
    }

    public function findBySubject(string $subject, Category $category)
    {
        return $this->createQueryBuilder('t')
                    ->andWhere('t.title = :subject')
                    ->setParameter('subject', $subject)
                    ->andWhere('t.category = :category')
                    ->setParameter('category', $category)
                    ->orderBy('t.id', 'DESC')
                    ->getQuery()
                    ->getResult()
        ; 
    }

    public function findByMessage(string $message, Category $category) 
    {
        return $this->createQueryBuilder('t')
                    ->andWhere('t.category = :category')
                    ->setParameter('category', $category)
                        ->join('t.posts', 'p')
                        ->getQuery()
                        ->getResult();

    }

    // /**
    //  * @return Topic[] Returns an array of Topic objects
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
    public function findOneBySomeField($value): ?Topic
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
