<?php

namespace App\Repository;

use App\Entity\ChoiceQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChoiceQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChoiceQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChoiceQuestion[]    findAll()
 * @method ChoiceQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChoiceQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChoiceQuestion::class);
    }

    // /**
    //  * @return ChoiceQuestion[] Returns an array of ChoiceQuestion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ChoiceQuestion
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
