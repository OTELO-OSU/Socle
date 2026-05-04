<?php

namespace App\Entity;

use App\Repository\PlateformeHistoriqueTutelleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlateformeHistoriqueTutelleRepository::class)]
class PlateformeHistoriqueTutelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriqueTutelles')]
    #[ORM\JoinColumn(name: 'plateforme_historique_id', referencedColumnName: 'id', nullable: false)]
    private ?PlateformeHistorique $plateformeHistorique = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriqueTutelles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entite $tutelle = null;

    #[ORM\Column]
    private ?bool $principale = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlateformeHistorique(): ?PlateformeHistorique
    {
        return $this->plateformeHistorique;
    }

    public function setPlateformeHistorique(?PlateformeHistorique $plateformeHistorique): static
    {
        $this->plateformeHistorique = $plateformeHistorique;

        return $this;
    }

    public function getTutelle(): ?Entite
    {
        return $this->tutelle;
    }

    public function setTutelle(?Entite $tutelle): static
    {
        $this->tutelle = $tutelle;

        return $this;
    }

    public function isPrincipale(): ?bool
    {
        return $this->principale;
    }

    public function setPrincipale(bool $principale): static
    {
        $this->principale = $principale;

        return $this;
    }
}
