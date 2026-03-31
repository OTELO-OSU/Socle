<?php

namespace App\Entity;

use App\Repository\FinancementInstrumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FinancementInstrumentRepository::class)]
class FinancementInstrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'financementInstruments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Instrument $instrument = null;

    #[ORM\ManyToOne(inversedBy: 'financementInstruments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entite $financeur = null;

    #[ORM\Column]
    private ?int $taux = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstrument(): ?Instrument
    {
        return $this->instrument;
    }

    public function setInstrument(?Instrument $instrument): static
    {
        $this->instrument = $instrument;

        return $this;
    }

    public function getFinanceur(): ?Entite
    {
        return $this->financeur;
    }

    public function setFinanceur(?Entite $financeur): static
    {
        $this->financeur = $financeur;

        return $this;
    }

    public function getTaux(): ?int
    {
        return $this->taux;
    }

    public function setTaux(int $taux): static
    {
        $this->taux = $taux;

        return $this;
    }
}
