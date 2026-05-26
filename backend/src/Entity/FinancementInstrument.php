<?php

namespace App\Entity;

use App\Repository\FinancementInstrumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FinancementInstrumentRepository::class)]
class FinancementInstrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(["plateforme:detail"])]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'financementInstruments', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Instrument $instrument = null;

    #[ORM\ManyToOne(inversedBy: 'financementInstruments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["plateforme:detail"])]
    private ?Entite $financeur = null;

    #[ORM\Column]
    #[Groups(["plateforme:detail"])]
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
