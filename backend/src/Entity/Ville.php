<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(['ville:list'])]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['ville:list', 'plateforme:harvester'])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Groups(['ville:list', 'plateforme:harvester'])]
    #[SerializedName("numero_departement")]
    #[ORM\Column]
    private ?string $numeroDepartement = null;

    #[Groups(['ville:list', 'plateforme:harvester'])]
    #[ORM\Column(length: 255)]
    private ?string $departement = null;

    /**
     * @var Collection<int, PlateformeHistorique>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistorique::class, mappedBy: 'ville')]
    private Collection $plateformeHistoriques;

    public function __construct()
    {
        $this->plateformeHistoriques = new ArrayCollection();
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

    public function getNumeroDepartement(): ?string
    {
        return $this->numeroDepartement;
    }

    public function setNumeroDepartement(string $numeroDepartement): static
    {
        $this->numeroDepartement = $numeroDepartement;

        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(string $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    /**
     * @return Collection<int, PlateformeHistorique>
     */
    public function getPlateformeHistoriques(): Collection
    {
        return $this->plateformeHistoriques;
    }

    public function addPlateformeHistorique(PlateformeHistorique $plateformeHistorique): static
    {
        if (!$this->plateformeHistoriques->contains($plateformeHistorique)) {
            $this->plateformeHistoriques->add($plateformeHistorique);
            $plateformeHistorique->setVille($this);
        }

        return $this;
    }

    public function removePlateformeHistorique(PlateformeHistorique $plateformeHistorique): static
    {
        if ($this->plateformeHistoriques->removeElement($plateformeHistorique)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistorique->getVille() === $this) {
                $plateformeHistorique->setVille(null);
            }
        }

        return $this;
    }
}
