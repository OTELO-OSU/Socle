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
    private ?PosteFutur $posteFutur = null;

    #[ORM\ManyToOne(inversedBy: 'posteFuturEmploiTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmploiType $emploiType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosteFutur(): ?PosteFutur
    {
        return $this->posteFutur;
    }

    public function setPosteFutur(?PosteFutur $posteFutur): static
    {
        $this->posteFutur = $posteFutur;

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
