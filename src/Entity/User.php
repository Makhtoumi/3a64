<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    public function __construct()
{
    $this->dossierMedicals = new ArrayCollection();
}

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_DOCTOR = 'ROLE_DOCTOR';
    public const ROLE_PATIENT = 'ROLE_PATIENT';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    private string $status = 'pending';

    // Add these three required methods
    public function getSalt(): ?string
    {
        // Not needed when using modern hashing algorithms
        return null;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary sensitive data on the user, clear it here
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    // Required for newer Symfony versions
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Keep your existing methods below
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getStatus(): string
{
    return $this->status;
}

public function setStatus(string $status): self
{
    $this->status = $status;
    return $this;
}

public function getDossierMedicals(): Collection
{
    return $this->dossierMedicals;
}
public function addDossierMedical(DossierMedical $dossierMedical): self
{
    if (!$this->dossierMedicals->contains($dossierMedical)) {
        $this->dossierMedicals->add($dossierMedical);
        $dossierMedical->setPatient($this);
    }
    return $this;
}

public function removeDossierMedical(DossierMedical $dossierMedical): self
{
    if ($this->dossierMedicals->removeElement($dossierMedical)) {
        // Set the owning side to null (unless already changed)
        if ($dossierMedical->getPatient() === $this) {
            $dossierMedical->setPatient(null);
        }
    }
    return $this;
}
}