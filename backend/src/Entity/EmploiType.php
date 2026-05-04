<?php

namespace App\Entity;

use App\Repository\EmploiTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmploiTypeRepository::class)]
class EmploiType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $acronyme = null;

    /**
     * @var Collection<int, CorpsBapEmploiType>
     */
    #[ORM\OneToMany(targetEntity: CorpsBapEmploiType::class, mappedBy: 'emploiType')]
    private Collection $corpsBapEmploiTypes;

    /**
     * @var Collection<int, UtilisateurEmploiType>
     */
    #[ORM\OneToMany(targetEntity: UtilisateurEmploiType::class, mappedBy: 'emploiType')]
    private Collection $utilisateurEmploiTypes;

    /**
     * @var Collection<int, PosteFuturEmploiType>
     */
    #[ORM\OneToMany(targetEntity: PosteFuturEmploiType::class, mappedBy: 'emploiType')]
    private Collection $posteFuturEmploiTypes;

    public function __construct()
    {
        $this->corpsBapEmploiTypes = new ArrayCollection();
        $this->utilisateurEmploiTypes = new ArrayCollection();
        $this->posteFuturEmploiTypes = new ArrayCollection();
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

    public function getAcronyme(): ?string
    {
        return $this->acronyme;
    }

    public function setAcronyme(string $acronyme): static
    {
        $this->acronyme = $acronyme;

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
            $corpsBapEmploiType->setEmploiType($this);
        }

        return $this;
    }

    public function removeCorpsBapEmploiType(CorpsBapEmploiType $corpsBapEmploiType): static
    {
        if ($this->corpsBapEmploiTypes->removeElement($corpsBapEmploiType)) {
            // set the owning side to null (unless already changed)
            if ($corpsBapEmploiType->getEmploiType() === $this) {
                $corpsBapEmploiType->setEmploiType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UtilisateurEmploiType>
     */
    public function getUtilisateurEmploiTypes(): Collection
    {
        return $this->utilisateurEmploiTypes;
    }

    public function addUtilisateurEmploiType(UtilisateurEmploiType $utilisateurEmploiType): static
    {
        if (!$this->utilisateurEmploiTypes->contains($utilisateurEmploiType)) {
            $this->utilisateurEmploiTypes->add($utilisateurEmploiType);
            $utilisateurEmploiType->setEmploiType($this);
        }

        return $this;
    }

    public function removeUtilisateurEmploiType(UtilisateurEmploiType $utilisateurEmploiType): static
    {
        if ($this->utilisateurEmploiTypes->removeElement($utilisateurEmploiType)) {
            // set the owning side to null (unless already changed)
            if ($utilisateurEmploiType->getEmploiType() === $this) {
                $utilisateurEmploiType->setEmploiType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PosteFuturEmploiType>
     */
    public function getPosteFuturEmploiTypes(): Collection
    {
        return $this->posteFuturEmploiTypes;
    }

    public function addPosteFuturEmploiType(PosteFuturEmploiType $posteFuturEmploiType): static
    {
        if (!$this->posteFuturEmploiTypes->contains($posteFuturEmploiType)) {
            $this->posteFuturEmploiTypes->add($posteFuturEmploiType);
            $posteFuturEmploiType->setEmploiType($this);
        }

        return $this;
    }

    public function removePosteFuturEmploiType(PosteFuturEmploiType $posteFuturEmploiType): static
    {
        if ($this->posteFuturEmploiTypes->removeElement($posteFuturEmploiType)) {
            // set the owning side to null (unless already changed)
            if ($posteFuturEmploiType->getEmploiType() === $this) {
                $posteFuturEmploiType->setEmploiType(null);
            }
        }

        return $this;
    }
}
