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
    private ?Instrument $instrument_source = null;

    #[ORM\ManyToOne(inversedBy: 'instrumentMutualisations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Instrument $instrument_cible = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstrumentSource(): ?Instrument
    {
        return $this->instrument_source;
    }

    public function setInstrumentSource(?Instrument $instrument_source): static
    {
        $this->instrument_source = $instrument_source;

        return $this;
    }

    public function getInstrumentCible(): ?Instrument
    {
        return $this->instrument_cible;
    }

    public function setInstrumentCible(?Instrument $instrument_cible): static
    {
        $this->instrument_cible = $instrument_cible;

        return $this;
    }
}
