<?php

namespace App\Entity;

use App\Repository\PosteFuturRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PosteFuturRepository::class)]
class PosteFutur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posteFuturs')]
    #[ORM\JoinColumn(name: 'plateforme_historique_id', referencedColumnName: 'id', nullable: false)]
    private ?PlateformeHistorique $plateformeHistorique = null;

    #[ORM\ManyToOne(inversedBy: 'posteFuturs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entite $employeur = null;

    #[ORM\ManyToOne(inversedBy: 'posteFuturs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Corps $corps = null;

    #[ORM\Column]
    private ?int $quotite = null;

    #[ORM\Column]
    private ?int $rangNumerateur = null;

    #[ORM\Column]
    private ?int $rangDenominateur = null;

    /**
     * @var Collection<int, PosteFuturBap>
     */
    #[ORM\OneToMany(targetEntity: PosteFuturBap::class, mappedBy: 'posteFutur', cascade: ['remove'], orphanRemoval: true)]
    private Collection $posteFuturBaps;

    /**
     * @var Collection<int, PosteFuturEmploiType>
     */
    #[ORM\OneToMany(targetEntity: PosteFuturEmploiType::class, mappedBy: 'posteFutur', cascade: ['remove'], orphanRemoval: true)]
    private Collection $posteFuturEmploiTypes;

    public function __construct()
    {
        $this->posteFuturBaps = new ArrayCollection();
        $this->posteFuturEmploiTypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlateformeHistorique(): ?PlateformeHistorique
    {
        return $this->plateformeHistorique;
    }

    public function setPlateformeHistorique(?PlateformeHistorique $plateformeHistorique): static
    {
        $this->plateformeHistorique = $plateformeHistorique;

        return $this;
    }

    public function getEmployeur(): ?Entite
    {
        return $this->employeur;
    }

    public function setEmployeur(?Entite $employeur): static
    {
        $this->employeur = $employeur;

        return $this;
    }

    public function getCorps(): ?Corps
    {
        return $this->corps;
    }

    public function setCorps(?Corps $corps): static
    {
        $this->corps = $corps;

        return $this;
    }

    public function getQuotite(): ?int
    {
        return $this->quotite;
    }

    public function setQuotite(int $quotite): static
    {
        $this->quotite = $quotite;

        return $this;
    }

    public function getRangNumerateur(): ?int
    {
        return $this->rangNumerateur;
    }

    public function setRangNumerateur(int $rangNumerateur): static
    {
        $this->rangNumerateur = $rangNumerateur;

        return $this;
    }

    public function getRangDenominateur(): ?int
    {
        return $this->rangDenominateur;
    }

    public function setRangDenominateur(int $rangDenominateur): static
    {
        $this->rangDenominateur = $rangDenominateur;

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
            $posteFuturBap->setPosteFutur($this);
        }

        return $this;
    }

    public function removePosteFuturBap(PosteFuturBap $posteFuturBap): static
    {
        if ($this->posteFuturBaps->removeElement($posteFuturBap)) {
            // set the owning side to null (unless already changed)
            if ($posteFuturBap->getPosteFutur() === $this) {
                $posteFuturBap->setPosteFutur(null);
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
            $posteFuturEmploiType->setPosteFutur($this);
        }

        return $this;
    }

    public function removePosteFuturEmploiType(PosteFuturEmploiType $posteFuturEmploiType): static
    {
        if ($this->posteFuturEmploiTypes->removeElement($posteFuturEmploiType)) {
            // set the owning side to null (unless already changed)
            if ($posteFuturEmploiType->getPosteFutur() === $this) {
                $posteFuturEmploiType->setPosteFutur(null);
            }
        }

        return $this;
    }
}
