<?php

namespace App\Entity;

use App\Repository\ContributionTutelleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContributionTutelleRepository::class)]
class ContributionTutelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contributionTutelles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlateformeHistorique $plateforme_historique = null;

    #[ORM\ManyToOne(inversedBy: 'contributionTutelles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entite $financeur = null;

    #[ORM\Column]
    private ?int $taux = null;

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

    public function getFinanceur(): ?Entite
    {
        return $this->financeur;
    }

    public function setFinanceur(?Entite $financeur): static
    {
        $this->financeur = $financeur;

        return $this;
    }

    public function getTaux(): ?int
    {
        return $this->taux;
    }

    public function setTaux(int $taux): static
    {
        $this->taux = $taux;

        return $this;
    }
}
