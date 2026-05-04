<?php

namespace App\Entity;

use App\Repository\UtilisateurInscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurInscriptionRepository::class)]
class UtilisateurInscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'utilisateurInscription', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurInscriptions')]
    private ?Plateforme $plateformeDemande = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurInscriptions')]
    private ?Entite $uniteDemande = null;

    #[ORM\Column]
    private ?bool $osuDemande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getPlateformeDemande(): ?Plateforme
    {
        return $this->plateformeDemande;
    }

    public function setPlateformeDemande(?Plateforme $plateformeDemande): static
    {
        $this->plateformeDemande = $plateformeDemande;

        return $this;
    }

    public function getUniteDemande(): ?Entite
    {
        return $this->uniteDemande;
    }

    public function setUniteDemande(?Entite $uniteDemande): static
    {
        $this->uniteDemande = $uniteDemande;

        return $this;
    }

    public function isOsuDemande(): ?bool
    {
        return $this->osuDemande;
    }

    public function setOsuDemande(bool $osuDemande): static
    {
        $this->osuDemande = $osuDemande;

        return $this;
    }
}
