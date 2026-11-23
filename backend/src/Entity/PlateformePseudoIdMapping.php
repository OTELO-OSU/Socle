<?php

namespace App\Entity;

use App\Repository\PlateformePseudoIdMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlateformePseudoIdMappingRepository::class)]
class PlateformePseudoIdMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $plateformeId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instanceHost = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudoIdMapped = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlateformeId(): ?int
    {
        return $this->plateformeId;
    }

    public function setPlateformeId(int $plateformeId): static
    {
        $this->plateformeId = $plateformeId;

        return $this;
    }

    public function getInstanceHost(): ?string
    {
        return $this->instanceHost;
    }

    public function setInstanceHost(?string $instanceHost): static
    {
        $this->instanceHost = $instanceHost;

        return $this;
    }

    public function getPseudoIdMapped(): ?string
    {
        return $this->pseudoIdMapped;
    }

    public function setPseudoIdMapped(string $pseudoIdMapped): static
    {
        $this->pseudoIdMapped = $pseudoIdMapped;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
