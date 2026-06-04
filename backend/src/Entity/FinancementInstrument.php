<?php

namespace App\Entity;

use App\Repository\FinancementInstrumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: FinancementInstrumentRepository::class)]
#[ORM\Table(name: 'financement_instrument')]
#[ORM\UniqueConstraint(name: 'uniq_financement_instrument_financeur', columns: ['instrument_id', 'financeur_id'])]
class FinancementInstrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'financementInstruments', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Instrument $instrument = null;

    #[ORM\ManyToOne(inversedBy: 'financementInstruments')]
    #[ORM\JoinColumn(nullable: false)]
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

    #[Groups(["plateforme:detail"])]
    #[SerializedName('financeur_id')]
    public function getFinanceurId()
    {
        return $this->financeur?->getId();
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
