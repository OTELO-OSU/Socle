<?php

namespace App\Entity;

use App\Repository\PlateformeHistoriqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlateformeHistoriqueRepository::class)]
class PlateformeHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plateforme $plateforme = null;

    #[ORM\Column]
    private ?int $annee = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_officiel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $forme_developpee_nom_officiel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $site_web = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    private ?Ville $ville = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $logo_url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $autre_type_labellisation = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    private ?Entite $laboratoire_operateur = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $espace_bureau = null;

    /**
     * @var Collection<int, PlateformeHistoriquePersonnel>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistoriquePersonnel::class, mappedBy: 'plateforme_historique_id')]
    private Collection $plateformeHistoriquePersonnels;

    #[ORM\Column(nullable: true)]
    private ?float $espace_laboratoire = null;

    #[ORM\Column(nullable: true)]
    private ?float $materiel_consommable = null;

    #[ORM\Column(nullable: true)]
    private ?float $contrat_entretien = null;

    #[ORM\Column(nullable: true)]
    private ?float $etude_prestation = null;

    #[ORM\Column(nullable: true)]
    private ?float $fluide_energie = null;

    #[ORM\Column(nullable: true)]
    private ?float $mission_frais_generaux = null;

    #[ORM\ManyToOne(inversedBy: 'plateformeHistoriques')]
    private ?DeterminationTarifMethode $determination_tarif_plateforme = null;

    #[ORM\Column(nullable: true)]
    private ?int $contribution_tutelle_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contribution_fond_public_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contribution_fond_prive_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contribution_credit_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contribution_projet_recherche_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $contribution_structure_pia_taux = null;

    #[ORM\Column(nullable: true)]
    private ?float $dotation_instrument_national = null;

    #[ORM\Column(nullable: true)]
    private ?bool $labellisation_qualite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom_labellisation_qualite = null;

    #[ORM\Column(nullable: true)]
    private ?bool $procedure_transparente_tous = null;

    #[ORM\Column(nullable: true)]
    private ?bool $procedure_transparente_prestation_exterieure = null;

    #[ORM\Column(nullable: true)]
    private ?int $accueil_international_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $modele_77_2424_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $modele_77_1224_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $modele_57_2424_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $modele_57_1224_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisation_fonctionnement_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisation_maintenance_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisation_developpement_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisation_panne_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisation_temps_mort_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisation_equipe_operante_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisation_collaboration_interne_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisation_collaboration_externe_taux = null;

    #[ORM\Column(nullable: true)]
    private ?int $utilisation_prestation_service_taux = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bges_realise = null;

    #[ORM\Column(nullable: true)]
    private ?float $bges_domicile_travail = null;

    #[ORM\Column(nullable: true)]
    private ?float $bges_mission = null;

    #[ORM\Column(nullable: true)]
    private ?float $bges_mission_ext = null;

    #[ORM\Column(nullable: true)]
    private ?float $bges_consommable = null;

    #[ORM\Column(nullable: true)]
    private ?float $bges_equipement = null;

    #[ORM\Column(nullable: true)]
    private ?float $bges_envoi_echantillon = null;

    #[ORM\Column(nullable: true)]
    private ?float $bges_batiment = null;

    #[ORM\Column(nullable: true)]
    private ?float $bges_dechet = null;

    #[ORM\Column(nullable: true)]
    private ?int $publication_equipe_operante = null;

    #[ORM\Column(nullable: true)]
    private ?int $publication_collaboration_interne = null;

    #[ORM\Column(nullable: true)]
    private ?int $publication_collaboration_externe = null;

    #[ORM\Column(nullable: true)]
    private ?int $publication_autre_type = null;

    #[ORM\Column(nullable: true)]
    private ?int $publication_these = null;

    /**
     * @var Collection<int, PlateformeHistoriqueTutelle>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistoriqueTutelle::class, mappedBy: 'plateforme_historique_id')]
    private Collection $plateformeHistoriqueTutelles;

    /**
     * @var Collection<int, PosteFutur>
     */
    #[ORM\OneToMany(targetEntity: PosteFutur::class, mappedBy: 'plateforme_historique')]
    private Collection $posteFuturs;

    /**
     * @var Collection<int, ContributionTutelle>
     */
    #[ORM\OneToMany(targetEntity: ContributionTutelle::class, mappedBy: 'plateforme_historique')]
    private Collection $contributionTutelles;

    /**
     * @var Collection<int, Instrument>
     */
    #[ORM\OneToMany(targetEntity: Instrument::class, mappedBy: 'plateforme_historique')]
    private Collection $instruments;

    #[ORM\Column]
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
        return $this->nom_officiel;
    }

    public function setNomOfficiel(string $nom_officiel): static
    {
        $this->nom_officiel = $nom_officiel;

        return $this;
    }

    public function getFormeDeveloppeeNomOfficiel(): ?string
    {
        return $this->forme_developpee_nom_officiel;
    }

    public function setFormeDeveloppeeNomOfficiel(?string $forme_developpee_nom_officiel): static
    {
        $this->forme_developpee_nom_officiel = $forme_developpee_nom_officiel;

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->site_web;
    }

    public function setSiteWeb(?string $site_web): static
    {
        $this->site_web = $site_web;

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
        return $this->logo_url;
    }

    public function setLogoUrl(?string $logo_url): static
    {
        $this->logo_url = $logo_url;

        return $this;
    }

    public function getAutreTypeLabellisation(): ?string
    {
        return $this->autre_type_labellisation;
    }

    public function setAutreTypeLabellisation(?string $autre_type_labellisation): static
    {
        $this->autre_type_labellisation = $autre_type_labellisation;

        return $this;
    }

    public function getLaboratoireOperateur(): ?Entite
    {
        return $this->laboratoire_operateur;
    }

    public function setLaboratoireOperateur(?Entite $laboratoire_operateur): static
    {
        $this->laboratoire_operateur = $laboratoire_operateur;

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
        return $this->espace_bureau;
    }

    public function setEspaceBureau(?float $espace_bureau): static
    {
        $this->espace_bureau = $espace_bureau;

        return $this;
    }

    public function getEspaceLaboratoire(): ?float
    {
        return $this->espace_laboratoire;
    }

    public function setEspaceLaboratoire(?float $espace_laboratoire): static
    {
        $this->espace_laboratoire = $espace_laboratoire;

        return $this;
    }

    public function getMaterielConsommable(): ?float
    {
        return $this->materiel_consommable;
    }

    public function setMaterielConsommable(?float $materiel_consommable): static
    {
        $this->materiel_consommable = $materiel_consommable;

        return $this;
    }

    public function getContratEntretien(): ?float
    {
        return $this->contrat_entretien;
    }

    public function setContratEntretien(?float $contrat_entretien): static
    {
        $this->contrat_entretien = $contrat_entretien;

        return $this;
    }

    public function getEtudePrestation(): ?float
    {
        return $this->etude_prestation;
    }

    public function setEtudePrestation(?float $etude_prestation): static
    {
        $this->etude_prestation = $etude_prestation;

        return $this;
    }

    public function getFluideEnergie(): ?float
    {
        return $this->fluide_energie;
    }

    public function setFluideEnergie(?float $fluide_energie): static
    {
        $this->fluide_energie = $fluide_energie;

        return $this;
    }

    public function getMissionFraisGeneraux(): ?float
    {
        return $this->mission_frais_generaux;
    }

    public function setMissionFraisGeneraux(?float $mission_frais_generaux): static
    {
        $this->mission_frais_generaux = $mission_frais_generaux;

        return $this;
    }

    public function getDeterminationTarifPlateforme(): ?DeterminationTarifMethode
    {
        return $this->determination_tarif_plateforme;
    }

    public function setDeterminationTarifPlateforme(?DeterminationTarifMethode $determination_tarif_plateforme): static
    {
        $this->determination_tarif_plateforme = $determination_tarif_plateforme;

        return $this;
    }

    public function getContributionTutelleTaux(): ?int
    {
        return $this->contribution_tutelle_taux;
    }

    public function setContributionTutelleTaux(?int $contribution_tutelle_taux): static
    {
        $this->contribution_tutelle_taux = $contribution_tutelle_taux;

        return $this;
    }

    public function getContributionFondPublicTaux(): ?int
    {
        return $this->contribution_fond_public_taux;
    }

    public function setContributionFondPublicTaux(?int $contribution_fond_public_taux): static
    {
        $this->contribution_fond_public_taux = $contribution_fond_public_taux;

        return $this;
    }

    public function getContributionFondPriveTaux(): ?int
    {
        return $this->contribution_fond_prive_taux;
    }

    public function setContributionFondPriveTaux(?int $contribution_fond_prive_taux): static
    {
        $this->contribution_fond_prive_taux = $contribution_fond_prive_taux;

        return $this;
    }

    public function getContributionCreditTaux(): ?int
    {
        return $this->contribution_credit_taux;
    }

    public function setContributionCreditTaux(?int $contribution_credit_taux): static
    {
        $this->contribution_credit_taux = $contribution_credit_taux;

        return $this;
    }

    public function getContributionProjetRechercheTaux(): ?int
    {
        return $this->contribution_projet_recherche_taux;
    }

    public function setContributionProjetRechercheTaux(?int $contribution_projet_recherche_taux): static
    {
        $this->contribution_projet_recherche_taux = $contribution_projet_recherche_taux;

        return $this;
    }

    public function getContributionStructurePiaTaux(): ?int
    {
        return $this->contribution_structure_pia_taux;
    }

    public function setContributionStructurePiaTaux(?int $contribution_structure_pia_taux): static
    {
        $this->contribution_structure_pia_taux = $contribution_structure_pia_taux;

        return $this;
    }

    public function getDotationInstrumentNational(): ?float
    {
        return $this->dotation_instrument_national;
    }

    public function setDotationInstrumentNational(?float $dotation_instrument_national): static
    {
        $this->dotation_instrument_national = $dotation_instrument_national;

        return $this;
    }

    public function isLabellisationQualite(): ?bool
    {
        return $this->labellisation_qualite;
    }

    public function setLabellisationQualite(?bool $labellisation_qualite): static
    {
        $this->labellisation_qualite = $labellisation_qualite;

        return $this;
    }

    public function getNomLabellisationQualite(): ?string
    {
        return $this->nom_labellisation_qualite;
    }

    public function setNomLabellisationQualite(?string $nom_labellisation_qualite): static
    {
        $this->nom_labellisation_qualite = $nom_labellisation_qualite;

        return $this;
    }

    public function isProcedureTransparenteTous(): ?bool
    {
        return $this->procedure_transparente_tous;
    }

    public function setProcedureTransparenteTous(?bool $procedure_transparente_tous): static
    {
        $this->procedure_transparente_tous = $procedure_transparente_tous;

        return $this;
    }

    public function isProcedureTransparentePrestationExterieure(): ?bool
    {
        return $this->procedure_transparente_prestation_exterieure;
    }

    public function setProcedureTransparentePrestationExterieure(?bool $procedure_transparente_prestation_exterieure): static
    {
        $this->procedure_transparente_prestation_exterieure = $procedure_transparente_prestation_exterieure;

        return $this;
    }

    public function getAccueilInternationalTaux(): ?int
    {
        return $this->accueil_international_taux;
    }

    public function setAccueilInternationalTaux(?int $accueil_international_taux): static
    {
        $this->accueil_international_taux = $accueil_international_taux;

        return $this;
    }

    public function getModele772424Taux(): ?int
    {
        return $this->modele_77_2424_taux;
    }

    public function setModele772424Taux(?int $modele_77_2424_taux): static
    {
        $this->modele_77_2424_taux = $modele_77_2424_taux;

        return $this;
    }

    public function getModele771224Taux(): ?int
    {
        return $this->modele_77_1224_taux;
    }

    public function setModele771224Taux(?int $modele_77_1224_taux): static
    {
        $this->modele_77_1224_taux = $modele_77_1224_taux;

        return $this;
    }

    public function getModele572424Taux(): ?int
    {
        return $this->modele_57_2424_taux;
    }

    public function setModele572424Taux(?int $modele_57_2424_taux): static
    {
        $this->modele_57_2424_taux = $modele_57_2424_taux;

        return $this;
    }

    public function getModele571224Taux(): ?int
    {
        return $this->modele_57_1224_taux;
    }

    public function setModele571224Taux(?int $modele_57_1224_taux): static
    {
        $this->modele_57_1224_taux = $modele_57_1224_taux;

        return $this;
    }

    public function getUtilisationFonctionnementTaux(): ?int
    {
        return $this->utilisation_fonctionnement_taux;
    }

    public function setUtilisationFonctionnementTaux(?int $utilisation_fonctionnement_taux): static
    {
        $this->utilisation_fonctionnement_taux = $utilisation_fonctionnement_taux;

        return $this;
    }

    public function getUtilisationMaintenanceTaux(): ?int
    {
        return $this->utilisation_maintenance_taux;
    }

    public function setUtilisationMaintenanceTaux(?int $utilisation_maintenance_taux): static
    {
        $this->utilisation_maintenance_taux = $utilisation_maintenance_taux;

        return $this;
    }

    public function getUtilisationDeveloppementTaux(): ?int
    {
        return $this->utilisation_developpement_taux;
    }

    public function setUtilisationDeveloppementTaux(?int $utilisation_developpement_taux): static
    {
        $this->utilisation_developpement_taux = $utilisation_developpement_taux;

        return $this;
    }

    public function getUtilisationPanneTaux(): ?int
    {
        return $this->utilisation_panne_taux;
    }

    public function setUtilisationPanneTaux(?int $utilisation_panne_taux): static
    {
        $this->utilisation_panne_taux = $utilisation_panne_taux;

        return $this;
    }

    public function getUtilisationTempsMortTaux(): ?int
    {
        return $this->utilisation_temps_mort_taux;
    }

    public function setUtilisationTempsMortTaux(?int $utilisation_temps_mort_taux): static
    {
        $this->utilisation_temps_mort_taux = $utilisation_temps_mort_taux;

        return $this;
    }

    public function getUtilisationEquipeOperanteTaux(): ?int
    {
        return $this->utilisation_equipe_operante_taux;
    }

    public function setUtilisationEquipeOperanteTaux(?int $utilisation_equipe_operante_taux): static
    {
        $this->utilisation_equipe_operante_taux = $utilisation_equipe_operante_taux;

        return $this;
    }

    public function getUtilisationCollaborationInterneTaux(): ?int
    {
        return $this->utilisation_collaboration_interne_taux;
    }

    public function setUtilisationCollaborationInterneTaux(?int $utilisation_collaboration_interne_taux): static
    {
        $this->utilisation_collaboration_interne_taux = $utilisation_collaboration_interne_taux;

        return $this;
    }

    public function getUtilisationCollaborationExterneTaux(): ?int
    {
        return $this->utilisation_collaboration_externe_taux;
    }

    public function setUtilisationCollaborationExterneTaux(?int $utilisation_collaboration_externe_taux): static
    {
        $this->utilisation_collaboration_externe_taux = $utilisation_collaboration_externe_taux;

        return $this;
    }

    public function getUtilisationPrestationServiceTaux(): ?int
    {
        return $this->utilisation_prestation_service_taux;
    }

    public function setUtilisationPrestationServiceTaux(?int $utilisation_prestation_service_taux): static
    {
        $this->utilisation_prestation_service_taux = $utilisation_prestation_service_taux;

        return $this;
    }

    public function isBgesRealise(): ?bool
    {
        return $this->bges_realise;
    }

    public function setBgesRealise(?bool $bges_realise): static
    {
        $this->bges_realise = $bges_realise;

        return $this;
    }

    public function getBgesDomicileTravail(): ?float
    {
        return $this->bges_domicile_travail;
    }

    public function setBgesDomicileTravail(?float $bges_domicile_travail): static
    {
        $this->bges_domicile_travail = $bges_domicile_travail;

        return $this;
    }

    public function getBgesMission(): ?float
    {
        return $this->bges_mission;
    }

    public function setBgesMission(?float $bges_mission): static
    {
        $this->bges_mission = $bges_mission;

        return $this;
    }

    public function getBgesMissionExt(): ?float
    {
        return $this->bges_mission_ext;
    }

    public function setBgesMissionExt(?float $bges_mission_ext): static
    {
        $this->bges_mission_ext = $bges_mission_ext;

        return $this;
    }

    public function getBgesConsommable(): ?float
    {
        return $this->bges_consommable;
    }

    public function setBgesConsommable(?float $bges_consommable): static
    {
        $this->bges_consommable = $bges_consommable;

        return $this;
    }

    public function getBgesEquipement(): ?float
    {
        return $this->bges_equipement;
    }

    public function setBgesEquipement(?float $bges_equipement): static
    {
        $this->bges_equipement = $bges_equipement;

        return $this;
    }

    public function getBgesEnvoiEchantillon(): ?float
    {
        return $this->bges_envoi_echantillon;
    }

    public function setBgesEnvoiEchantillon(?float $bges_envoi_echantillon): static
    {
        $this->bges_envoi_echantillon = $bges_envoi_echantillon;

        return $this;
    }

    public function getBgesBatiment(): ?float
    {
        return $this->bges_batiment;
    }

    public function setBgesBatiment(?float $bges_batiment): static
    {
        $this->bges_batiment = $bges_batiment;

        return $this;
    }

    public function getBgesDechet(): ?float
    {
        return $this->bges_dechet;
    }

    public function setBgesDechet(?float $bges_dechet): static
    {
        $this->bges_dechet = $bges_dechet;

        return $this;
    }

    public function getPublicationEquipeOperante(): ?int
    {
        return $this->publication_equipe_operante;
    }

    public function setPublicationEquipeOperante(?int $publication_equipe_operante): static
    {
        $this->publication_equipe_operante = $publication_equipe_operante;

        return $this;
    }

    public function getPublicationCollaborationInterne(): ?int
    {
        return $this->publication_collaboration_interne;
    }

    public function setPublicationCollaborationInterne(?int $publication_collaboration_interne): static
    {
        $this->publication_collaboration_interne = $publication_collaboration_interne;

        return $this;
    }

    public function getPublicationCollaborationExterne(): ?int
    {
        return $this->publication_collaboration_externe;
    }

    public function setPublicationCollaborationExterne(?int $publication_collaboration_externe): static
    {
        $this->publication_collaboration_externe = $publication_collaboration_externe;

        return $this;
    }

    public function getPublicationAutreType(): ?int
    {
        return $this->publication_autre_type;
    }

    public function setPublicationAutreType(?int $publication_autre_type): static
    {
        $this->publication_autre_type = $publication_autre_type;

        return $this;
    }

    public function getPublicationThese(): ?int
    {
        return $this->publication_these;
    }

    public function setPublicationThese(?int $publication_these): static
    {
        $this->publication_these = $publication_these;

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
