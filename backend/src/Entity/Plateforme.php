<?php

namespace App\Entity;

use App\Repository\PlateformeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: PlateformeRepository::class)]
class Plateforme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plateforme:list', 'plateforme:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['plateforme:list', 'plateforme:detail'])]
    private ?string $pseudoId = null;

    #[Groups(['plateforme:list', 'plateforme:detail'])]
    #[SerializedName('dernier_historique')]
    public function getLastHistorique(): ?PlateformeHistorique
    {
        $historiques = $this->getPlateformeHistoriques()->toArray();

        usort(
            $historiques,
            fn(PlateformeHistorique $a, PlateformeHistorique $b)
            => $b->getAnnee() <=> $a->getAnnee()
        );

        return $historiques[0] ?? null;
    }

    #[SerializedName('dernier_historique')]
    public function setLastHistorique(?PlateformeHistorique $historique): static
    {
        if ($historique !== null) {
            $this->addPlateformeHistorique($historique);
        }

        return $this;
    }

    /**
     * @var Collection<int, UtilisateurInscription>
     */
    #[ORM\OneToMany(targetEntity: UtilisateurInscription::class, mappedBy: 'plateformeDemande', cascade: ['remove'], orphanRemoval: true)]
    private Collection $utilisateurInscriptions;

    /**
     * @var Collection<int, PlateformeHistorique>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistorique::class, mappedBy: 'plateforme', cascade: ['remove'], orphanRemoval: true)]
    private Collection $plateformeHistoriques;

    #[ORM\Column]
    #[SerializedName("instrument_national")]
    #[Groups(['plateforme:list', "plateforme:detail"])]
    private bool $instrumentNational = false;

    #[ORM\OneToMany(mappedBy: "plateforme", targetEntity: PlateformeTags::class, cascade: ["remove"], orphanRemoval: true)]
    private Collection $plateformeTags;

    public function __construct()
    {
        $this->utilisateurInscriptions = new ArrayCollection();
        $this->plateformeHistoriques = new ArrayCollection();
        $this->plateformeTags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudoId(): ?string
    {
        return $this->pseudoId;
    }

    public function setPseudoId(?string $pseudoId): static
    {
        $this->pseudoId = $pseudoId;

        return $this;
    }

    /**
     * @return Collection<int, UtilisateurInscription>
     */
    public function getUtilisateurInscriptions(): Collection
    {
        return $this->utilisateurInscriptions;
    }

    public function addUtilisateurInscription(UtilisateurInscription $utilisateurInscription): static
    {
        if (!$this->utilisateurInscriptions->contains($utilisateurInscription)) {
            $this->utilisateurInscriptions->add($utilisateurInscription);
            $utilisateurInscription->setPlateformeDemande($this);
        }

        return $this;
    }

    public function removeUtilisateurInscription(UtilisateurInscription $utilisateurInscription): static
    {
        if ($this->utilisateurInscriptions->removeElement($utilisateurInscription)) {
            // set the owning side to null (unless already changed)
            if ($utilisateurInscription->getPlateformeDemande() === $this) {
                $utilisateurInscription->setPlateformeDemande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PlateformeHistorique>
     */
    public function getPlateformeHistoriques(): Collection
    {
        return $this->plateformeHistoriques;
    }

    public function addPlateformeHistorique(PlateformeHistorique $plateformeHistorique): static
    {
        if (!$this->plateformeHistoriques->contains($plateformeHistorique)) {
            $this->plateformeHistoriques->add($plateformeHistorique);
            $plateformeHistorique->setPlateforme($this);
        }

        return $this;
    }

    public function removePlateformeHistorique(PlateformeHistorique $plateformeHistorique): static
    {
        if ($this->plateformeHistoriques->removeElement($plateformeHistorique)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistorique->getPlateforme() === $this) {
                $plateformeHistorique->setPlateforme(null);
            }
        }

        return $this;
    }

    public function isInstrumentNational(): ?bool
    {
        return $this->instrumentNational;
    }

    public function setInstrumentNational(bool $instrumentNational): static
    {
        $this->instrumentNational = $instrumentNational;

        return $this;
    }

    #[SerializedName("tags_ids")]
    #[Groups(['plateforme:list', "plateforme:detail"])]
    public function getPlateformeTagsId(): array
    {
        $tagsId = [];
        foreach ($this->plateformeTags as $plateformeTag) {
            $tagsId[] = $plateformeTag->getTagId();
        }

        return $tagsId;
    }

    public function getPlateformeTags(): Collection
    {
        return $this->plateformeTags;
    }

    public function addPlateformeTag(PlateformeTags $PlateformeTags): static
    {
        if (!$this->plateformeTags->contains($PlateformeTags)) {
            $this->plateformeTags->add($PlateformeTags);
            $PlateformeTags->setPlateforme($this);
        }

        return $this;
    }

    public function removePlateformeTag(PlateformeTags $PlateformeTags): static
    {
        if ($this->plateformeTags->removeElement($PlateformeTags)) {
            // set the owning side to null (unless already changed)
            if ($PlateformeTags->getPlateforme() === $this) {
                $PlateformeTags->setPlateforme(null);
            }
        }

        return $this;
    }
}
