<?php

namespace App\Repository;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    public function findByAfterArchiveHistory(string $date, array $criteria = [])
    {
        $query = $this->createQueryBuilder('a')
            ->andWhere('a.createdAt > :date')
            ->andWhere('a.isDeleted = :isDeleted')
            ->setParameter('date', $date)
            ->setParameter('isDeleted', false);

        if (count($criteria)) {
            $i=0;
            foreach ($criteria as $key => $value) {
                $valKey = 'val'.$i;
                $query->andWhere('a.' . $key  . ' = :' . $valKey);
                $query->setParameter($valKey, $value);
                $i++;
            }
        }

        return $query
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByBeforeArchiveHistory(string $date)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.createdAt <= :date')
            ->andWhere('a.isDeleted = :isDeleted')
            ->setParameter('date', $date)
            ->setParameter('isDeleted', false)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Application[] Returns an array of Application objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Application
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
