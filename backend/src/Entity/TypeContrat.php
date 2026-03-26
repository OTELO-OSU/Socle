<?php

namespace App\Entity;

use App\Repository\TypeContratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeContratRepository::class)]
class TypeContrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, PlateformeHistoriquePersonnel>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistoriquePersonnel::class, mappedBy: 'type_contrat')]
    private Collection $plateformeHistoriquePersonnels;

    public function __construct()
    {
        $this->plateformeHistoriquePersonnels = new ArrayCollection();
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
     * @return Collection<int, PlateformeHistoriquePersonnel>
     */
    public function getPlateformeHistoriquePersonnels(): Collection
    {
        return $this->plateformeHistoriquePersonnels;
    }

    public function addPlateformeHistoriquePersonnel(PlateformeHistoriquePersonnel $plateformeHistoriquePersonnel): static
    {
        if (!$this->plateformeHistoriquePersonnels->contains($plateformeHistoriquePersonnel)) {
            $this->plateformeHistoriquePersonnels->add($plateformeHistoriquePersonnel);
            $plateformeHistoriquePersonnel->setTypeContrat($this);
        }

        return $this;
    }

    public function removePlateformeHistoriquePersonnel(PlateformeHistoriquePersonnel $plateformeHistoriquePersonnel): static
    {
        if ($this->plateformeHistoriquePersonnels->removeElement($plateformeHistoriquePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistoriquePersonnel->getTypeContrat() === $this) {
                $plateformeHistoriquePersonnel->setTypeContrat(null);
            }
        }

        return $this;
    }
}
