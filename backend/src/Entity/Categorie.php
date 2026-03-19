<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Entite>
     */
    #[ORM\OneToMany(targetEntity: Entite::class, mappedBy: 'categorie', orphanRemoval: true)]
    private Collection $entites;

    public function __construct()
    {
        $this->entites = new ArrayCollection();
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
     * @return Collection<int, Entite>
     */
    public function getEntites(): Collection
    {
        return $this->entites;
    }

    public function addEntite(Entite $entite): static
    {
        if (!$this->entites->contains($entite)) {
            $this->entites->add($entite);
            $entite->setCategorie($this);
        }

        return $this;
    }

    public function removeEntite(Entite $entite): static
    {
        if ($this->entites->removeElement($entite)) {
            // set the owning side to null (unless already changed)
            if ($entite->getCategorie() === $this) {
                $entite->setCategorie(null);
            }
        }

        return $this;
    }
}
