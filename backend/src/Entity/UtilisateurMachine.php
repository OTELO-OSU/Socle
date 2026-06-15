<?php

namespace App\Entity;

use App\Repository\UtilisateurMachineRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: UtilisateurMachineRepository::class)]
class UtilisateurMachine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(["hmt:admin"])]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'utilisateurMachine', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[Groups(["hmt:admin"])]
    #[SerializedName("client_id")]
    #[ORM\Column(length: 255)]
    private ?string $clientId = null;

    #[ORM\Column(length: 255)]
    private ?string $clientSecret = null;

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

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    #[Groups(["hmt:admin"])]
    #[SerializedName("tags_ids")]
    public function getTags()
    {
        $tagArray = [];
        /** @var UtilisateurTags $tag */
        foreach ($this->utilisateur->getUtilisateurTags() as $tag) {
            $tagArray[] = $tag->getTagId();
        }

        return $tagArray;
    }

    #[Groups(["hmt:admin"])]
    #[SerializedName("utilisateur_id")]
    public function getUtilisateurId(): ?int
    {
        return $this->utilisateur->getId();
    }
}
