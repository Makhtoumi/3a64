<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('a');
    
        if (isset($filters['start_date']) && $filters['start_date']) {
            $qb->andWhere('a.appointmentDate >= :start')
               ->setParameter('start', $filters['start_date']);
        }
    
        if (isset($filters['end_date']) && $filters['end_date']) {
            $qb->andWhere('a.appointmentDate <= :end')
               ->setParameter('end', $filters['end_date']);
        }
    

    
        return $qb->getQuery()->getResult();
    }
    
    public function countByDoctor(): array
    {
        return $this->createQueryBuilder('a')
            ->select('d.name, COUNT(a.id) as total')
            ->leftJoin('a.doctor', 'd')
            ->groupBy('d.id')
            ->getQuery()
            ->getResult();
    }

public function countUniquePatients(): int
{
    return $this->createQueryBuilder('a')
        ->select('COUNT(DISTINCT a.clientName)')
        ->getQuery()
        ->getSingleScalarResult();
}

    
public function getFilteredStats(array $filters): array
{
    $qb = $this->createQueryBuilder('a')
        ->select('COUNT(a.id) as total, d.name as doctor_name')
        ->leftJoin('a.doctor', 'd')
        ->groupBy('d.id');

    if (!empty($filters['start_date'])) {
        $qb->andWhere('a.appointmentDate >= :start')
           ->setParameter('start', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
        $qb->andWhere('a.appointmentDate <= :end')
           ->setParameter('end', $filters['end_date']);
    }

    return $qb->getQuery()->getResult();
}
public function getFilteredAppointmentStats(array $filters): array
{
    $qb = $this->createQueryBuilder('a')
        ->select('d.name, COUNT(a.id) as total')
        ->leftJoin('a.doctor', 'd')
        ->groupBy('d.id');

    $this->applyDateFilters($qb, $filters, 'appointmentDate');
    
    return $qb->getQuery()->getResult();
}
public function countByFilters(array $filters): int
{
    $qb = $this->createQueryBuilder('a')
        ->select('COUNT(a.id)');

    $this->applyDateFilters($qb, $filters, 'appointmentDate');
    
    return $qb->getQuery()->getSingleScalarResult();
}

private function applyDateFilters($qb, array $filters, string $fieldName): void
{
    if (!empty($filters['start_date'])) {
        $qb->andWhere("a.$fieldName >= :start")
           ->setParameter('start', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
        $qb->andWhere("a.$fieldName <= :end")
           ->setParameter('end', $filters['end_date']);
    }
}

public function deleteOlderThan(\DateTimeInterface $threshold): int
{
    return $this->createQueryBuilder('a')
        ->delete()
        ->where('a.appointmentDate < :threshold')
        ->setParameter('threshold', $threshold)
        ->getQuery()
        ->execute();
}


public function findUpcomingAppointments(): array
{
    $now = new \DateTime();
    $end = (clone $now)->modify('+24 hours');

    return $this->createQueryBuilder('a')
        ->andWhere('a.appointmentDate BETWEEN :now AND :end')
        ->setParameter('now', $now)
        ->setParameter('end', $end)
        ->getQuery()
        ->getResult();
}
    //    /**
    //     * @return Appointment[] Returns an array of Appointment objects
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

    //    public function findOneBySomeField($value): ?Appointment
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
