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
    #[ORM\JoinColumn(nullable: false)]
    private ?PlateformeHistorique $plateforme_historique = null;

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
        return $this->plateforme_historique;
    }

    public function setPlateformeHistorique(?PlateformeHistorique $plateforme_historique): static
    {
        $this->plateforme_historique = $plateforme_historique;

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
