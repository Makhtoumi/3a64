<?php
namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateOrderStatusCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:update-order-status')
            ->setDescription('Met à jour les statuts des commandes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $thresholdDate = new \DateTime('-7 days');
        
        $this->em->createQueryBuilder()
            ->update('App\Entity\Commande', 'c')
            ->set('c.statut', ':newStatus')
            ->where('c.date < :threshold AND c.statut = :pending')
            ->setParameters([
                'newStatus' => 'expiré',
                'threshold' => $thresholdDate,
                'pending' => 'en attente'
            ])
            ->getQuery()
            ->execute();

        return Command::SUCCESS;
    }
}