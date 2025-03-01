<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Appointment;
use App\Entity\Prescription;
use App\Repository\PrescriptionRepository;
use App\Form\AppointmentTypeBack;
use App\Form\PrescriptionTypeBack;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class AppointmentBackController extends AbstractController
{
    #[Route('/appointment/back', name: 'app_appointment_index')]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        // Fetch all appointments from the database
        $appointments = $appointmentRepository->findAll();
    
        // Pass the appointments to the view
        return $this->render('appointmentBack/index.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/appointment/back/new', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentTypeBack::class, $appointment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('appointmentBack/new.html.twig', [
            'appointment' => $appointment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/appointment/back/{id}', name: 'back_app_appointment_show', methods: ['GET'])]
    public function show(int $id, AppointmentRepository $appointmentRepository): Response
    {
        $appointment = $appointmentRepository->find($id);

        if (!$appointment) {
            throw $this->createNotFoundException('Appointment not found.');
        }

        return $this->render('appointmentBack/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/appointment/back/{id}/edit', name: 'back_app_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, AppointmentRepository $appointmentRepository, EntityManagerInterface $entityManager): Response
    {
        $appointment = $appointmentRepository->find($id);

        if (!$appointment) {
            throw $this->createNotFoundException('Appointment not found.');
        }

        $form = $this->createForm(AppointmentTypeBack::class, $appointment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('appointmentBack/edit.html.twig', [
            'appointment' => $appointment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/appointment/back/{id}', name: 'back_app_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, AppointmentRepository $appointmentRepository, EntityManagerInterface $entityManager): Response
    {
        $appointment = $appointmentRepository->find($id);

        if (!$appointment) {
            throw $this->createNotFoundException('Appointment not found.');
        }

        if ($this->isCsrfTokenValid('delete'.$appointment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($appointment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/appointment/back/{id}/prescriptions', name: 'back_app_appointment_prescriptions', methods: ['GET'])]
    public function viewPrescriptions(int $id, AppointmentRepository $appointmentRepository): Response
    {
        $appointment = $appointmentRepository->find($id);

        if (!$appointment) {
            throw $this->createNotFoundException('Appointment not found.');
        }

        $prescriptions = $appointment->getPrescriptions();

        return $this->render('appointmentBack/prescriptions.html.twig', [
            'appointment' => $appointment,
            'prescriptions' => $prescriptions,
        ]);
    }

    #[Route('/appointment/back/{id}/add-prescription', name: 'back_add_prescription', methods: ['GET', 'POST'])]
    public function addPrescription(Request $request, int $id, AppointmentRepository $appointmentRepository, EntityManagerInterface $entityManager): Response
    {
        $appointment = $appointmentRepository->find($id);

        if (!$appointment) {
            throw $this->createNotFoundException('Appointment not found.');
        }

        $prescription = new Prescription();
        $prescription->setAppointment($appointment);

        $form = $this->createForm(PrescriptionTypeBack::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($prescription);
            $entityManager->flush();

            return $this->redirectToRoute('back_app_appointment_show', ['id' => $appointment->getId()]);
        }

        return $this->render('appointmentBack/add_prescription.html.twig', [
            'form' => $form->createView(),
            'appointment' => $appointment,
        ]);
    }

    #[Route('/prescription/{id}/delete', name: 'app_prescription_delete', methods: ['POST'])]
    public function deletePrescription(Prescription $prescription, EntityManagerInterface $entityManager): Response
    {
        $appointmentId = $prescription->getAppointment()->getId();

        $entityManager->remove($prescription);
        $entityManager->flush();

        return $this->redirectToRoute('back_app_appointment_prescriptions', ['id' => $appointmentId]);
    }

    #[Route('/prescription/{id}/edit', name: 'app_prescription_edit', methods: ['GET', 'POST'])]
    public function editPrescription(Request $request, Prescription $prescription, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PrescriptionTypeBack::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('back_app_appointment_prescriptions', ['id' => $prescription->getAppointment()->getId()]);
        }

        return $this->render('appointmentBack/edit_prescription.html.twig', [
            'form' => $form->createView(),
            'prescription' => $prescription,
        ]);
    }
}
