<?php

namespace App\Repository;

use App\Entity\AnalysisIndicator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnalysisIndicator>
 *
 * @method AnalysisIndicator|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnalysisIndicator|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnalysisIndicator[]    findAll()
 * @method AnalysisIndicator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnalysisIndicatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnalysisIndicator::class);
    }

//    /**
//     * @return AnalysisIndicator[] Returns an array of AnalysisIndicator objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AnalysisIndicator
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
