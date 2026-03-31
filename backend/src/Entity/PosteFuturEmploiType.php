<?php

namespace App\Entity;

use App\Repository\PosteFuturEmploiTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PosteFuturEmploiTypeRepository::class)]
class PosteFuturEmploiType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posteFuturEmploiTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PosteFutur $poste_futur = null;

    #[ORM\ManyToOne(inversedBy: 'posteFuturEmploiTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmploiType $emploi_type = null;

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

    public function getEmploiType(): ?EmploiType
    {
        return $this->emploi_type;
    }

    public function setEmploiType(?EmploiType $emploi_type): static
    {
        $this->emploi_type = $emploi_type;

        return $this;
    }
}
