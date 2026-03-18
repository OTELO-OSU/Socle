<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PlateformeHistoriqueDTO
{
    public int $id;
    public int $plateforme_id;

    # Présentation
    ## Principale
    public string $pseudo_id;
    public string $nom_officiel;
    public string $forme_developpee_nom_officiel;
    public string $site_web;
    public int $ville_id;
    public string $logo_url;
    public string $autre_type_labellisation;

    ## Appartenance
    public int $laboratoire_operateur_id;
    public array $tutelle_principale;
    public array $tutelle_secondaire;
    public float $latitude;
    public float $longitude;
    public float $espace_bureau;
    public float $espace_laboratoire;

    # Personnel
    public array $responsable_scientifique;
    public array $responsable_technique;
    public array $personnel;
    public array $postes_futurs;

    # Equipements
    public array $instrument_actuel;
    public array $instrument_futur;

    # Fonctionnement
    ## Cout
    public float $materiel_consommable;
    public float $contrat_entretien;
    public float $etude_prestation;
    public float $fluide_energie;
    public float $mission_frais_generaux;
    public int $determination_tarif_plateforme_id;

    ## Ressource
    public int $contribution_tutelle_pourcentage;
    public array $contribution_tutelle_detail;
    public int $contribution_fond_public;
    public int $contribution_fond_prive;
    public int $contribution_credit;
    public int $contribution_projet_recherche;
    public int $contribution_structure_pia;
    public int $dotation_instrument_national;

    # Activités
    ## Accesibilité
    public bool $labellisation_qualite;
    public string $nom_labelisation_qualite;
    public bool $procedure_transparente_tous;
    public bool $procedure_transparente_prestation_exterieure;
    public int $pourcentage_accueil_international;

    ## Utilisation
    public int $modele_77_2424;
    public int $modele_77_1224;
    public int $modele_57_2424;
    public int $modele_57_1224;
    public int $taux_utilisation_fonctionnement;
    public int $taux_utilisation_maintenance;
    public int $taux_utilisation_developemment;
    public int $taux_utilisation_panne;
    public int $taux_utilisation_temps_mort;
    public int $taux_utilisation_equipe_operante;
    public int $taux_utilisation_collaboration_interne;
    public int $taux_utilisation_collaboration_externe;
    public int $taux_utilisation_prestation_service;

    ## Empreinte Carbone
    public bool $bges_realise;
    public float $bges_domicile_travail;
    public float $bges_mission;
    public float $bges_mission_ext;
    public float $bges_consommable;
    public float $bges_equipement;
    public float $bges_envoi_echantillon;
    public float $bges_batiment;
    public float $bges_dechet;

    # Publication
    public int $publication_equipe_operante;
    public int $publication_collaboration_interne;
    public int $publication_collaboration_externe;
    public int $publication_autre_type;
    public int $publication_these;
}
