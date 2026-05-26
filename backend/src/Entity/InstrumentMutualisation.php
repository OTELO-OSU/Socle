<?php

namespace App\Entity;

use App\Repository\InstrumentMutualisationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity(repositoryClass: InstrumentMutualisationRepository::class)]
class InstrumentMutualisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'instrumentMutualisations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Instrument $instrumentSource = null;

    #[ORM\ManyToOne(inversedBy: 'instrumentMutualisations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["plateforme:detail"])]
    #[MaxDepth(1)]
    private ?Instrument $instrumentCible = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstrumentSource(): ?Instrument
    {
        return $this->instrumentSource;
    }

    public function setInstrumentSource(?Instrument $instrumentSource): static
    {
        $this->instrumentSource = $instrumentSource;

        return $this;
    }

    public function getInstrumentCible(): ?Instrument
    {
        return $this->instrumentCible;
    }

    public function setInstrumentCible(?Instrument $instrumentCible): static
    {
        $this->instrumentCible = $instrumentCible;

        return $this;
    }
}
