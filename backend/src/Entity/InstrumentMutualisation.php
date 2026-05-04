<?php

namespace App\Entity;

use App\Repository\InstrumentMutualisationRepository;
use Doctrine\ORM\Mapping as ORM;

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
    private ?Instrument $instrumentCible = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstrumentSource(): ?Instrument
    {
        return $this->instrument_source;
    }

    public function setInstrumentSource(?Instrument $instrumentSource): static
    {
        $this->instrument_source = $instrumentSource;

        return $this;
    }

    public function getInstrumentCible(): ?Instrument
    {
        return $this->instrument_cible;
    }

    public function setInstrumentCible(?Instrument $instrumentCible): static
    {
        $this->instrument_cible = $instrumentCible;

        return $this;
    }
}
