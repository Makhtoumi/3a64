<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: "App\Repository\AnalysisIndicatorRepository")]
class AnalysisIndicator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $calculationFormula = null;

    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotNull]
    private array $thresholds = [];

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isActive = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCalculationFormula(): ?string
    {
        return $this->calculationFormula;
    }

    public function setCalculationFormula(string $calculationFormula): static
    {
        $this->calculationFormula = $calculationFormula;
        return $this;
    }

    public function getThresholds(): array
    {
        return $this->thresholds;
    }

    public function setThresholds(array $thresholds): static
    {
        $this->thresholds = $thresholds;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }
}