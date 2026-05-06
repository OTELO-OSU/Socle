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

    #[ORM\Column]
    private ?bool $directeurUniteDemande = null;

    #[ORM\Column]
    private ?bool $directeurOsuDemande = null;

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

    public function isDirecteurUniteDemande(): ?bool
    {
        return $this->directeurUniteDemande;
    }

    public function setDirecteurUniteDemande(?bool $directeurUniteDemande): static
    {
        $this->directeurUniteDemande = $directeurUniteDemande;

        return $this;
    }

    public function isDirecteurOsuDemande(): ?bool
    {
        return $this->directeurOsuDemande;
    }

    public function setDirecteurOsuDemande(bool $directeurOsuDemande): static
    {
        $this->directeurOsuDemande = $directeurOsuDemande;

        return $this;
    }
}
