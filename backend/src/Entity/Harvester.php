<?php

namespace App\Entity;

use App\Repository\HarvesterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: HarvesterRepository::class)]
class Harvester
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(["harvester:admin"])]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["harvester:admin"])]
    #[ORM\Column(length: 255)]
    private ?string $client_id = null;

    #[ORM\Column(length: 255)]
    private ?string $client_secret = null;

    #[Groups(["harvester:admin"])]
    #[ORM\Column(length: 255)]
    private ?string $host = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?string
    {
        return $this->client_id;
    }

    public function setClientId(string $client_id): static
    {
        $this->client_id = $client_id;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->client_secret;
    }

    public function setClientSecret(string $client_secret): static
    {
        $this->client_secret = $client_secret;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }
}
