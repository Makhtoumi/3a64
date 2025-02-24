<?php

namespace App\Controller;


use App\Entity\Commande;
use App\Entity\Appointment;
use App\Form\AnalysisFilterType;
use App\Service\StatsGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\AppointmentRepository;
use App\Entity\AnalysisIndicator ; 
use App\Form\AnalysisIndicatorType ;

class AnalyseDonnesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private StatsGenerator $statsGenerator,
        private MailerInterface $mailer
    ) {         $this->em = $em;
        $this->statsGenerator = $statsGenerator;}

    #[Route('/admin/analyses', name: 'app_analyse_index')]
    public function index(Request $request): Response
    {
        // Création du formulaire de filtre
        $filterForm = $this->createForm(AnalysisFilterType::class);
        $filterForm->handleRequest($request);
    
        // Vérification des données du formulaire et passage d'un tableau vide si null
        $filters = $filterForm->isSubmitted() && $filterForm->isValid() ? $filterForm->getData() : [];
    
        // Appel de la méthode avec les filtres
        $appointments = $this->em->getRepository(Appointment::class)
            ->findByFilters($filters);
    
        $commandes = $this->em->getRepository(Commande::class)
            ->findByFilters($filters);
    
        // Retour des données à la vue
        return $this->render('analyse/index.html.twig', [
            'appointments' => $appointments,
            'commandes' => $commandes,
            'filterForm' => $filterForm->createView(),
            'stats' => $this->statsGenerator->getGlobalStats()
        ]);
    }
    #[Route('/admin/analyses/rapport', name: 'app_analyse_report')]
    public function generateReport(Request $request, Pdf $knpSnappyPdf): Response
    {
        $data = $request->query->all();
        
        // Initialisation des valeurs par défaut
        $defaults = [
            'start_date' => null,
            'end_date' => null,
            'status' => null
        ];
        
        $data = array_merge($defaults, $data);
        
        $stats = $this->statsGenerator->getFilteredStats($data);
    
        $html = $this->renderView('analyse/report.pdf.twig', [
            'stats' => $stats,
            'filters' => $data
        ]);
    
        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'rapport-analytique-'.date('Ymd-His').'.pdf'
        );
    }

    #[Route('/admin/analyses/export', name: 'app_analyse_export')]
    public function exportData(Request $request): Response
    {
        // Export CSV des données
        $data = $request->query->all();
        $results = $this->statsGenerator->getExportData($data);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-analyses-'.date('Ymd-His').'.csv"');

        return $this->render('analyse/export.csv.twig', [
            'data' => $results
        ], $response);
    }

    #[Route('/admin/analyses/auto-status', name: 'app_analyse_auto_status')]
    public function autoUpdateStatus(): Response
    {
        // Mise à jour automatique des statuts
        try {
            $updated = $this->statsGenerator->autoUpdateCommandStatus();
            $this->addFlash('success', $updated.' statuts de commandes mis à jour');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour : '.$e->getMessage());
        }

        return $this->redirectToRoute('app_analyse_index');
    }

    #[Route('/admin/analyses/cleanup', name: 'app_analyse_cleanup')]
    public function cleanupOldData(): Response
    {
        $threshold = new \DateTime('-2 years');
        $repository = $this->em->getRepository(Appointment::class);
        
        try {
            $deleted = $repository->deleteOlderThan($threshold);
            $this->addFlash('success', $deleted.' anciens rendez-vous supprimés');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du nettoyage: '.$e->getMessage());
        }
        
        return $this->redirectToRoute('app_analyse_index');
    }

    // #[Route('/admin/analyses/notify', name: 'app_analyse_notify')]
    // public function sendNotifications(): Response
    // {
    //     try {
    //         $appointments = $this->em->getRepository(Appointment::class)
    //             ->findUpcomingAppointments();
    
    //         $sentCount = 0;
    //         foreach ($appointments as $appointment) {
    //             if ($appointment->getClientEmail()) { // Vérifiez que l'email existe
    //                 $email = (new Email())
    //                     ->to($appointment->getClientEmail())
    //                     ->subject('Rappel de rendez-vous')
    //                     ->html($this->renderView('emails/appointment_reminder.html.twig', [
    //                         'appointment' => $appointment
    //                     ]));
    
    //                 $this->mailer->send($email);
    //                 $sentCount++;
    //             }
    //         }
    
    //         $this->addFlash('success', $sentCount.' notifications envoyées');
    //     } catch (\Exception $e) {
    //         $this->addFlash('error', 'Erreur d\'envoi : '.$e->getMessage());
    //     }
    
    //     return $this->redirectToRoute('app_analyse_index');
    // }

    #[Route('/admin/analyses/notify', name: 'app_analyse_notify')]
public function sendNotifications(): Response
{
    try {
        $appointments = $this->em->getRepository(Appointment::class)
            ->findUpcomingAppointments();

        $sentCount = 0;
        foreach ($appointments as $appointment) {
            if ($appointment->getClientEmail()) { // Vérifiez que l'email existe
                $email = (new Email())
                    ->to($appointment->getClientEmail())
                    ->subject('Rappel de rendez-vous')
                    ->html($this->renderView('emails/appointment_reminder.html.twig', [
                        'appointment' => $appointment
                    ]));

                $this->mailer->send($email);
                $sentCount++;
            }
        }

        $this->addFlash('success', $sentCount.' notifications envoyées');
    } catch (\Exception $e) {
        $this->addFlash('error', 'Erreur d\'envoi : '.$e->getMessage());
    }

    return $this->redirectToRoute('app_analyse_index');
}

#[Route('/admin/analyses/indicators', name: 'app_analyse_indicator')]
public function indicatorCrud(): Response
{
    $indicators = $this->em->getRepository(AnalysisIndicator::class)->findAll();
    
    return $this->render('analyse/crud/indicator/index.html.twig', [
        'indicators' => $indicators,
        'currentValues' => $this->statsGenerator->getIndicatorValues($indicators),
        'stats' => $this->statsGenerator->getGlobalStats(),
    ]);
}

#[Route('/admin/analyses/indicators/new', name: 'app_analyse_indicator_new')]
public function newIndicator(Request $request): Response
{
    $indicator = new AnalysisIndicator();
    $form = $this->createForm(AnalysisIndicatorType::class, $indicator);
    
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $this->em->persist($indicator);
        $this->em->flush();
        
        $this->addFlash('success', 'Indicator created successfully');
        return $this->redirectToRoute('app_analyse_indicator');
    }
    
    return $this->render('analyse/crud/indicator/new.html.twig', [
        'form' => $form->createView() , 
        'available_metrics' => $this->statsGenerator->getAvailableMetrics()
    ]);
}

#[Route('/admin/analyses/indicators/{id}/edit', name: 'app_analyse_indicator_edit')]
public function editIndicator(Request $request, AnalysisIndicator $indicator): Response
{
    $form = $this->createForm(AnalysisIndicatorType::class, $indicator);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $this->em->flush();
        
        $this->addFlash('success', 'Indicator updated successfully');
        return $this->redirectToRoute('app_analyse_indicator');
    }
    
    return $this->render('analyse/crud/indicator/edit.html.twig', [
        'form' => $form->createView(),
        'indicator' => $indicator
    ]);
}

#[Route('/admin/analyses/indicators/{id}/delete', name: 'app_analyse_indicator_delete')]
public function deleteIndicator(AnalysisIndicator $indicator): Response
{
    $this->em->remove($indicator);
    $this->em->flush();
    
    $this->addFlash('success', 'Indicator deleted successfully');
    return $this->redirectToRoute('app_analyse_indicator');
}

}
