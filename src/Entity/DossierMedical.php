<?php
namespace App\Entity;

use App\Repository\DossierMedicalRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: DossierMedicalRepository::class)]
class DossierMedical
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $medications = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $familyMedicalHistory = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $patientEntries = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $doctorEntries = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $diagnoses = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $labResults = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $vaccinations = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'dossierMedicals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $patient = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $doctor = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): self
    {
        $this->allergies = $allergies;
        return $this;
    }

    public function getMedications(): ?string
    {
        return $this->medications;
    }

    public function setMedications(?string $medications): self
    {
        $this->medications = $medications;
        return $this;
    }

    public function getFamilyMedicalHistory(): ?string
    {
        return $this->familyMedicalHistory;
    }

    public function setFamilyMedicalHistory(?string $familyMedicalHistory): self
    {
        $this->familyMedicalHistory = $familyMedicalHistory;
        return $this;
    }

    public function getPatientEntries(): ?string
    {
        return $this->patientEntries;
    }

    public function setPatientEntries(?string $patientEntries): self
    {
        $this->patientEntries = $patientEntries;
        return $this;
    }

    public function getDoctorEntries(): ?string
    {
        return $this->doctorEntries;
    }

    public function setDoctorEntries(?string $doctorEntries): self
    {
        $this->doctorEntries = $doctorEntries;
        return $this;
    }

    public function getDiagnoses(): ?string
    {
        return $this->diagnoses;
    }

    public function setDiagnoses(?string $diagnoses): self
    {
        $this->diagnoses = $diagnoses;
        return $this;
    }

    public function getLabResults(): ?string
    {
        return $this->labResults;
    }

    public function setLabResults(?string $labResults): self
    {
        $this->labResults = $labResults;
        return $this;
    }

    public function getVaccinations(): ?string
    {
        return $this->vaccinations;
    }

    public function setVaccinations(?string $vaccinations): self
    {
        $this->vaccinations = $vaccinations;
        return $this;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): self
    {
        $this->patient = $patient;
        return $this;
    }

    public function getDoctor(): ?User
    {
        return $this->doctor;
    }

    public function setDoctor(?User $doctor): self
    {
        $this->doctor = $doctor;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}