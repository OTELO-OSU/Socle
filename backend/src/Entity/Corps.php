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
    #[Groups(['utilisateur:list', 'corps:list', "plateforme:detail"])]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['utilisateur:list', 'corps:list'])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Groups(['utilisateur:list', 'corps:list'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $acronyme = null;

    /**
     * @var Collection<int, Grade>
     */
    #[Groups(['corps:list'])]
    #[ORM\ManyToMany(targetEntity: Grade::class)]
    #[ORM\JoinTable(name: 'corps_grade')]
    private Collection $grades;

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

    public function __construct()
    {
        $this->grades = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
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
     * @return Collection<int, Grade>
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(Grade $grade): static
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
        }

        return $this;
    }

    public function removeGrade(Grade $grade): static
    {
        $this->grades->removeElement($grade);

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
}
