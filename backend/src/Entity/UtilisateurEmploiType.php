<?php

namespace App\Entity;

use App\Repository\UtilisateurEmploiTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurEmploiTypeRepository::class)]
class UtilisateurEmploiType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurEmploiTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurEmploiTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmploiType $emploi_type = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmploiType(): ?EmploiType
    {
        return $this->emploi_type;
    }

    public function setEmploiType(?EmploiType $emploi_type): static
    {
        $this->emploi_type = $emploi_type;

        return $this;
    }
}
