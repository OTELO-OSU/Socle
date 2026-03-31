<?php

namespace App\Entity;

use App\Repository\GradeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GradeRepository::class)]
class Grade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, CorpsGrade>
     */
    #[ORM\OneToMany(targetEntity: CorpsGrade::class, mappedBy: 'grade')]
    private Collection $corpsGrades;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'grade')]
    private Collection $utilisateurs;

    public function __construct()
    {
        $this->corpsGrades = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, CorpsGrade>
     */
    public function getCorpsGrades(): Collection
    {
        return $this->corpsGrades;
    }

    public function addCorpsGrade(CorpsGrade $corpsGrade): static
    {
        if (!$this->corpsGrades->contains($corpsGrade)) {
            $this->corpsGrades->add($corpsGrade);
            $corpsGrade->setGrade($this);
        }

        return $this;
    }

    public function removeCorpsGrade(CorpsGrade $corpsGrade): static
    {
        if ($this->corpsGrades->removeElement($corpsGrade)) {
            // set the owning side to null (unless already changed)
            if ($corpsGrade->getGrade() === $this) {
                $corpsGrade->setGrade(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setGrade($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getGrade() === $this) {
                $utilisateur->setGrade(null);
            }
        }

        return $this;
    }
}
