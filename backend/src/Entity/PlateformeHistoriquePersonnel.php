<?php

namespace App\Entity;

use App\Repository\PlateformeHistoriquePersonnelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PlateformeHistoriquePersonnelRepository::class)]
class PlateformeHistoriquePersonnel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriquePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlateformeHistorique $plateforme_historique = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriquePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['plateforme:list'])]
    private ?Utilisateur $utilisateur_id = null;

    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?bool $responsable_scientifique = false;

    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?bool $responsable_technique = false;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriquePersonnels')]
    #[ORM\JoinColumn(nullable: true)]
    private ?TypeContrat $type_contrat = null;

    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?int $quotite = 0;

    #[ORM\Column]
    private ?bool $responsablePlateforme = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlateformeHistorique(): ?PlateformeHistorique
    {
        return $this->plateforme_historique;
    }

    public function setPlateformeHistorique(?PlateformeHistorique $plateforme_historique_id): static
    {
        $this->plateforme_historique = $plateforme_historique_id;

        return $this;
    }

    public function getUtilisateurId(): ?Utilisateur
    {
        return $this->utilisateur_id;
    }

    public function setUtilisateurId(?Utilisateur $utilisateur_id): static
    {
        $this->utilisateur_id = $utilisateur_id;

        return $this;
    }

    public function isResponsableScientifique(): ?bool
    {
        return $this->responsable_scientifique;
    }

    public function setResponsableScientifique(bool $responsable_scientifique): static
    {
        $this->responsable_scientifique = $responsable_scientifique;

        return $this;
    }

    public function isResponsableTechnique(): ?bool
    {
        return $this->responsable_technique;
    }

    public function setResponsableTechnique(bool $responsable_technique): static
    {
        $this->responsable_technique = $responsable_technique;

        return $this;
    }

    public function getTypeContrat(): ?TypeContrat
    {
        return $this->type_contrat;
    }

    public function setTypeContrat(?TypeContrat $type_contrat): static
    {
        $this->type_contrat = $type_contrat;

        return $this;
    }

    public function getQuotite(): ?int
    {
        return $this->quotite;
    }

    public function setQuotite(int $quotite): static
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
