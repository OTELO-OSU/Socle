<?php

namespace App\Entity;

use App\Repository\PlateformeHistoriqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: PlateformeHistoriqueRepository::class)]
class PlateformeHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plateforme:list', "plateforme:detail", 'plateforme:list:publique'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plateforme $plateforme = null;

    #[ORM\Column]
    #[Groups(['plateforme:list', "plateforme:detail"])]
    private ?int $annee = null;

    #[ORM\Column(length: 255)]
    #[SerializedName("nom_officiel")]
    #[Groups(['plateforme:list', "plateforme:detail", 'plateforme:list:publique'])]
    private ?string $nomOfficiel = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[SerializedName("forme_developpee_nom_officiel")]
    #[Groups(['plateforme:list', "plateforme:detail", 'plateforme:list:publique'])]
    private ?string $formeDeveloppeeNomOfficiel = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[SerializedName("site_web")]
    #[Groups(['plateforme:list', "plateforme:detail", 'plateforme:list:publique'])]
    private ?string $siteWeb = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    private ?Ville $ville = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[SerializedName("logo_url")]
    #[Groups(['plateforme:list', "plateforme:detail", 'plateforme:list:publique'])]
    private ?string $logoUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[SerializedName("autre_type_labellisation")]
    #[Groups(["plateforme:detail", 'plateforme:list:publique'])]
    private ?string $autreTypeLabellisation = null;

    #[SerializedName("laboratoire_operateur")]
    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    private ?Entite $laboratoireOperateur = null;

    #[Groups(["plateforme:list", "plateforme:detail", 'plateforme:list:publique'])]
    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[Groups(["plateforme:list", "plateforme:detail", 'plateforme:list:publique'])]
    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[Groups(["plateforme:detail"])]
    #[SerializedName("espace_bureau")]
    #[ORM\Column(nullable: true)]
    private ?float $espaceBureau = null;

    /**
     * @var Collection<int, PlateformeHistoriquePersonnel>
     */
    #[SerializedName("personnels")]
    #[ORM\OneToMany(targetEntity: PlateformeHistoriquePersonnel::class, mappedBy: 'plateformeHistorique')]
    #[Groups(['plateforme:list', "plateforme:detail"])]
    private Collection $plateformeHistoriquePersonnels;

    #[SerializedName("espace_laboratoire")]
    #[ORM\Column(nullable: true)]
    #[Groups(["plateforme:detail"])]
    private ?float $espaceLaboratoire = null;

    #[SerializedName("materiel_consommable")]
    #[ORM\Column(nullable: true)]
    #[Groups(["plateforme:detail"])]
    private ?float $materielConsommable = null;

    #[SerializedName("contrat_entretien")]
    #[ORM\Column(nullable: true)]
    #[Groups(["plateforme:detail"])]
    private ?float $contratEntretien = null;

    #[SerializedName("etude_prestation")]
    #[ORM\Column(nullable: true)]
    #[Groups(["plateforme:detail"])]
    private ?float $etudePrestation = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("fluide_energie")]
    #[Groups(["plateforme:detail"])]
    private ?float $fluideEnergie = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("mission_frais_generaux")]
    #[Groups(["plateforme:detail"])]
    private ?float $missionFraisGeneraux = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    private ?DeterminationTarifMethode $determinationTarifPlateforme = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("contribution_tutelle_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $contributionTutelleTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("contribution_fond_public_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $contributionFondPublicTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("contribution_fond_prive_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $contributionFondPriveTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("contribution_credit_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $contributionCreditTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("contribution_projet_recherche_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $contributionProjetRechercheTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("contribution_structure_pia_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $contributionStructurePiaTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("dotation_instrument_national")]
    #[Groups(["plateforme:detail"])]
    private ?float $dotationInstrumentNational = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("labellisation_qualite")]
    #[Groups(["plateforme:detail"])]
    private ?bool $labellisationQualite = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[SerializedName("nom_labellisation_qualite")]
    #[Groups(["plateforme:detail"])]
    private ?string $nomLabellisationQualite = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("procedure_transparente_tous")]
    #[Groups(["plateforme:detail"])]
    private ?bool $procedureTransparenteTous = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("procedure_transparente_prestation_exterieure")]
    #[Groups(["plateforme:detail"])]
    private ?bool $procedureTransparentePrestationExterieure = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("accueil_international_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $accueilInternationalTaux = null;

    #[ORM\Column(name: 'modele_77_2424_taux', nullable: true)]
    #[SerializedName("modele_77_2424_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $modele772424Taux = null;

    #[ORM\Column(name: 'modele_77_1224_taux', nullable: true)]
    #[SerializedName("modele_77_1224_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $modele771224Taux = null;

    #[ORM\Column(name: 'modele_57_2424_taux', nullable: true)]
    #[SerializedName("modele_57_2424_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $modele572424Taux = null;

    #[ORM\Column(name: 'modele_57_1224_taux', nullable: true)]
    #[SerializedName("modele_57_1224_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $modele571224Taux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("utilisation_fonctionnement_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $utilisationFonctionnementTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("utilisation_maintenance_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $utilisationMaintenanceTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("utilisation_developpement_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $utilisationDeveloppementTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("utilisation_panne_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $utilisationPanneTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("utilisation_temps_mort_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $utilisationTempsMortTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("utilisation_equipe_operante_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $utilisationEquipeOperanteTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("utilisation_collaboration_interne_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $utilisationCollaborationInterneTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("utilisation_collaboration_externe_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $utilisationCollaborationExterneTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("utilisation_prestation_service_taux")]
    #[Groups(["plateforme:detail"])]
    private ?int $utilisationPrestationServiceTaux = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("bges_realise")]
    #[Groups(["plateforme:detail"])]
    private ?bool $bgesRealise = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("bges_domicile_travail")]
    #[Groups(["plateforme:detail"])]
    private ?float $bgesDomicileTravail = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("bges_mission")]
    #[Groups(["plateforme:detail"])]
    private ?float $bgesMission = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("bges_mission_ext")]
    #[Groups(["plateforme:detail"])]
    private ?float $bgesMissionExt = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("bges_consommable")]
    #[Groups(["plateforme:detail"])]
    private ?float $bgesConsommable = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("bges_equipement")]
    #[Groups(["plateforme:detail"])]
    private ?float $bgesEquipement = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("bges_envoi_echantillon")]
    #[Groups(["plateforme:detail"])]
    private ?float $bgesEnvoiEchantillon = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("bges_batiment")]
    #[Groups(["plateforme:detail"])]
    private ?float $bgesBatiment = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("bges_dechet")]
    #[Groups(["plateforme:detail"])]
    private ?float $bgesDechet = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("publication_equipe_operante")]
    #[Groups(["plateforme:detail"])]
    private ?int $publicationEquipeOperante = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("publication_collaboration_interne")]
    #[Groups(["plateforme:detail"])]
    private ?int $publicationCollaborationInterne = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("publication_collaboration_externe")]
    #[Groups(["plateforme:detail"])]
    private ?int $publicationCollaborationExterne = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("publication_autre_type")]
    #[Groups(["plateforme:detail"])]
    private ?int $publicationAutreType = null;

    #[ORM\Column(nullable: true)]
    #[SerializedName("publication_these")]
    #[Groups(["plateforme:detail"])]
    private ?int $publicationThese = null;

    /**
     * @var Collection<int, PlateformeHistoriqueTutelle>
     */
    #[SerializedName("tutelles")]
    #[ORM\OneToMany(targetEntity: PlateformeHistoriqueTutelle::class, mappedBy: 'plateformeHistorique')]
    private Collection $plateformeHistoriqueTutelles;

    /**
     * @var Collection<int, PosteFutur>
     */
    #[SerializedName("postes_futurs")]
    #[ORM\OneToMany(targetEntity: PosteFutur::class, mappedBy: 'plateformeHistorique')]
    #[Groups(["plateforme:detail"])]
    private Collection $posteFuturs;

    /**
     * @var Collection<int, ContributionTutelle>
     */
    #[ORM\OneToMany(targetEntity: ContributionTutelle::class, mappedBy: 'plateformeHistorique')]
    #[Groups(["plateforme:detail"])]
    #[SerializedName("contribution_tutelle_detail")]
    private Collection $contributionTutelles;

    /**
     * @var Collection<int, Instrument>
     */
    #[ORM\OneToMany(targetEntity: Instrument::class, mappedBy: 'plateformeHistorique')]
    #[Groups(["plateforme:detail"])]
    private Collection $instruments;

    #[ORM\Column]
    #[Groups(['plateforme:list'])]
    private ?bool $brouillon = null;

    /**
     * @var Collection<int, Publications>
     */
    #[ORM\OneToMany(targetEntity: Publications::class, mappedBy: 'plateformeHistorique')]
    #[Groups(["plateforme:detail"])]
    #[SerializedName("publications")]
    private Collection $publications;

    #[Groups(["plateforme:detail"])]
    #[SerializedName("nombre_apprentis_alternants")]
    #[ORM\Column(nullable: true)]
    private ?int $nombreApprentisAlternants = null;

    #[Groups(["plateforme:detail"])]
    #[SerializedName("nombre_autres_etudiants")]
    #[ORM\Column(nullable: true)]
    private ?int $nombreAutresEtudiants = null;

    #[Groups(["plateforme:list", "plateforme:detail"])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->plateformeHistoriqueTutelles = new ArrayCollection();
        $this->plateformeHistoriquePersonnels = new ArrayCollection();
        $this->posteFuturs = new ArrayCollection();
        $this->contributionTutelles = new ArrayCollection();
        $this->instruments = new ArrayCollection();
        $this->publications = new ArrayCollection();
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

    #[Groups(['plateforme:detail', 'plateforme:list:publique', 'plateforme:list'])]
    #[SerializedName('ville_id')]
    public function getVilleId(): ?int
    {
        return $this->ville?->getId();
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

    #[Groups(['plateforme:detail'])]
    #[SerializedName('laboratoire_operateur_id')]
    public function getLaboratoireOperateurId(): ?int
    {
        return $this->laboratoireOperateur?->getId();
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

    #[Groups(["plateforme:detail"])]
    #[SerializedName("determination_tarif_plateforme_id")]
    function getDeterminationTarifPlateformeId(): ?int
    {
        return $this->determinationTarifPlateforme?->getId();
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

    #[Groups(['plateforme:detail'])]
    #[SerializedName('tutelles_principales_ids')]
    public function getTutellesPrincipalesIds(): array
    {
        $ids = [];
        foreach ($this->plateformeHistoriqueTutelles as $plateformeHistoriqueTutelle) {
            if ($plateformeHistoriqueTutelle->isPrincipale()) {
                $ids[] = $plateformeHistoriqueTutelle->getTutelle()->getId();
            }
        }
        return $ids;
    }

    #[Groups(['plateforme:detail'])]
    #[SerializedName('tutelles_secondaires_ids')]
    public function getTutellesSecondairesIds(): array
    {
        $ids = [];
        foreach ($this->plateformeHistoriqueTutelles as $plateformeHistoriqueTutelle) {
            if (!$plateformeHistoriqueTutelle->isPrincipale()) {
                $ids[] = $plateformeHistoriqueTutelle->getTutelle()->getId();
            }
        }
        return $ids;
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

    /**
     * @return Collection<int, Publications>
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publications $publication): static
    {
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);
            $publication->setPlateformeHistorique($this);
        }

        return $this;
    }

    public function removePublication(Publications $publication): static
    {
        if ($this->publications->removeElement($publication)) {
            // set the owning side to null (unless already changed)
            if ($publication->getPlateformeHistorique() === $this) {
                $publication->setPlateformeHistorique(null);
            }
        }

        return $this;
    }

    public function getNombreApprentisAlternants(): ?int
    {
        return $this->nombreApprentisAlternants;
    }

    public function setNombreApprentisAlternants(?int $nombreApprentisAlternants): static
    {
        $this->nombreApprentisAlternants = $nombreApprentisAlternants;

        return $this;
    }

    public function getNombreAutresEtudiants(): ?int
    {
        return $this->nombreAutresEtudiants;
    }

    public function setNombreAutresEtudiants(?int $nombreAutresEtudiants): static
    {
        $this->nombreAutresEtudiants = $nombreAutresEtudiants;

        return $this;
    }

    #[Groups(['plateforme:detail'])]
    #[SerializedName('tags_ids')]
    public function getTagsIds(): array
    {
        $ids = [];
        /** @var PlateformeTags $tag */
        foreach ($this->getPlateforme()->getPlateformeTags() as $tag) {
            $ids[] = $tag->getTagId();
        }
        return $ids;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
