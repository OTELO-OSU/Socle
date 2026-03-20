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

    #[ORM\OneToOne(inversedBy: 'utilisateurInscription', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurInscriptions')]
    private ?Plateforme $plateforme_demande = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurInscriptions')]
    private ?Entite $unite_demande = null;

    #[ORM\Column]
    private ?bool $osu_demande = null;

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
        return $this->plateforme_demande;
    }

    public function setPlateformeDemande(?Plateforme $plateforme_demande): static
    {
        $this->plateforme_demande = $plateforme_demande;

        return $this;
    }

    public function getUniteDemande(): ?Entite
    {
        return $this->unite_demande;
    }

    public function setUniteDemande(?Entite $unite_demande): static
    {
        $this->unite_demande = $unite_demande;

        return $this;
    }

    public function isOsuDemande(): ?bool
    {
        return $this->osu_demande;
    }

    public function setOsuDemande(bool $osu_demande): static
    {
        $this->osu_demande = $osu_demande;

        return $this;
    }
}
