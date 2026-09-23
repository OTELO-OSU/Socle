<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UtilisateurTags
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'utilisateurTags')]
    #[ORM\JoinColumn(nullable: false, name: "utilisateur_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private Utilisateur $utilisateur;

    #[ORM\Id]
    #[ORM\Column(name: "tag_id")]
    private int $tagId;

    public function __construct(Utilisateur $utilisateur, int $tagId)
    {
        $this->utilisateur = $utilisateur;
        $this->tagId = $tagId;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function setTagId(int $tagId): self
    {
        $this->tagId = $tagId;

        return $this;
    }
}
