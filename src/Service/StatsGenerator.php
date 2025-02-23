<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AnalysisIndicator;
use App\Repository\CommandeRepository;
use App\Repository\AppointmentRepository;
use MathPHP\NumericalAnalysis\Calculus\ExpressionParser;
use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;

class StatsGenerator
{
    private $commandeRepo;
    private $appointmentRepo;
    
    public function __construct(
        EntityManagerInterface $em,
        CommandeRepository $commandeRepo,
        AppointmentRepository $appointmentRepo
    ) {
        $this->em = $em;
        $this->commandeRepo = $commandeRepo;
        $this->appointmentRepo = $appointmentRepo;
    }

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
        $indicators = $this->em->getRepository(AnalysisIndicator::class)->findBy(['isActive' => true]);
        return [
            'appointments' => [
                'total' => $this->em->getRepository(Appointment::class)->count([]),
                'by_doctor' => $this->em->getRepository(Appointment::class)->countByDoctor(),
                // SUPPRIMER 'cancel_rate'
            ],
            'orders' => [
                'total_sales' => $this->em->getRepository(Commande::class)->getTotalSales(),
                'status_distribution' => $this->em->getRepository(Commande::class)->getStatusDistribution(),
                'avg_order_value' => $this->em->getRepository(Commande::class)->getAverageOrderValue(),
                'daily' => $this->em->getRepository(Commande::class)->getDailyStats()

            ], 
            'indicators' => $this->getIndicatorValues($indicators)

        ];
    }

    public function autoUpdateCommandStatus(): int
    {
        return $this->em->getRepository(Commande::class)
            ->autoUpdateStatusBasedOnTime();
    }


    public function getIndicatorValues(array $indicators): array
    {
        $values = [];
        $baseMetrics = $this->getBaseMetrics();
    
        foreach ($indicators as $indicator) {
            try {
                $value = $this->calculateFormula(
                    $indicator->getCalculationFormula(),
                    $baseMetrics
                );
                
                $values[] = [
                    'id' => $indicator->getId(),
                    'name' => $indicator->getName(),          // Add this
                    'description' => $indicator->getDescription(),  // Add this
                    'value' => $value,
                    'status' => $this->determineStatus($value, $indicator->getThresholds())
                ];
            } catch (\Exception $e) {
                $values[] = [
                    'id' => $indicator->getId(),
                    'name' => $indicator->getName(),          // Add this
                    'description' => $indicator->getDescription(),  // Add this
                    'value' => null,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $values;
    }
    public function getAvailableMetrics(): array
    {
        return array_keys($this->getBaseMetrics());
    }


    private function getBaseMetrics(): array
    {
        return [
            'total_orders' => $this->commandeRepo->count([]),
            'total_appointments' => $this->appointmentRepo->count([]),
            'average_order_value' => $this->commandeRepo->getAverageOrderValue(),
            // Add more metrics as needed
        ];
    }
private function calculateFormula(string $formula, array $metrics): float
{
    // Replace metric names with their values
    $expression = preg_replace_callback('/\b(\w+)\b/', function($matches) use ($metrics) {
        return $metrics[$matches[1]] ?? $matches[0];
    }, $formula);

    // Use safer calculation method
    return $this->safeEval($expression);
}

private function safeEval(string $expression): float
{
    $parser = new StdMathParser();
    $AST = $parser->parse($expression);
    $evaluator = new Evaluator();
    
    return $AST->accept($evaluator);
}

private function determineStatus(float $value, array $thresholds): string
{
    $status = 'secondary';
    foreach ($thresholds as $threshold) {
        if ($this->compare($value, $threshold['operator'], $threshold['value'])) {
            $status = $threshold['level'];
            break;
        }
    }
    return $status;
}

private function compare(float $value, string $operator, float $threshold): bool
{
    return match ($operator) {
        '>' => $value > $threshold,
        '<' => $value < $threshold,
        '>=' => $value >= $threshold,
        '<=' => $value <= $threshold,
        '==' => $value == $threshold,
        default => false,
    };
}
}
