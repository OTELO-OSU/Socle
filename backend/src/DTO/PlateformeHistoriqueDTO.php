<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/** Les arguments obligatoires sont :
- nom_officiel
 */
class PlateformeHistoriqueDTO
{
    public ?int $id = null;
    public ?int $plateforme_id = null;

    # Présentation
    ## Principale
    public ?string $pseudo_id = null;
    public string $nom_officiel;
    public ?string $forme_developpee_nom_officiel = null;
    public ?string $site_web = null;
    public ?int $ville_id = null;
    public ?string $logo_url = null;
    public ?string $autre_type_labellisation = null;

    ## Appartenance
    public ?int $laboratoire_operateur_id = null;
    public ?array $tutelle_principale = null;
    public ?array $tutelle_secondaire = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?float $espace_bureau = null;
    public ?float $espace_laboratoire = null;

    # Personnel
    public ?array $responsable_scientifique = null;
    public ?array $responsable_technique = null;
    public ?array $personnel = null;
    public ?array $postes_futurs = null;

    # Equipements
    public ?array $instrument_actuel = null;
    public ?array $instrument_futur = null;

    # Fonctionnement
    ## Cout
    public ?float $materiel_consommable = null;
    public ?float $contrat_entretien = null;
    public ?float $etude_prestation = null;
    public ?float $fluide_energie = null;
    public ?float $mission_frais_generaux = null;
    public ?int $determination_tarif_plateforme_id = null;

    ## Ressource
    public ?int $contribution_tutelle_pourcentage = null;
    public ?array $contribution_tutelle_detail = null;
    public ?int $contribution_fond_public = null;
    public ?int $contribution_fond_prive = null;
    public ?int $contribution_credit = null;
    public ?int $contribution_projet_recherche = null;
    public ?int $contribution_structure_pia = null;
    public ?int $dotation_instrument_national = null;

    # Activités
    ## Accesibilité
    public ?bool $labellisation_qualite = null;
    public ?string $nom_labelisation_qualite = null;
    public ?bool $procedure_transparente_tous = null;
    public ?bool $procedure_transparente_prestation_exterieure = null;
    public ?int $pourcentage_accueil_international = null;

    ## Utilisation
    public ?int $modele_77_2424 = null;
    public ?int $modele_77_1224 = null;
    public ?int $modele_57_2424 = null;
    public ?int $modele_57_1224 = null;
    public ?int $taux_utilisation_fonctionnement = null;
    public ?int $taux_utilisation_maintenance = null;
    public ?int $taux_utilisation_developemment = null;
    public ?int $taux_utilisation_panne = null;
    public ?int $taux_utilisation_temps_mort = null;
    public ?int $taux_utilisation_equipe_operante = null;
    public ?int $taux_utilisation_collaboration_interne = null;
    public ?int $taux_utilisation_collaboration_externe = null;
    public ?int $taux_utilisation_prestation_service = null;

    ## Empreinte Carbone
    public ?bool $bges_realise = null;
    public ?float $bges_domicile_travail = null;
    public ?float $bges_mission = null;
    public ?float $bges_mission_ext = null;
    public ?float $bges_consommable = null;
    public ?float $bges_equipement = null;
    public ?float $bges_envoi_echantillon = null;
    public ?float $bges_batiment = null;
    public ?float $bges_dechet = null;

    # Publication
    public ?int $publication_equipe_operante = null;
    public ?int $publication_collaboration_interne = null;
    public ?int $publication_collaboration_externe = null;
    public ?int $publication_autre_type = null;
    public ?int $publication_these = null;
}
