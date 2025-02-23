<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 *
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function autoUpdateStatusBasedOnTime(): int
    {
    $conn = $this->getEntityManager()->getConnection();
    $sql = "UPDATE commande 
            SET statut = 'expired' 
            WHERE date < NOW() - INTERVAL '7 DAY' 
            AND statut = 'pending'";
    
    return $conn->executeStatement($sql);
    }

    public function findByFilters(array $filters): array
{
    $qb = $this->createQueryBuilder('c'); // 'c' is the alias for Commande
    
    // Apply filters if they exist
    if (isset($filters['start_date']) && $filters['start_date']) {
        $qb->andWhere('c.date >= :start')
           ->setParameter('start', $filters['start_date']);
    }

    if (isset($filters['end_date']) && $filters['end_date']) {
        $qb->andWhere('c.date <= :end')
           ->setParameter('end', $filters['end_date']);
    }

    if (isset($filters['status']) && $filters['status']) {
        $qb->andWhere('c.statut = :status')
           ->setParameter('status', $filters['status']);
    }

    return $qb->getQuery()->getResult();
}
public function getTotalSales(): float
{
    $result = $this->createQueryBuilder('c')
        ->select('SUM(c.totale)')
        ->getQuery()
        ->getSingleScalarResult();

    // If the result is null, return 0.0 as a default value
    return (float) $result ?: 0.0;
}


public function getStatusDistribution(array $filters = []): array // Ajouter une valeur par défaut
{
    $qb = $this->createQueryBuilder('c')
        ->select('c.statut, COUNT(c.id) as count')
        ->groupBy('c.statut');

    // Ajouter la gestion des filtres
    if (!empty($filters['start_date'])) {
        $qb->andWhere('c.date >= :start')
           ->setParameter('start', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
        $qb->andWhere('c.date <= :end')
           ->setParameter('end', $filters['end_date']);
    }

    return $qb->getQuery()->getResult();
}
public function getAverageOrderValue(): float
{
    // Calculate the average of the 'totale' field in the Commande entity
    $result = $this->createQueryBuilder('c')
        ->select('AVG(c.totale)')
        ->getQuery()
        ->getSingleScalarResult();

    // If the result is null, return 0.0 as a default value
    return (float) $result ?: 0.0;
}
    public function save(Commande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function getFilteredStats(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('SUM(c.totale) as total_sales, COUNT(c.id) as total_orders');
    
        if (!empty($filters['start_date'])) {
            $qb->andWhere('c.date >= :start')
               ->setParameter('start', $filters['start_date']);
        }
    
        if (!empty($filters['end_date'])) {
            $qb->andWhere('c.date <= :end')
               ->setParameter('end', $filters['end_date']);
        }
    
        return $qb->getQuery()->getSingleResult();
    }




public function getTotalSalesByFilters(array $filters): float
{
    $qb = $this->createQueryBuilder('c')
        ->select('SUM(c.totale)');

    $this->applyDateFilters($qb, $filters, 'date');
    
    return $qb->getQuery()->getSingleScalarResult() ?? 0;
}

private function applyDateFilters($qb, array $filters, string $fieldName): void
{
    if (!empty($filters['start_date'])) {
        $qb->andWhere("c.$fieldName >= :start")
           ->setParameter('start', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
        $qb->andWhere("c.$fieldName <= :end")
           ->setParameter('end', $filters['end_date']);
    }
}
//    /**
//     * @return Commande[] Returns an array of Commande objects
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

//    public function findOneBySomeField($value): ?Commande
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
