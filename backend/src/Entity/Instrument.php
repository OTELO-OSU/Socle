<?php

namespace App\Entity;

use App\Repository\InstrumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstrumentRepository::class)]
class Instrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'instruments')]
    #[ORM\JoinColumn(name: 'plateforme_historique_id', referencedColumnName: 'id', nullable: false)]
    private ?PlateformeHistorique $plateformeHistorique = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $modele = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $anneeAcquisition = null;

    #[ORM\Column(nullable: true)]
    private ?float $cout = null;

    #[ORM\Column(nullable: true)]
    private ?float $valeurRemplacement = null;

    #[ORM\Column(nullable: true)]
    private ?int $dureeUtilisation = null;

    #[ORM\ManyToOne(inversedBy: 'instruments')]
    private ?Operation $operation = null;

    #[ORM\Column(nullable: true)]
    private ?int $anneeAchatEnvisagee = null;

    #[ORM\Column]
    private ?bool $futur = null;

    /**
     * @var Collection<int, InstrumentMutualisation>
     */
    #[ORM\OneToMany(targetEntity: InstrumentMutualisation::class, mappedBy: 'instrumentSource')]
    private Collection $instrumentMutualisations;

    #[ORM\ManyToOne(inversedBy: 'instruments')]
    private ?ContratMaintenance $contratMaintenance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaireNatureContrat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fournisseurContrat = null;

    #[ORM\Column(nullable: true)]
    private ?bool $mutualiseAutreInstrument = null;

    #[ORM\Column(nullable: true)]
    private ?bool $mutualiseUmrOsu = null;

    #[ORM\Column(nullable: true)]
    private ?bool $mutualiseSite = null;

    /**
     * @var Collection<int, FinancementInstrument>
     */
    #[ORM\OneToMany(targetEntity: FinancementInstrument::class, mappedBy: 'instrument', cascade: ['remove'], orphanRemoval: true)]
    private Collection $financementInstruments;

    #[ORM\Column(nullable: true)]
    private ?float $coutMaintenance = null;

    public function __construct()
    {
        $this->instrumentMutualisations = new ArrayCollection();
        $this->financementInstruments = new ArrayCollection();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(?string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getAnneeAcquisition(): ?\DateTime
    {
        return $this->anneeAcquisition;
    }

    public function setAnneeAcquisition(?\DateTime $anneeAcquisition): static
    {
        $this->anneeAcquisition = $anneeAcquisition;

        return $this;
    }

    public function getCout(): ?float
    {
        return $this->cout;
    }

    public function setCout(?float $cout): static
    {
        $this->cout = $cout;

        return $this;
    }

    public function getValeurRemplacement(): ?float
    {
        return $this->valeurRemplacement;
    }

    public function setValeurRemplacement(?float $valeurRemplacement): static
    {
        $this->valeurRemplacement = $valeurRemplacement;

        return $this;
    }

    public function getDureeUtilisation(): ?int
    {
        return $this->dureeUtilisation;
    }

    public function setDureeUtilisation(?int $dureeUtilisation): static
    {
        $this->dureeUtilisation = $dureeUtilisation;

        return $this;
    }

    public function getOperation(): ?Operation
    {
        return $this->operation;
    }

    public function setOperation(?Operation $operation): static
    {
        $this->operation = $operation;

        return $this;
    }

    public function getAnneeAchatEnvisagee(): ?int
    {
        return $this->anneeAchatEnvisagee;
    }

    public function setAnneeAchatEnvisagee(?int $anneeAchatEnvisagee): static
    {
        $this->anneeAchatEnvisagee = $anneeAchatEnvisagee;

        return $this;
    }

    public function isFutur(): ?bool
    {
        return $this->futur;
    }

    public function setFutur(?bool $futur): static
    {
        $this->futur = $futur;

        return $this;
    }

    /**
     * @return Collection<int, InstrumentMutualisation>
     */
    public function getInstrumentMutualisations(): Collection
    {
        return $this->instrumentMutualisations;
    }

    public function addInstrumentMutualisation(InstrumentMutualisation $instrumentMutualisation): static
    {
        if (!$this->instrumentMutualisations->contains($instrumentMutualisation)) {
            $this->instrumentMutualisations->add($instrumentMutualisation);
            $instrumentMutualisation->setInstrumentSource($this);
        }

        return $this;
    }

    public function removeInstrumentMutualisation(InstrumentMutualisation $instrumentMutualisation): static
    {
        if ($this->instrumentMutualisations->removeElement($instrumentMutualisation)) {
            // set the owning side to null (unless already changed)
            if ($instrumentMutualisation->getInstrumentSource() === $this) {
                $instrumentMutualisation->setInstrumentSource(null);
            }
        }

        return $this;
    }

    public function getContratMaintenance(): ?ContratMaintenance
    {
        return $this->contratMaintenance;
    }

    public function setContratMaintenance(?ContratMaintenance $contratMaintenance): static
    {
        $this->contratMaintenance = $contratMaintenance;

        return $this;
    }

    public function getCommentaireNatureContrat(): ?string
    {
        return $this->commentaireNatureContrat;
    }

    public function setCommentaireNatureContrat(?string $commentaireNatureContrat): static
    {
        $this->commentaireNatureContrat = $commentaireNatureContrat;

        return $this;
    }

    public function getFournisseurContrat(): ?string
    {
        return $this->fournisseurContrat;
    }

    public function setFournisseurContrat(?string $fournisseurContrat): static
    {
        $this->fournisseurContrat = $fournisseurContrat;

        return $this;
    }

    public function isMutualiseAutreInstrument(): ?bool
    {
        return $this->mutualiseAutreInstrument;
    }

    public function setMutualiseAutreInstrument(?bool $mutualiseAutreInstrument): static
    {
        $this->mutualiseAutreInstrument = $mutualiseAutreInstrument;

        return $this;
    }

    public function isMutualiseUmrOsu(): ?bool
    {
        return $this->mutualiseUmrOsu;
    }

    public function setMutualiseUmrOsu(?bool $mutualiseUmrOsu): static
    {
        $this->mutualiseUmrOsu = $mutualiseUmrOsu;

        return $this;
    }

    public function isMutualiseSite(): ?bool
    {
        return $this->mutualiseSite;
    }

    public function setMutualiseSite(?bool $mutualiseSite): static
    {
        $this->mutualiseSite = $mutualiseSite;

        return $this;
    }

    /**
     * @return Collection<int, FinancementInstrument>
     */
    public function getFinancementInstruments(): Collection
    {
        return $this->financementInstruments;
    }

    public function addFinancementInstrument(FinancementInstrument $financementInstrument): static
    {
        if (!$this->financementInstruments->contains($financementInstrument)) {
            $this->financementInstruments->add($financementInstrument);
            $financementInstrument->setInstrument($this);
        }

        return $this;
    }

    public function removeFinancementInstrument(FinancementInstrument $financementInstrument): static
    {
        if ($this->financementInstruments->removeElement($financementInstrument)) {
            // set the owning side to null (unless already changed)
            if ($financementInstrument->getInstrument() === $this) {
                $financementInstrument->setInstrument(null);
            }
        }

        return $this;
    }

    public function getCoutMaintenance(): ?float
    {
        return $this->coutMaintenance;
    }

    public function setCoutMaintenance(?float $coutMaintenance): static
    {
        $this->coutMaintenance = $coutMaintenance;

        return $this;
    }
}
