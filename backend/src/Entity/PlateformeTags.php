<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PlateformeTags
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Plateforme::class, inversedBy: 'plateformeTags')]
    #[ORM\JoinColumn(nullable: false, name: "plateforme_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private Plateforme $plateforme;

    #[ORM\Id]
    #[ORM\Column(name: "tag_id")]
    private int $tagId;

    public function __construct(Plateforme $plateforme, int $tagId)
    {
        $this->plateforme = $plateforme;
        $this->tagId = $tagId;
    }

    public function getPlateforme(): Plateforme
    {
        return $this->plateforme;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function setPlateforme(?Plateforme $plateforme): self
    {
        $this->plateforme = $plateforme;

        return $this;
    }

    public function setTagId(int $tagId): self
    {
        $this->tagId = $tagId;

        return $this;
    }
}
