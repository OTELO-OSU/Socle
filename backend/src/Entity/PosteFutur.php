<?php

namespace App\Entity;

use App\Repository\PosteFuturRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

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

    #[Groups(["plateforme:detail"])]
    #[ORM\ManyToOne(inversedBy: 'posteFuturs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entite $employeur = null;

    #[Groups(["plateforme:detail"])]
    #[ORM\ManyToOne(inversedBy: 'posteFuturs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Corps $corps = null;

    #[ORM\Column]
    #[Groups(["plateforme:detail", 'plateforme:harvester'])]
    private ?int $quotite = null;

    #[ORM\Column]
    #[Groups(["plateforme:detail", 'plateforme:harvester'])]
    #[SerializedName('rang_numerateur')]
    private ?int $rangNumerateur = null;

    #[ORM\Column]
    #[Groups(["plateforme:detail", 'plateforme:harvester'])]
    #[SerializedName('rang_denominateur')]
    private ?int $rangDenominateur = null;

    #[Groups(["plateforme:detail", "plateforme:harvester"])]
    #[SerializedName('baps_ids')]
    public function getBapsIds(): array
    {
        $array = [];
        foreach ($this->baps as $bap) {
            $array[] = $bap->getId();
        }
        return $array;
    }

    /**
     * @var Collection<int, Bap>
     */
    #[ORM\ManyToMany(targetEntity: Bap::class, cascade: ['remove'])]
    private Collection $baps;

    /**
     * @var Collection<int, EmploiType>
     */
    #[ORM\ManyToMany(targetEntity: EmploiType::class, cascade: ['remove'])]
    private Collection $emploiTypes;

    #[ORM\ManyToOne(inversedBy: 'posteFuturs')]
    private ?ModaliteRecrutement $modaliteRecrutement = null;

    public function __construct()
    {
        $this->baps = new ArrayCollection();
        $this->emploiTypes = new ArrayCollection();
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

    #[Groups(["plateforme:detail"])]
    #[SerializedName('employeur_id')]
    public function getEmployeurId(): ?int
    {
        return $this->employeur?->getId();
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
    #[Groups(["plateforme:detail"])]
    #[SerializedName('corps_id')]
    public function getCorpsId(): ?int
    {
        return $this->corps?->getId();
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
     * @return Collection<int, Bap>
     */
    public function getBaps(): Collection
    {
        return $this->baps;
    }

    public function addBap(Bap $posteFuturBap): static
    {
        if (!$this->baps->contains($posteFuturBap)) {
            $this->baps->add($posteFuturBap);
        }

        return $this;
    }

    public function removeBap(Bap $posteFuturBap): static
    {
        $this->baps->removeElement($posteFuturBap);
        return $this;
    }

    #[Groups(["plateforme:detail", "plateforme:harvester"])]
    #[SerializedName('emploi_types_ids')]
    public function getEmploiTypesIds(): array
    {
        $array = [];
        foreach ($this->emploiTypes as $emploiType) {
            $array[] = $emploiType->getId();
        }
        return $array;
    }

    /**
     * @return Collection<int, EmploiType>
     */
    public function getEmploiTypes(): Collection
    {
        return $this->emploiTypes;
    }

    public function addEmploiType(EmploiType $posteFuturEmploiType): static
    {
        if (!$this->emploiTypes->contains($posteFuturEmploiType)) {
            $this->emploiTypes->add($posteFuturEmploiType);
        }

        return $this;
    }

    public function removeEmploiType(EmploiType $posteFuturEmploiType): static
    {
        $this->emploiTypes->removeElement($posteFuturEmploiType);
        return $this;
    }

    #[Groups(["plateforme:detail", "plateforme:harvester"])]
    #[SerializedName('modalite_recrutement_id')]
    public function getModaliteRecrutementId(): ?int
    {
        return $this->modaliteRecrutement ? $this->modaliteRecrutement->getId() : null;
    }

    public function getModaliteRecrutement(): ?ModaliteRecrutement
    {
        return $this->modaliteRecrutement;
    }

    public function setModaliteRecrutement(?ModaliteRecrutement $modaliteRecrutement): static
    {
        $this->modaliteRecrutement = $modaliteRecrutement;

        return $this;
    }
}
