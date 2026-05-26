<?php

namespace App\Entity;

use App\Repository\ContributionTutelleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: ContributionTutelleRepository::class)]
class ContributionTutelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contributionTutelles')]
    #[ORM\JoinColumn(name: 'plateforme_historique_id', referencedColumnName: 'id', nullable: false)]
    private ?PlateformeHistorique $plateformeHistorique = null;

    #[ORM\ManyToOne(inversedBy: 'contributionTutelles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entite $financeur = null;

    #[Groups(["plateforme:detail"])]
    #[ORM\Column]
    private ?int $taux = null;

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

    #[Groups(["plateforme:detail"])]
    #[SerializedName('financeur_id')]
    public function getFinanceurId(): ?int
    {
        return $this->financeur?->getId();
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
