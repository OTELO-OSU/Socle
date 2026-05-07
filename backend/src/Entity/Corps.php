<?php

namespace App\Entity;

use App\Repository\CorpsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CorpsRepository::class)]
class Corps
{
    #[ORM\Id]
    #[Groups(['utilisateur:list'])]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['utilisateur:list'])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Groups(['utilisateur:list'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $acronyme = null;

    /**
     * @var Collection<int, CorpsGrade>
     */
    #[ORM\OneToMany(targetEntity: CorpsGrade::class, mappedBy: 'corps')]
    private Collection $corpsGrades;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'corps')]
    private Collection $utilisateurs;

    /**
     * @var Collection<int, PosteFutur>
     */
    #[ORM\OneToMany(targetEntity: PosteFutur::class, mappedBy: 'corps')]
    private Collection $posteFuturs;

    /**
     * @var Collection<int, CorpsBapEmploiType>
     */
    #[ORM\OneToMany(targetEntity: CorpsBapEmploiType::class, mappedBy: 'corps')]
    private Collection $corpsBapEmploiTypes;

    public function __construct()
    {
        $this->corpsGrades = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
        $this->posteFuturs = new ArrayCollection();
        $this->corpsBapEmploiTypes = new ArrayCollection();
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

    public function setAcronyme(?string $acronyme): static
    {
        $this->acronyme = $acronyme;

        return $this;
    }

    /**
     * @return Collection<int, CorpsGrade>
     */
    public function getCorpsGrades(): Collection
    {
        return $this->corpsGrades;
    }

    public function addCorpsGrade(CorpsGrade $corpsGrade): static
    {
        if (!$this->corpsGrades->contains($corpsGrade)) {
            $this->corpsGrades->add($corpsGrade);
            $corpsGrade->setCorps($this);
        }

        return $this;
    }

    public function removeCorpsGrade(CorpsGrade $corpsGrade): static
    {
        if ($this->corpsGrades->removeElement($corpsGrade)) {
            // set the owning side to null (unless already changed)
            if ($corpsGrade->getCorps() === $this) {
                $corpsGrade->setCorps(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setCorps($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getCorps() === $this) {
                $utilisateur->setCorps(null);
            }
        }

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
            $posteFutur->setCorps($this);
        }

        return $this;
    }

    public function removePosteFutur(PosteFutur $posteFutur): static
    {
        if ($this->posteFuturs->removeElement($posteFutur)) {
            // set the owning side to null (unless already changed)
            if ($posteFutur->getCorps() === $this) {
                $posteFutur->setCorps(null);
            }
        }

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
            $corpsBapEmploiType->setCorps($this);
        }

        return $this;
    }

    public function removeCorpsBapEmploiType(CorpsBapEmploiType $corpsBapEmploiType): static
    {
        if ($this->corpsBapEmploiTypes->removeElement($corpsBapEmploiType)) {
            // set the owning side to null (unless already changed)
            if ($corpsBapEmploiType->getCorps() === $this) {
                $corpsBapEmploiType->setCorps(null);
            }
        }

        return $this;
    }
}
