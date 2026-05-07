<?php

namespace App\Entity;

use App\Repository\PlateformeHistoriquePersonnelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

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
    #[Ignore]
    private ?PlateformeHistorique $plateformeHistorique = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriquePersonnels')]
    #[ORM\JoinColumn(name: 'utilisateur_id_id', referencedColumnName: 'id', nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(name: 'responsable_scientifique')]
    private ?bool $responsableScientifique = false;

    #[ORM\Column(name: 'responsable_technique')]
    private ?bool $responsableTechnique = false;

    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?int $quotite = 0;

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

    #[Groups(['plateforme:list'])]
    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    #[Groups(['plateforme:list'])]
    public function isResponsableScientifique(): ?bool
    {
        return $this->responsableScientifique;
    }

    public function setResponsableScientifique(bool $responsableScientifique): static
    {
        $this->responsableScientifique = $responsableScientifique;

        return $this;
    }

    #[Groups(['plateforme:list'])]
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

    public function setQuotite(int $quotite): static
    {
        $this->quotite = $quotite;

        return $this;
    }

    #[Groups(['plateforme:list'])]
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
