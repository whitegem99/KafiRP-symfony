<?php

namespace App\Repository;

use App\Entity\TextQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TextQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method TextQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method TextQuestion[]    findAll()
 * @method TextQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TextQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TextQuestion::class);
    }

    // /**
    //  * @return TextQuestion[] Returns an array of TextQuestion objects
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
    public function findOneBySomeField($value): ?TextQuestion
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
