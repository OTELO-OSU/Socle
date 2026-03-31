<?php

namespace App\Entity;

use App\Repository\PosteFuturBapRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PosteFuturBapRepository::class)]
class PosteFuturBap
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posteFuturBaps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PosteFutur $poste_futur = null;

    #[ORM\ManyToOne(inversedBy: 'posteFuturBaps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bap $bap = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosteFutur(): ?PosteFutur
    {
        return $this->poste_futur;
    }

    public function setPosteFutur(?PosteFutur $poste_futur): static
    {
        $this->poste_futur = $poste_futur;

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
}
