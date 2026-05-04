<?php

namespace App\Entity;

use App\Repository\CorpsBapEmploiTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CorpsBapEmploiTypeRepository::class)]
class CorpsBapEmploiType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'corpsBapEmploiTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Corps $corps = null;

    #[ORM\ManyToOne(inversedBy: 'corpsBapEmploiTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bap $bap = null;

    #[ORM\ManyToOne(inversedBy: 'corpsBapEmploiTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmploiType $emploiType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCorps(): ?Corps
    {
        return $this->corps;
    }

    public function setCorps(?Corps $corps): static
    {
        $this->corps = $corps;

        return $this;
    }

    public function getBap(): ?Bap
    {
        return $this->bap;
    }

    public function setBap(?Bap $bap): static
    {
        $this->bap = $bap;

        return $this;
    }

    public function getEmploiType(): ?EmploiType
    {
        return $this->emploiType;
    }

    public function setEmploiType(?EmploiType $emploiType): static
    {
        $this->emploiType = $emploiType;

        return $this;
    }
}
