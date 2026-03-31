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
    #[ORM\JoinColumn(nullable: false)]
    private ?PlateformeHistorique $plateforme_historique = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $modele = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $annee_acquisition = null;

    #[ORM\Column]
    private ?float $cout = null;

    #[ORM\Column(nullable: true)]
    private ?float $valeur_remplacement = null;

    #[ORM\Column(nullable: true)]
    private ?int $duree_utilisation = null;

    #[ORM\ManyToOne(inversedBy: 'instruments')]
    private ?Operation $operation = null;

    #[ORM\Column]
    private ?int $annee_achat_envisagee = null;

    #[ORM\Column]
    private ?bool $futur = null;

    /**
     * @var Collection<int, InstrumentMutualisation>
     */
    #[ORM\OneToMany(targetEntity: InstrumentMutualisation::class, mappedBy: 'instrument_source')]
    private Collection $instrumentMutualisations;

    #[ORM\ManyToOne(inversedBy: 'instruments')]
    private ?ContratMaintenance $contrat_maintenance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire_nature_contrat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fournisseur_contrat = null;

    #[ORM\Column(nullable: true)]
    private ?bool $mutualise_autre_instrument = null;

    #[ORM\Column(nullable: true)]
    private ?bool $mutualise_umr_osu = null;

    #[ORM\Column(nullable: true)]
    private ?bool $mutualise_site = null;

    /**
     * @var Collection<int, FinancementInstrument>
     */
    #[ORM\OneToMany(targetEntity: FinancementInstrument::class, mappedBy: 'instrument')]
    private Collection $financementInstruments;

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
        return $this->plateforme_historique;
    }

    public function setPlateformeHistorique(?PlateformeHistorique $plateforme_historique): static
    {
        $this->plateforme_historique = $plateforme_historique;

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
        return $this->annee_acquisition;
    }

    public function setAnneeAcquisition(?\DateTime $annee_acquisition): static
    {
        $this->annee_acquisition = $annee_acquisition;

        return $this;
    }

    public function getCout(): ?float
    {
        return $this->cout;
    }

    public function setCout(float $cout): static
    {
        $this->cout = $cout;

        return $this;
    }

    public function getValeurRemplacement(): ?float
    {
        return $this->valeur_remplacement;
    }

    public function setValeurRemplacement(?float $valeur_remplacement): static
    {
        $this->valeur_remplacement = $valeur_remplacement;

        return $this;
    }

    public function getDureeUtilisation(): ?int
    {
        return $this->duree_utilisation;
    }

    public function setDureeUtilisation(?int $duree_utilisation): static
    {
        $this->duree_utilisation = $duree_utilisation;

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
        return $this->annee_achat_envisagee;
    }

    public function setAnneeAchatEnvisagee(int $annee_achat_envisagee): static
    {
        $this->annee_achat_envisagee = $annee_achat_envisagee;

        return $this;
    }

    public function isFutur(): ?bool
    {
        return $this->futur;
    }

    public function setFutur(bool $futur): static
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
        return $this->contrat_maintenance;
    }

    public function setContratMaintenance(?ContratMaintenance $contrat_maintenance): static
    {
        $this->contrat_maintenance = $contrat_maintenance;

        return $this;
    }

    public function getCommentaireNatureContrat(): ?string
    {
        return $this->commentaire_nature_contrat;
    }

    public function setCommentaireNatureContrat(?string $commentaire_nature_contrat): static
    {
        $this->commentaire_nature_contrat = $commentaire_nature_contrat;

        return $this;
    }

    public function getFournisseurContrat(): ?string
    {
        return $this->fournisseur_contrat;
    }

    public function setFournisseurContrat(?string $fournisseur_contrat): static
    {
        $this->fournisseur_contrat = $fournisseur_contrat;

        return $this;
    }

    public function isMutualiseAutreInstrument(): ?bool
    {
        return $this->mutualise_autre_instrument;
    }

    public function setMutualiseAutreInstrument(?bool $mutualise_autre_instrument): static
    {
        $this->mutualise_autre_instrument = $mutualise_autre_instrument;

        return $this;
    }

    public function isMutualiseUmrOsu(): ?bool
    {
        return $this->mutualise_umr_osu;
    }

    public function setMutualiseUmrOsu(?bool $mutualise_umr_osu): static
    {
        $this->mutualise_umr_osu = $mutualise_umr_osu;

        return $this;
    }

    public function isMutualiseSite(): ?bool
    {
        return $this->mutualise_site;
    }

    public function setMutualiseSite(?bool $mutualise_site): static
    {
        $this->mutualise_site = $mutualise_site;

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
}
