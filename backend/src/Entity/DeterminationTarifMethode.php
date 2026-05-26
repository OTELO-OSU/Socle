<?php

namespace App\Entity;

use App\Repository\DeterminationTarifMethodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DeterminationTarifMethodeRepository::class)]
class DeterminationTarifMethode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(["plateforme:detail", "determination_tarif:list"])]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["plateforme:detail", "determination_tarif:list"])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, PlateformeHistorique>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistorique::class, mappedBy: 'determinationTarifPlateforme')]
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
            $plateformeHistorique->setDeterminationTarifPlateforme($this);
        }

        return $this;
    }

    public function removePlateformeHistorique(PlateformeHistorique $plateformeHistorique): static
    {
        if ($this->plateformeHistoriques->removeElement($plateformeHistorique)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistorique->getDeterminationTarifPlateforme() === $this) {
                $plateformeHistorique->setDeterminationTarifPlateforme(null);
            }
        }

        return $this;
    }
}
