<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;

class StatsGenerator
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function getFilteredStats(array $filters): array
    {
        return [
            'appointments' => [
                'by_doctor' => $this->em->getRepository(Appointment::class)
                    ->getFilteredAppointmentStats($filters) ?: [],
                'total' => $this->em->getRepository(Appointment::class)
                    ->countByFilters($filters) ?: 0
            ],
            'orders' => [
                'status_distribution' => $this->em->getRepository(Commande::class)
                    ->getStatusDistribution($filters) ?: [],
                'total_sales' => $this->em->getRepository(Commande::class)
                    ->getTotalSalesByFilters($filters) ?: 0
            ]
        ];
    }
    public function getGlobalStats(): array
    {
        return [
            'appointments' => [
                'total' => $this->em->getRepository(Appointment::class)->count([]),
                'by_doctor' => $this->em->getRepository(Appointment::class)->countByDoctor(),
                // SUPPRIMER 'cancel_rate'
            ],
            'orders' => [
                'total_sales' => $this->em->getRepository(Commande::class)->getTotalSales(),
                'status_distribution' => $this->em->getRepository(Commande::class)->getStatusDistribution(),
                'avg_order_value' => $this->em->getRepository(Commande::class)->getAverageOrderValue()
            ]
        ];
    }

    public function autoUpdateCommandStatus(): int
    {
        return $this->em->getRepository(Commande::class)
            ->autoUpdateStatusBasedOnTime();
    }
}
