<?php 

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Form\DossierMedicalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/dossier-medical')]
class DossierMedicalController extends AbstractController
{
    #[Route('/', name: 'app_dossier_medical_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (in_array('ROLE_DOCTOR', $user->getRoles())) {
            // Doctors can see all records
            $dossierMedicals = $entityManager
                ->getRepository(DossierMedical::class)
                ->findAll();
        } else {
            // Patients can only see their own records
            $dossierMedicals = $entityManager
                ->getRepository(DossierMedical::class)
                ->findBy(['patient' => $user]);
        }

        return $this->render('dossier_medical/index.html.twig', [
            'dossier_medicals' => $dossierMedicals,
        ]);
    }

    #[Route('/new', name: 'app_dossier_medical_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Only doctors can create new records
        if (!in_array('ROLE_DOCTOR', $user->getRoles())) {
            throw new AccessDeniedException('Only doctors can create medical records.');
        }

        $dossierMedical = new DossierMedical();
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dossierMedical);
            $entityManager->flush();

            return $this->redirectToRoute('app_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dossier_medical/new.html.twig', [
            'dossier_medical' => $dossierMedical,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_dossier_medical_show', methods: ['GET'])]
    public function show(DossierMedical $dossierMedical): Response
    {
        $user = $this->getUser();

        // Patients can only view their own records
        if (!in_array('ROLE_DOCTOR', $user->getRoles()) && $dossierMedical->getPatient() !== $user) {
            throw new AccessDeniedException('You are not allowed to view this record.');
        }

        return $this->render('dossier_medical/show.html.twig', [
            'dossier_medical' => $dossierMedical,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_dossier_medical_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Only doctors can edit records
        if (!in_array('ROLE_DOCTOR', $user->getRoles())) {
            throw new AccessDeniedException('Only doctors can edit medical records.');
        }

        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dossier_medical/edit.html.twig', [
            'dossier_medical' => $dossierMedical,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_dossier_medical_delete', methods: ['POST'])]
    public function delete(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Only doctors can delete records
        if (!in_array('ROLE_DOCTOR', $user->getRoles())) {
            throw new AccessDeniedException('Only doctors can delete medical records.');
        }

        if ($this->isCsrfTokenValid('delete'.$dossierMedical->getId(), $request->request->get('_token'))) {
            $entityManager->remove($dossierMedical);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
    }
}