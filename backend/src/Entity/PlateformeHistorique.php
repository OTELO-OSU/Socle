<?php

namespace App\Entity;

use App\Repository\PlateformeHistoriqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PlateformeHistoriqueRepository::class)]
class PlateformeHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plateforme $plateforme = null;

    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?int $annee = null;

    #[ORM\Column(length: 255)]
    #[Groups(['plateforme:list'])]
    private ?string $nomOfficiel = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['plateforme:list'])]
    private ?string $formeDeveloppeeNomOfficiel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteWeb = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    private ?Ville $ville = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $logoUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $autreTypeLabellisation = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    private ?Entite $laboratoireOperateur = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $espaceBureau = null;

    /**
     * @var Collection<int, PlateformeHistoriquePersonnel>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistoriquePersonnel::class, mappedBy: 'plateformeHistorique')]
    #[Groups(['plateforme:list'])]
    private Collection $plateformeHistoriquePersonnels;

    #[ORM\Column(nullable: true)]
    private ?float $espaceLaboratoire = null;

    #[ORM\Column(nullable: true)]
    private ?float $materielConsommable = null;

    #[ORM\Column(nullable: true)]
    private ?float $contratEntretien = null;

    #[ORM\Column(nullable: true)]
    private ?float $etudePrestation = null;

    #[ORM\Column(nullable: true)]
    private ?float $fluideEnergie = null;

    #[ORM\Column(nullable: true)]
    private ?float $missionFraisGeneraux = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    private ?DeterminationTarifMethode $determinationTarifPlateforme = null;

    #[ORM\Column(nullable: true)]
    private ?int $contributionTutelleTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contributionFondPublicTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contributionFondPriveTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contributionCreditTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contributionProjetRechercheTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contributionStructurePiaTaux = null;

    #[ORM\Column(nullable: true)]
    private ?float $dotationInstrumentNational = null;

    #[ORM\Column(nullable: true)]
    private ?bool $labellisationQualite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomLabellisationQualite = null;

    #[ORM\Column(nullable: true)]
    private ?bool $procedureTransparenteTous = null;

    #[ORM\Column(nullable: true)]
    private ?bool $procedureTransparentePrestationExterieure = null;

    #[ORM\Column(nullable: true)]
    private ?int $accueilInternationalTaux = null;

    #[ORM\Column(name: 'modele_77_2424_taux', nullable: true)]
    private ?int $modele772424Taux = null;

    #[ORM\Column(name: 'modele_77_1224_taux', nullable: true)]
    private ?int $modele771224Taux = null;

    #[ORM\Column(name: 'modele_57_2424_taux', nullable: true)]
    private ?int $modele572424Taux = null;

    #[ORM\Column(name: 'modele_57_1224_taux', nullable: true)]
    private ?int $modele571224Taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisationFonctionnementTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisationMaintenanceTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisationDeveloppementTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisationPanneTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisationTempsMortTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisationEquipeOperanteTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisationCollaborationInterneTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisationCollaborationExterneTaux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisationPrestationServiceTaux = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bgesRealise = null;

    #[ORM\Column(nullable: true)]
    private ?float $bgesDomicileTravail = null;

    #[ORM\Column(nullable: true)]
    private ?float $bgesMission = null;

    #[ORM\Column(nullable: true)]
    private ?float $bgesMissionExt = null;

    #[ORM\Column(nullable: true)]
    private ?float $bgesConsommable = null;

    #[ORM\Column(nullable: true)]
    private ?float $bgesEquipement = null;

    #[ORM\Column(nullable: true)]
    private ?float $bgesEnvoiEchantillon = null;

    #[ORM\Column(nullable: true)]
    private ?float $bgesBatiment = null;

    #[ORM\Column(nullable: true)]
    private ?float $bgesDechet = null;

    #[ORM\Column(nullable: true)]
    private ?int $publicationEquipeOperante = null;

    #[ORM\Column(nullable: true)]
    private ?int $publicationCollaborationInterne = null;

    #[ORM\Column(nullable: true)]
    private ?int $publicationCollaborationExterne = null;

    #[ORM\Column(nullable: true)]
    private ?int $publicationAutreType = null;

    #[ORM\Column(nullable: true)]
    private ?int $publicationThese = null;

    /**
     * @var Collection<int, PlateformeHistoriqueTutelle>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistoriqueTutelle::class, mappedBy: 'plateformeHistorique')]
    private Collection $plateformeHistoriqueTutelles;

    /**
     * @var Collection<int, PosteFutur>
     */
    #[ORM\OneToMany(targetEntity: PosteFutur::class, mappedBy: 'plateformeHistorique')]
    private Collection $posteFuturs;

    /**
     * @var Collection<int, ContributionTutelle>
     */
    #[ORM\OneToMany(targetEntity: ContributionTutelle::class, mappedBy: 'plateformeHistorique')]
    private Collection $contributionTutelles;

    /**
     * @var Collection<int, Instrument>
     */
    #[ORM\OneToMany(targetEntity: Instrument::class, mappedBy: 'plateformeHistorique')]
    private Collection $instruments;

    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?bool $brouillon = null;

    public function __construct()
    {
        $this->plateformeHistoriqueTutelles = new ArrayCollection();
        $this->plateformeHistoriquePersonnels = new ArrayCollection();
        $this->posteFuturs = new ArrayCollection();
        $this->contributionTutelles = new ArrayCollection();
        $this->instruments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): ?PlateformeHistorique
    {
        $this->id = $id;
        return $this;
    }

    public function getPlateforme(): ?Plateforme
    {
        return $this->plateforme;
    }

    public function setPlateforme(?Plateforme $plateforme): static
    {
        $this->plateforme = $plateforme;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getNomOfficiel(): ?string
    {
        return $this->nomOfficiel;
    }

    public function setNomOfficiel(string $nomOfficiel): static
    {
        $this->nomOfficiel = $nomOfficiel;

        return $this;
    }

    public function getFormeDeveloppeeNomOfficiel(): ?string
    {
        return $this->formeDeveloppeeNomOfficiel;
    }

    public function setFormeDeveloppeeNomOfficiel(?string $formeDeveloppeeNomOfficiel): static
    {
        $this->formeDeveloppeeNomOfficiel = $formeDeveloppeeNomOfficiel;

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): static
    {
        $this->siteWeb = $siteWeb;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): static
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    public function getAutreTypeLabellisation(): ?string
    {
        return $this->autreTypeLabellisation;
    }

    public function setAutreTypeLabellisation(?string $autreTypeLabellisation): static
    {
        $this->autreTypeLabellisation = $autreTypeLabellisation;

        return $this;
    }

    public function getLaboratoireOperateur(): ?Entite
    {
        return $this->laboratoireOperateur;
    }

    public function setLaboratoireOperateur(?Entite $laboratoireOperateur): static
    {
        $this->laboratoireOperateur = $laboratoireOperateur;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getEspaceBureau(): ?float
    {
        return $this->espaceBureau;
    }

    public function setEspaceBureau(?float $espaceBureau): static
    {
        $this->espaceBureau = $espaceBureau;

        return $this;
    }

    public function getEspaceLaboratoire(): ?float
    {
        return $this->espaceLaboratoire;
    }

    public function setEspaceLaboratoire(?float $espaceLaboratoire): static
    {
        $this->espaceLaboratoire = $espaceLaboratoire;

        return $this;
    }

    public function getMaterielConsommable(): ?float
    {
        return $this->materielConsommable;
    }

    public function setMaterielConsommable(?float $materielConsommable): static
    {
        $this->materielConsommable = $materielConsommable;

        return $this;
    }

    public function getContratEntretien(): ?float
    {
        return $this->contratEntretien;
    }

    public function setContratEntretien(?float $contratEntretien): static
    {
        $this->contratEntretien = $contratEntretien;

        return $this;
    }

    public function getEtudePrestation(): ?float
    {
        return $this->etudePrestation;
    }

    public function setEtudePrestation(?float $etudePrestation): static
    {
        $this->etudePrestation = $etudePrestation;

        return $this;
    }

    public function getFluideEnergie(): ?float
    {
        return $this->fluideEnergie;
    }

    public function setFluideEnergie(?float $fluideEnergie): static
    {
        $this->fluideEnergie = $fluideEnergie;

        return $this;
    }

    public function getMissionFraisGeneraux(): ?float
    {
        return $this->missionFraisGeneraux;
    }

    public function setMissionFraisGeneraux(?float $missionFraisGeneraux): static
    {
        $this->missionFraisGeneraux = $missionFraisGeneraux;

        return $this;
    }

    public function getDeterminationTarifPlateforme(): ?DeterminationTarifMethode
    {
        return $this->determinationTarifPlateforme;
    }

    public function setDeterminationTarifPlateforme(?DeterminationTarifMethode $determinationTarifPlateforme): static
    {
        $this->determinationTarifPlateforme = $determinationTarifPlateforme;

        return $this;
    }

    public function getContributionTutelleTaux(): ?int
    {
        return $this->contributionTutelleTaux;
    }

    public function setContributionTutelleTaux(?int $contributionTutelleTaux): static
    {
        $this->contributionTutelleTaux = $contributionTutelleTaux;

        return $this;
    }

    public function getContributionFondPublicTaux(): ?int
    {
        return $this->contributionFondPublicTaux;
    }

    public function setContributionFondPublicTaux(?int $contributionFondPublicTaux): static
    {
        $this->contributionFondPublicTaux = $contributionFondPublicTaux;

        return $this;
    }

    public function getContributionFondPriveTaux(): ?int
    {
        return $this->contributionFondPriveTaux;
    }

    public function setContributionFondPriveTaux(?int $contributionFondPriveTaux): static
    {
        $this->contributionFondPriveTaux = $contributionFondPriveTaux;

        return $this;
    }

    public function getContributionCreditTaux(): ?int
    {
        return $this->contributionCreditTaux;
    }

    public function setContributionCreditTaux(?int $contributionCreditTaux): static
    {
        $this->contributionCreditTaux = $contributionCreditTaux;

        return $this;
    }

    public function getContributionProjetRechercheTaux(): ?int
    {
        return $this->contributionProjetRechercheTaux;
    }

    public function setContributionProjetRechercheTaux(?int $contributionProjetRechercheTaux): static
    {
        $this->contributionProjetRechercheTaux = $contributionProjetRechercheTaux;

        return $this;
    }

    public function getContributionStructurePiaTaux(): ?int
    {
        return $this->contributionStructurePiaTaux;
    }

    public function setContributionStructurePiaTaux(?int $contributionStructurePiaTaux): static
    {
        $this->contributionStructurePiaTaux = $contributionStructurePiaTaux;

        return $this;
    }

    public function getDotationInstrumentNational(): ?float
    {
        return $this->dotationInstrumentNational;
    }

    public function setDotationInstrumentNational(?float $dotationInstrumentNational): static
    {
        $this->dotationInstrumentNational = $dotationInstrumentNational;

        return $this;
    }

    public function isLabellisationQualite(): ?bool
    {
        return $this->labellisationQualite;
    }

    public function setLabellisationQualite(?bool $labellisationQualite): static
    {
        $this->labellisationQualite = $labellisationQualite;

        return $this;
    }

    public function getNomLabellisationQualite(): ?string
    {
        return $this->nomLabellisationQualite;
    }

    public function setNomLabellisationQualite(?string $nomLabellisationQualite): static
    {
        $this->nomLabellisationQualite = $nomLabellisationQualite;

        return $this;
    }

    public function isProcedureTransparenteTous(): ?bool
    {
        return $this->procedureTransparenteTous;
    }

    public function setProcedureTransparenteTous(?bool $procedureTransparenteTous): static
    {
        $this->procedureTransparenteTous = $procedureTransparenteTous;

        return $this;
    }

    public function isProcedureTransparentePrestationExterieure(): ?bool
    {
        return $this->procedureTransparentePrestationExterieure;
    }

    public function setProcedureTransparentePrestationExterieure(?bool $procedureTransparentePrestationExterieure): static
    {
        $this->procedureTransparentePrestationExterieure = $procedureTransparentePrestationExterieure;

        return $this;
    }

    public function getAccueilInternationalTaux(): ?int
    {
        return $this->accueilInternationalTaux;
    }

    public function setAccueilInternationalTaux(?int $accueilInternationalTaux): static
    {
        $this->accueilInternationalTaux = $accueilInternationalTaux;

        return $this;
    }

    public function getModele772424Taux(): ?int
    {
        return $this->modele772424Taux;
    }

    public function setModele772424Taux(?int $modele772424Taux): static
    {
        $this->modele772424Taux = $modele772424Taux;

        return $this;
    }

    public function getModele771224Taux(): ?int
    {
        return $this->modele771224Taux;
    }

    public function setModele771224Taux(?int $modele771224Taux): static
    {
        $this->modele771224Taux = $modele771224Taux;

        return $this;
    }

    public function getModele572424Taux(): ?int
    {
        return $this->modele572424Taux;
    }

    public function setModele572424Taux(?int $modele572424Taux): static
    {
        $this->modele572424Taux = $modele572424Taux;

        return $this;
    }

    public function getModele571224Taux(): ?int
    {
        return $this->modele571224Taux;
    }

    public function setModele571224Taux(?int $modele571224Taux): static
    {
        $this->modele571224Taux = $modele571224Taux;

        return $this;
    }

    public function getUtilisationFonctionnementTaux(): ?int
    {
        return $this->utilisationFonctionnementTaux;
    }

    public function setUtilisationFonctionnementTaux(?int $utilisationFonctionnementTaux): static
    {
        $this->utilisationFonctionnementTaux = $utilisationFonctionnementTaux;

        return $this;
    }

    public function getUtilisationMaintenanceTaux(): ?int
    {
        return $this->utilisationMaintenanceTaux;
    }

    public function setUtilisationMaintenanceTaux(?int $utilisationMaintenanceTaux): static
    {
        $this->utilisationMaintenanceTaux = $utilisationMaintenanceTaux;

        return $this;
    }

    public function getUtilisationDeveloppementTaux(): ?int
    {
        return $this->utilisationDeveloppementTaux;
    }

    public function setUtilisationDeveloppementTaux(?int $utilisationDeveloppementTaux): static
    {
        $this->utilisationDeveloppementTaux = $utilisationDeveloppementTaux;

        return $this;
    }

    public function getUtilisationPanneTaux(): ?int
    {
        return $this->utilisationPanneTaux;
    }

    public function setUtilisationPanneTaux(?int $utilisationPanneTaux): static
    {
        $this->utilisationPanneTaux = $utilisationPanneTaux;

        return $this;
    }

    public function getUtilisationTempsMortTaux(): ?int
    {
        return $this->utilisationTempsMortTaux;
    }

    public function setUtilisationTempsMortTaux(?int $utilisationTempsMortTaux): static
    {
        $this->utilisationTempsMortTaux = $utilisationTempsMortTaux;

        return $this;
    }

    public function getUtilisationEquipeOperanteTaux(): ?int
    {
        return $this->utilisationEquipeOperanteTaux;
    }

    public function setUtilisationEquipeOperanteTaux(?int $utilisationEquipeOperanteTaux): static
    {
        $this->utilisationEquipeOperanteTaux = $utilisationEquipeOperanteTaux;

        return $this;
    }

    public function getUtilisationCollaborationInterneTaux(): ?int
    {
        return $this->utilisationCollaborationInterneTaux;
    }

    public function setUtilisationCollaborationInterneTaux(?int $utilisationCollaborationInterneTaux): static
    {
        $this->utilisationCollaborationInterneTaux = $utilisationCollaborationInterneTaux;

        return $this;
    }

    public function getUtilisationCollaborationExterneTaux(): ?int
    {
        return $this->utilisationCollaborationExterneTaux;
    }

    public function setUtilisationCollaborationExterneTaux(?int $utilisationCollaborationExterneTaux): static
    {
        $this->utilisationCollaborationExterneTaux = $utilisationCollaborationExterneTaux;

        return $this;
    }

    public function getUtilisationPrestationServiceTaux(): ?int
    {
        return $this->utilisationPrestationServiceTaux;
    }

    public function setUtilisationPrestationServiceTaux(?int $utilisationPrestationServiceTaux): static
    {
        $this->utilisationPrestationServiceTaux = $utilisationPrestationServiceTaux;

        return $this;
    }

    public function isBgesRealise(): ?bool
    {
        return $this->bgesRealise;
    }

    public function setBgesRealise(?bool $bgesRealise): static
    {
        $this->bgesRealise = $bgesRealise;

        return $this;
    }

    public function getBgesDomicileTravail(): ?float
    {
        return $this->bgesDomicileTravail;
    }

    public function setBgesDomicileTravail(?float $bgesDomicileTravail): static
    {
        $this->bgesDomicileTravail = $bgesDomicileTravail;

        return $this;
    }

    public function getBgesMission(): ?float
    {
        return $this->bgesMission;
    }

    public function setBgesMission(?float $bgesMission): static
    {
        $this->bgesMission = $bgesMission;

        return $this;
    }

    public function getBgesMissionExt(): ?float
    {
        return $this->bgesMissionExt;
    }

    public function setBgesMissionExt(?float $bgesMissionExt): static
    {
        $this->bgesMissionExt = $bgesMissionExt;

        return $this;
    }

    public function getBgesConsommable(): ?float
    {
        return $this->bgesConsommable;
    }

    public function setBgesConsommable(?float $bgesConsommable): static
    {
        $this->bgesConsommable = $bgesConsommable;

        return $this;
    }

    public function getBgesEquipement(): ?float
    {
        return $this->bgesEquipement;
    }

    public function setBgesEquipement(?float $bgesEquipement): static
    {
        $this->bgesEquipement = $bgesEquipement;

        return $this;
    }

    public function getBgesEnvoiEchantillon(): ?float
    {
        return $this->bgesEnvoiEchantillon;
    }

    public function setBgesEnvoiEchantillon(?float $bgesEnvoiEchantillon): static
    {
        $this->bgesEnvoiEchantillon = $bgesEnvoiEchantillon;

        return $this;
    }

    public function getBgesBatiment(): ?float
    {
        return $this->bgesBatiment;
    }

    public function setBgesBatiment(?float $bgesBatiment): static
    {
        $this->bgesBatiment = $bgesBatiment;

        return $this;
    }

    public function getBgesDechet(): ?float
    {
        return $this->bgesDechet;
    }

    public function setBgesDechet(?float $bgesDechet): static
    {
        $this->bgesDechet = $bgesDechet;

        return $this;
    }

    public function getPublicationEquipeOperante(): ?int
    {
        return $this->publicationEquipeOperante;
    }

    public function setPublicationEquipeOperante(?int $publicationEquipeOperante): static
    {
        $this->publicationEquipeOperante = $publicationEquipeOperante;

        return $this;
    }

    public function getPublicationCollaborationInterne(): ?int
    {
        return $this->publicationCollaborationInterne;
    }

    public function setPublicationCollaborationInterne(?int $publicationCollaborationInterne): static
    {
        $this->publicationCollaborationInterne = $publicationCollaborationInterne;

        return $this;
    }

    public function getPublicationCollaborationExterne(): ?int
    {
        return $this->publicationCollaborationExterne;
    }

    public function setPublicationCollaborationExterne(?int $publicationCollaborationExterne): static
    {
        $this->publicationCollaborationExterne = $publicationCollaborationExterne;

        return $this;
    }

    public function getPublicationAutreType(): ?int
    {
        return $this->publicationAutreType;
    }

    public function setPublicationAutreType(?int $publicationAutreType): static
    {
        $this->publicationAutreType = $publicationAutreType;

        return $this;
    }

    public function getPublicationThese(): ?int
    {
        return $this->publicationThese;
    }

    public function setPublicationThese(?int $publicationThese): static
    {
        $this->publicationThese = $publicationThese;

        return $this;
    }

    /**
     * @return Collection<int, PlateformeHistoriqueTutelle>
     */
    public function getPlateformeHistoriqueTutelles(): Collection
    {
        return $this->plateformeHistoriqueTutelles;
    }

    public function addPlateformeHistoriqueTutelle(PlateformeHistoriqueTutelle $plateformeHistoriqueTutelle): static
    {
        if (!$this->plateformeHistoriqueTutelles->contains($plateformeHistoriqueTutelle)) {
            $this->plateformeHistoriqueTutelles->add($plateformeHistoriqueTutelle);
            $plateformeHistoriqueTutelle->setPlateformeHistorique($this);
        }

        return $this;
    }

    public function removePlateformeHistoriqueTutelle(PlateformeHistoriqueTutelle $plateformeHistoriqueTutelle): static
    {
        if ($this->plateformeHistoriqueTutelles->removeElement($plateformeHistoriqueTutelle)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistoriqueTutelle->getPlateformeHistorique() === $this) {
                $plateformeHistoriqueTutelle->setPlateformeHistorique(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PlateformeHistoriquePersonnel>
     */
    public function getPlateformeHistoriquePersonnels(): Collection
    {
        return $this->plateformeHistoriquePersonnels;
    }

    public function addPlateformeHistoriquePersonnel(PlateformeHistoriquePersonnel $plateformeHistoriquePersonnel): static
    {
        if (!$this->plateformeHistoriquePersonnels->contains($plateformeHistoriquePersonnel)) {
            $this->plateformeHistoriquePersonnels->add($plateformeHistoriquePersonnel);
            $plateformeHistoriquePersonnel->setPlateformeHistorique($this);
        }

        return $this;
    }

    public function removePlateformeHistoriquePersonnel(PlateformeHistoriquePersonnel $plateformeHistoriquePersonnel): static
    {
        if ($this->plateformeHistoriquePersonnels->removeElement($plateformeHistoriquePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistoriquePersonnel->getPlateformeHistorique() === $this) {
                $plateformeHistoriquePersonnel->setPlateformeHistorique(null);
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
            $posteFutur->setPlateformeHistorique($this);
        }

        return $this;
    }

    public function removePosteFutur(PosteFutur $posteFutur): static
    {
        if ($this->posteFuturs->removeElement($posteFutur)) {
            // set the owning side to null (unless already changed)
            if ($posteFutur->getPlateformeHistorique() === $this) {
                $posteFutur->setPlateformeHistorique(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ContributionTutelle>
     */
    public function getContributionTutelles(): Collection
    {
        return $this->contributionTutelles;
    }

    public function addContributionTutelle(ContributionTutelle $contributionTutelle): static
    {
        if (!$this->contributionTutelles->contains($contributionTutelle)) {
            $this->contributionTutelles->add($contributionTutelle);
            $contributionTutelle->setPlateformeHistorique($this);
        }

        return $this;
    }

    public function removeContributionTutelle(ContributionTutelle $contributionTutelle): static
    {
        if ($this->contributionTutelles->removeElement($contributionTutelle)) {
            // set the owning side to null (unless already changed)
            if ($contributionTutelle->getPlateformeHistorique() === $this) {
                $contributionTutelle->setPlateformeHistorique(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Instrument>
     */
    public function getInstruments(): Collection
    {
        return $this->instruments;
    }

    public function addInstrument(Instrument $instrument): static
    {
        if (!$this->instruments->contains($instrument)) {
            $this->instruments->add($instrument);
            $instrument->setPlateformeHistorique($this);
        }

        return $this;
    }

    public function removeInstrument(Instrument $instrument): static
    {
        if ($this->instruments->removeElement($instrument)) {
            // set the owning side to null (unless already changed)
            if ($instrument->getPlateformeHistorique() === $this) {
                $instrument->setPlateformeHistorique(null);
            }
        }

        return $this;
    }

    public function isBrouillon(): ?bool
    {
        return $this->brouillon;
    }

    public function setBrouillon(bool $brouillon): static
    {
        $this->brouillon = $brouillon;

        return $this;
    }
}
