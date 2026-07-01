<?php

namespace App\Entity;

use App\Repository\ModaliteRecrutementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModaliteRecrutementRepository::class)]
class ModaliteRecrutement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, PosteFutur>
     */
    #[ORM\OneToMany(targetEntity: PosteFutur::class, mappedBy: 'modaliteRecrutement')]
    private Collection $posteFuturs;

    public function __construct()
    {
        $this->posteFuturs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, PosteFutur>
     */
    public function getPosteFuturs(): Collection
    {
        return $this->posteFuturs;
    }

    public function addPosteFutur(PosteFutur $posteFutur): static
    {
        if (!$this->posteFuturs->contains($posteFutur)) {
            $this->posteFuturs->add($posteFutur);
            $posteFutur->setModaliteRecrutement($this);
        }

        return $this;
    }

    public function removePosteFutur(PosteFutur $posteFutur): static
    {
        if ($this->posteFuturs->removeElement($posteFutur)) {
            // set the owning side to null (unless already changed)
            if ($posteFutur->getModaliteRecrutement() === $this) {
                $posteFutur->setModaliteRecrutement(null);
            }
        }

        return $this;
    }
}
