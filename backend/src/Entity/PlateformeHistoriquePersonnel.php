<?php

namespace App\Entity;

use App\Repository\PlateformeHistoriquePersonnelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: PlateformeHistoriquePersonnelRepository::class)]
class PlateformeHistoriquePersonnel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriquePersonnels')]
    #[ORM\JoinColumn(name: 'plateforme_historique_id', referencedColumnName: 'id', nullable: false)]
    private ?PlateformeHistorique $plateformeHistorique = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriquePersonnels')]
    #[Groups(['plateforme:list', "plateforme:detail"])]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[Groups(['plateforme:list', "plateforme:detail"])]
    #[SerializedName("responsable_scientifique")]
    #[ORM\Column(name: 'responsable_scientifique')]
    private ?bool $responsableScientifique = false;

    #[Groups(['plateforme:list', "plateforme:detail"])]
    #[SerializedName("responsable_technique")]
    #[ORM\Column(name: 'responsable_technique')]
    private ?bool $responsableTechnique = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['plateforme:list', "plateforme:detail"])]
    private ?int $quotite = 0;

    #[Groups(['plateforme:list', "plateforme:detail"])]
    #[SerializedName("responsable_plateforme")]
    #[ORM\Column(name: 'responsable_plateforme')]
    private ?bool $responsablePlateforme = false;

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

    #[Groups(['plateforme:detail'])]
    #[SerializedName('utilisateur_id')]
    public function getUtilisateurId(): ?int
    {
        return $this->utilisateur?->getId();
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function isResponsableScientifique(): ?bool
    {
        return $this->responsableScientifique;
    }

    public function setResponsableScientifique(bool $responsableScientifique): static
    {
        $this->responsableScientifique = $responsableScientifique;

        return $this;
    }

    public function isResponsableTechnique(): ?bool
    {
        return $this->responsableTechnique;
    }

    public function setResponsableTechnique(bool $responsableTechnique): static
    {
        $this->responsableTechnique = $responsableTechnique;

        return $this;
    }

    public function getQuotite(): ?int
    {
        return $this->quotite;
    }

    public function setQuotite(?int $quotite): static
    {
        $this->quotite = $quotite;

        return $this;
    }

    public function isResponsablePlateforme(): ?bool
    {
        return $this->responsablePlateforme;
    }

    public function setResponsablePlateforme(bool $responsablePlateforme): static
    {
        $this->responsablePlateforme = $responsablePlateforme;

        return $this;
    }
}
