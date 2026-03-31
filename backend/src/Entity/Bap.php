<?php

namespace App\Entity;

use App\Repository\BapRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BapRepository::class)]
class Bap
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1)]
    private ?string $lettre = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, CorpsBapEmploiType>
     */
    #[ORM\OneToMany(targetEntity: CorpsBapEmploiType::class, mappedBy: 'bap')]
    private Collection $corpsBapEmploiTypes;

    /**
     * @var Collection<int, UtilisateurBap>
     */
    #[ORM\OneToMany(targetEntity: UtilisateurBap::class, mappedBy: 'bap')]
    private Collection $utilisateurBaps;

    /**
     * @var Collection<int, PosteFuturBap>
     */
    #[ORM\OneToMany(targetEntity: PosteFuturBap::class, mappedBy: 'bap')]
    private Collection $posteFuturBaps;

    public function __construct()
    {
        $this->corpsBapEmploiTypes = new ArrayCollection();
        $this->utilisateurBaps = new ArrayCollection();
        $this->posteFuturBaps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLettre(): ?string
    {
        return $this->lettre;
    }

    public function setLettre(string $lettre): static
    {
        $this->lettre = $lettre;

        return $this;
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
     * @return Collection<int, CorpsBapEmploiType>
     */
    public function getCorpsBapEmploiTypes(): Collection
    {
        return $this->corpsBapEmploiTypes;
    }

    public function addCorpsBapEmploiType(CorpsBapEmploiType $corpsBapEmploiType): static
    {
        if (!$this->corpsBapEmploiTypes->contains($corpsBapEmploiType)) {
            $this->corpsBapEmploiTypes->add($corpsBapEmploiType);
            $corpsBapEmploiType->setBap($this);
        }

        return $this;
    }

    public function removeCorpsBapEmploiType(CorpsBapEmploiType $corpsBapEmploiType): static
    {
        if ($this->corpsBapEmploiTypes->removeElement($corpsBapEmploiType)) {
            // set the owning side to null (unless already changed)
            if ($corpsBapEmploiType->getBap() === $this) {
                $corpsBapEmploiType->setBap(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UtilisateurBap>
     */
    public function getUtilisateurBaps(): Collection
    {
        return $this->utilisateurBaps;
    }

    public function addUtilisateurBap(UtilisateurBap $utilisateurBap): static
    {
        if (!$this->utilisateurBaps->contains($utilisateurBap)) {
            $this->utilisateurBaps->add($utilisateurBap);
            $utilisateurBap->setBap($this);
        }

        return $this;
    }

    public function removeUtilisateurBap(UtilisateurBap $utilisateurBap): static
    {
        if ($this->utilisateurBaps->removeElement($utilisateurBap)) {
            // set the owning side to null (unless already changed)
            if ($utilisateurBap->getBap() === $this) {
                $utilisateurBap->setBap(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PosteFuturBap>
     */
    public function getPosteFuturBaps(): Collection
    {
        return $this->posteFuturBaps;
    }

    public function addPosteFuturBap(PosteFuturBap $posteFuturBap): static
    {
        if (!$this->posteFuturBaps->contains($posteFuturBap)) {
            $this->posteFuturBaps->add($posteFuturBap);
            $posteFuturBap->setBap($this);
        }

        return $this;
    }

    public function removePosteFuturBap(PosteFuturBap $posteFuturBap): static
    {
        if ($this->posteFuturBaps->removeElement($posteFuturBap)) {
            // set the owning side to null (unless already changed)
            if ($posteFuturBap->getBap() === $this) {
                $posteFuturBap->setBap(null);
            }
        }

        return $this;
    }
}
