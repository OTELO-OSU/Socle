<?php

namespace App\DTO;

use App\Validator\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Groupes :
 ** null : Seule le nom_officiel est obligatoire
 ** instrument_national : Instrument national, qui va ajouter l'obligation de renseigner la dotation_instrument_national
 ** validation : Le groupe de validation sert à vérifier que toutes les infos minimum nécessaires sont là
 ** post_validation : Le groupe doit être executé pour la run de validation, après la run de validation classique, il sert à vérifier les règles de validation qui nécessitent que plusieurs champs soient présents (ex: somme des pourcentages égale à 100%)
 */

class PlateformeHistoriqueDTO
{
    #[Assert\NotBlank(groups: ['brouillon'])]
    public ?int $id = null;

    # Présentation
    ## Principale
    public ?string $pseudo_id = null;

    #[Assert\NotBlank(groups: ['validation', 'brouillon'], message: 'Le nom officiel de la plateforme est requis.')]
    public ?string $nom_officiel = null;

    public ?string $forme_developpee_nom_officiel = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le site web de la plateforme est requis.')]
    public ?string $site_web = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'La ville de la plateforme est requise.')]
    #[EntityExists(entity: 'App\Entity\Ville')]
    public ?int $ville_id = null;

    public ?string $logo_url = null;

    public ?string $autre_type_labellisation = null;

    ## Appartenance
    #[Assert\NotBlank(groups: ['validation'], message: 'Le laboratoire opérateur de la plateforme est requis.')]
    #[EntityExists(entity: 'App\Entity\Entite')]
    public ?int $laboratoire_operateur_id = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Au moins une tutelle principale de la plateforme est requise.')]
    #[Assert\All([
        new EntityExists(entity: 'App\Entity\Entite'),
        new Assert\Type('integer'),
    ])]
    public array $tutelles_principales_ids = [];

    #[Assert\All([
        new Assert\Type('integer'),
        new EntityExists(entity: 'App\Entity\Entite'),
    ])]
    public array $tutelles_secondaires_ids = [];

    #[Assert\NotBlank(groups: ['validation'], message: 'La latitude de la plateforme est requise.')]
    public ?float $latitude = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'La longitude de la plateforme est requise.')]
    public ?float $longitude = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'L\'espace de bureau de la plateforme est requis.')]
    public ?float $espace_bureau = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'L\'espace de laboratoire de la plateforme est requis.')]
    public ?float $espace_laboratoire = null;

    # Personnel
    #[Assert\All([
        new Assert\Collection(
            fields: [
                'utilisateur_id' =>
                new Assert\Required([
                    new Assert\NotBlank(),
                    new Assert\Type('integer'),
                ]),
                'quotite' =>
                new Assert\Required([
                    new Assert\NotNull(message: 'La quotité du personnel est requise.'),
                    new Assert\Type('integer'),
                    new Assert\Range(min: 0, max: 100),
                ])
            ],
            allowExtraFields: true
        )
    ])]
    public ?array $personnels = null;

    #[Assert\All([
        new Assert\Collection(
            fields: [
                'employeur_id' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'L\'employeur du poste futur est requis.'),
                    new Assert\Type('integer'),
                ]),
                'corps_id' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'Le corps du poste futur est requis.'),
                    new Assert\Type('integer'),
                ]),
                'baps_ids' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'Au moins une BAP est requise pour le poste futur.'),
                    new Assert\All([
                        new Assert\Type('integer'),
                    ])
                ]),
                'emploi_types_ids' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'Au moins un emploi type est requis pour le poste futur.'),
                    new Assert\All([
                        new Assert\Type('integer'),
                    ])
                ]),
                'quotite' =>
                new Assert\Required([
                    new Assert\NotNull(message: 'La quotité pour le poste futur est requise.'),
                    new Assert\Type('integer'),
                ]),
                'rang_numerateur' =>
                new Assert\Required([
                    new Assert\NotNull(message: 'Le rang (numérateur) du poste futur est requis.'),
                    new Assert\Type('integer'),
                ]),
                'rang_denominateur' =>
                new Assert\Required([
                    new Assert\NotNull(message: 'Le rang (dénominateur) du poste futur est requis.'),
                    new Assert\Type('integer'),
                ]),
            ]
        )
    ])]
    public ?array $postes_futurs = null;

    # Equipements
    #[Assert\All([
        new Assert\Collection(
            fields: [
                'futur' =>
                new Assert\Required([
                    new Assert\NotNull(message: 'La temporalité de l\'équipement est requise.'),
                    new Assert\Type('boolean'),
                ]),
                'nom' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'Le nom de l\'équipement est requis.'),
                    new Assert\Type('string'),
                ]),
                'marque' =>
                new Assert\Optional([
                    new Assert\Type('string'),
                ]),
                'modele' =>
                new Assert\Optional([
                    new Assert\Type('string'),
                ]),
                'annee_acquisition' =>
                new Assert\Optional([
                    new Assert\Type('integer'),
                ]),
                'cout' =>
                new Assert\Optional([
                    new Assert\NotBlank(message: 'Le coût de l\'équipement est requis.', groups: ['validation']),
                    new Assert\Type('numeric'),
                ]),
                'valeur_remplacement' =>
                new Assert\Optional([
                    new Assert\Type('numeric'),
                ]),
                'duree_utilisation' =>
                new Assert\Optional([
                    new Assert\Type('integer'),
                ]),
                'source_financement' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'La source de financement de l\'équipement est requise.', groups: ['validation']),
                    new Assert\All([
                        new Assert\Collection(
                            fields: [
                                "financeur_id" =>
                                new Assert\Required([
                                    new Assert\NotBlank(message: 'Le financeur de l\'équipement est requis.'),
                                    new Assert\Type('integer'),
                                ]),
                                "taux" =>
                                new Assert\Required([
                                    new Assert\NotBlank(message: 'Le taux de financement de l\'équipement est requis.'),
                                    new Assert\Type('integer'),
                                ])
                            ],
                        )
                    ])
                ]),
                "contrat_maintenance_id" =>
                new Assert\Optional([
                    new Assert\Type('integer'),
                ]),
                "commentaire_nature_contrat" =>
                new Assert\Optional([
                    new Assert\Type('string'),
                ]),
                "fournisseur_contrat" =>
                new Assert\Optional([
                    new Assert\Type('string'),
                ]),
                "mutualise_autre_instrument" =>
                new Assert\Optional([
                    new Assert\Type('boolean'),
                ]),
                "mutualise_site" =>
                new Assert\Optional([
                    new Assert\Type('boolean'),
                ]),
                "mutualise_umr_osu" =>
                new Assert\Optional([
                    new Assert\Type('boolean'),
                ]),
                "mutualise_autre_instrument_ids" =>
                new Assert\Optional([
                    new Assert\All([
                        new Assert\Type('integer'),
                    ])
                ]),
                "cout_maintenance" =>
                new Assert\Optional([
                    new Assert\Type('numeric'),
                ]),
                'operation_id' => [
                    new Assert\Optional([
                        new Assert\Type('integer'),
                    ])
                ],
                'annee_achat_envisagee' => [
                    new Assert\Optional([
                        new Assert\Type('integer'),
                    ])
                ],
            ],
            allowExtraFields: true
        )
    ])]
    public ?array $instruments = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function sourceFinancementEquippementActuelCentPourcent(ExecutionContextInterface $context): void
    {
        if ($this->instruments !== null) {
            foreach ($this->instruments as $index => $instrument) {
                $somme = 0;
                foreach ($instrument['source_financement'] as $source) {
                    $somme += $source['taux'];
                }

                if ($somme !== 100) {
                    $context->buildViolation('La somme des pourcentages de source de financement doit être égale à 100%.')
                        ->atPath("instruments[$index].source_financement")
                        ->addViolation();
                }
            }
        }
    }

    #[Assert\Callback(groups: ['validation'])]
    public function validerChampsInstrumentsSelonTemporalite(ExecutionContextInterface $context): void
    {
        if ($this->instruments === null) {
            return;
        }

        foreach ($this->instruments as $index => $instrument) {
            if (!is_array($instrument)) {
                continue;
            }

            $futur = $instrument['futur'] ?? null;

            if ($futur === false) {
                if (($instrument['annee_acquisition'] ?? null) === null) {
                    $context->buildViolation("L'année d'acquisition de l'équipement est requise.")
                        ->atPath("instruments[$index].annee_acquisition")
                        ->addViolation();
                }

                if (($instrument['valeur_remplacement'] ?? null) === null) {
                    $context->buildViolation("La valeur de remplacement de l'équipement est requise.")
                        ->atPath("instruments[$index].valeur_remplacement")
                        ->addViolation();
                }

                if (($instrument['duree_utilisation'] ?? null) === null) {
                    $context->buildViolation("La durée d'utilisation de l'équipement est requise.")
                        ->atPath("instruments[$index].duree_utilisation")
                        ->addViolation();
                }
            }

            if ($futur === true && ($instrument['annee_achat_envisagee'] ?? null) === null) {
                $context->buildViolation("L'année d'achat envisagée de l'équipement est requise.")
                    ->atPath("instruments[$index].annee_achat_envisagee")
                    ->addViolation();
            }
        }
    }

    # Fonctionnement
    ## Cout
    #[Assert\NotBlank(groups: ['validation'], message: 'Le coût des matériels et consommables de la plateforme est requis.')]
    #[Assert\PositiveOrZero]
    public ?float $materiel_consommable = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le coût des contrats d\'entretien de la plateforme est requis.')]
    #[Assert\PositiveOrZero]
    public ?float $contrat_entretien = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le coût des études et prestations de la plateforme est requis.')]
    #[Assert\PositiveOrZero]
    public ?float $etude_prestation = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le coût des fluides et énergies de la plateforme est requis.')]
    #[Assert\PositiveOrZero]
    public ?float $fluide_energie = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le coût des missions et frais généraux de la plateforme est requis.')]
    #[Assert\PositiveOrZero]
    public ?float $mission_frais_generaux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'La méthode de détermination du tarif de la plateforme est requise.')]
    #[EntityExists(entity: 'App\Entity\DeterminationTarifMethode')]
    public ?int $determination_tarif_plateforme_id = null;

    ## Ressource
    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de contribution des tutelle de la plateforme est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de contribution des tutelle doit être compris entre 0 et 100.')]
    public ?int $contribution_tutelle_taux = null;

    #[Assert\When(
        expression: 'this.contribution_tutelle_taux !== null && this.contribution_tutelle_taux !== 0',
        constraints: [
            new Assert\NotBlank(
                message: 'Le taux de contribution des tutelles est requis.'
            )
        ]
    )]
    #[Assert\All([
        new Assert\Collection(
            fields: [
                'financeur_id' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'La tutelle est requise.'),
                        new Assert\Type('integer'),
                    ])
                ],
                'taux' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'Le taux de contribution de la tutelle est requis.'),
                        new Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de contribution de la tutelle doit être compris entre 0 et 100.'),
                        new Assert\Type('integer'),
                    ])
                ],
            ]
        )
    ])]
    public ?array $contribution_tutelle_detail = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function contributionTutelleEgalCentPourcent(ExecutionContextInterface $context): void
    {
        if ($this->contribution_tutelle_taux !== null && $this->contribution_tutelle_taux !== 0) {
            $somme = 0;

            if ($this->contribution_tutelle_detail !== null) {
                foreach ($this->contribution_tutelle_detail as $detail) {
                    $somme += $detail['taux'];
                }
            }

            if ($somme !== 100) {
                $context->buildViolation('La somme des pourcentages de contribution tutelle doit être égale à 100%.')
                    ->atPath('contribution_tutelle_detail')
                    ->addViolation();
            }
        }
    }

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de contribution du fond public de la plateforme est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de contribution du fond public de la plateforme doit être compris entre 0 et 100.')]
    public ?int $contribution_fond_public_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de contribution du fond privé de la plateforme est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de contribution du fond privé de la plateforme doit être compris entre 0 et 100.')]
    public ?int $contribution_fond_prive_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de contribution du crédit de la plateforme est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de contribution du crédit de la plateforme doit être compris entre 0 et 100.')]
    public ?int $contribution_credit_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de contribution du projet de recherche de la plateforme est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de contribution du projet de recherche de la plateforme doit être compris entre 0 et 100.')]
    public ?int $contribution_projet_recherche_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de contribution des structures PIA de la plateforme est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de contribution des structures PIA de la plateforme doit être compris entre 0 et 100.')]
    public ?int $contribution_structure_pia_taux = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function contributionsEgalCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->contribution_tutelle_taux;
        $somme += $this->contribution_fond_public_taux;
        $somme += $this->contribution_fond_prive_taux;
        $somme += $this->contribution_credit_taux;
        $somme += $this->contribution_projet_recherche_taux;
        $somme += $this->contribution_structure_pia_taux;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de contribution doit être égale à 100%.')
                ->atPath('contribution_structure_pia')
                ->addViolation();
        }
    }

    public ?float $dotation_instrument_national = null;

    # Activités
    ## Accesibilité
    #[Assert\NotNull(groups: ['validation'], message: 'Le statut de labellisation qualité de la plateforme est requis.')]
    public ?bool $labellisation_qualite = null;

    public ?string $nom_labellisation_qualite = null;

    #[Assert\NotNull(groups: ['validation'], message: 'Le statut de procédure transparente de la plateforme est requis.')]
    public ?bool $procedure_transparente_tous = null;

    #[Assert\NotNull(groups: ['validation'], message: 'Le statut de procédure transparente pour les prestations extérieures de la plateforme est requis.')]
    public ?bool $procedure_transparente_prestation_exterieure = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'accueil international de la plateforme est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'accueil international de la plateforme doit être compris entre 0 et 100.')]
    public ?int $accueil_international_taux = null;

    ## Utilisation
    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 24 heures sur 24 est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 24 heures sur 24 doit être compris entre 0 et 100.')]
    public ?int $modele_77_2424_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 12 heures sur 24 est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 12 heures sur 24 doit être compris entre 0 et 100.')]
    public ?int $modele_77_1224_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 24 heures sur 24 est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 24 heures sur 24 doit être compris entre 0 et 100.')]
    public ?int $modele_57_2424_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 12 heures sur 24 est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 12 heures sur 24 doit être compris entre 0 et 100.')]
    public ?int $modele_57_1224_taux = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function ModelesSommeCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->modele_77_2424_taux;
        $somme += $this->modele_77_1224_taux;
        $somme += $this->modele_57_2424_taux;
        $somme += $this->modele_57_1224_taux;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de modèles d\'utilisation doit être égale à 100%.')
                ->atPath('modele_57_1224')
                ->addViolation();
        }
    }

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'utilisation de la plateforme en fonctionnement est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'utilisation de la plateforme en fonctionnement doit être compris entre 0 et 100.')]
    public ?int $utilisation_fonctionnement_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'utilisation de la plateforme en maintenance est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'utilisation de la plateforme en maintenance doit être compris entre 0 et 100.')]
    public ?int $utilisation_maintenance_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'utilisation de la plateforme en développement est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'utilisation de la plateforme en développement doit être compris entre 0 et 100.')]
    public ?int $utilisation_developpement_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'utilisation de la plateforme en panne est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'utilisation de la plateforme en panne doit être compris entre 0 et 100.')]
    public ?int $utilisation_panne_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'utilisation de la plateforme en temps mort est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'utilisation de la plateforme en temps mort doit être compris entre 0 et 100.')]
    public ?int $utilisation_temps_mort_taux = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function TauxUtilisationPlateformeSommeCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->utilisation_fonctionnement_taux;
        $somme += $this->utilisation_maintenance_taux;
        $somme += $this->utilisation_developpement_taux;
        $somme += $this->utilisation_panne_taux;
        $somme += $this->utilisation_temps_mort_taux;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de taux d\'utilisation doit être égale à 100%.')
                ->atPath('utilisation_temps_mort_taux')
                ->addViolation();
        }
    }

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'utilisation de la plateforme par l\'équipe opérante est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'utilisation de la plateforme par l\'équipe opérante doit être compris entre 0 et 100.')]
    public ?int $utilisation_equipe_operante_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'utilisation de la plateforme par la collaboration interne est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'utilisation de la plateforme par la collaboration interne doit être compris entre 0 et 100.')]
    public ?int $utilisation_collaboration_interne_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'utilisation de la plateforme par la collaboration externe est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'utilisation de la plateforme par la collaboration externe doit être compris entre 0 et 100.')]
    public ?int $utilisation_collaboration_externe_taux = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le taux d\'utilisation de la plateforme par la prestation de service est requis.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux d\'utilisation de la plateforme par la prestation de service doit être compris entre 0 et 100.')]
    public ?int $utilisation_prestation_service_taux = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function TauxUtilisationDetaillePlateformeSommeCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->utilisation_equipe_operante_taux;
        $somme += $this->utilisation_collaboration_interne_taux;
        $somme += $this->utilisation_collaboration_externe_taux;
        $somme += $this->utilisation_prestation_service_taux;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de taux d\'utilisation doit être égale à 100%.')
                ->atPath('utilisation_prestation_service_taux')
                ->addViolation();
        }
    }

    ## Empreinte Carbone
    #[Assert\NotNull(groups: ['validation'], message: 'Le statut de réalisation du bilan carbone global de la plateforme est requis.')]
    public ?bool $bges_realise = null;

    #[Assert\When(
        expression: 'this.bges_realise === true',
        constraints: [
            new Assert\NotBlank(message: 'Le bilan carbone domicile-travail de la plateforme est requis si le bilan carbone global a été réalisé.')
        ],
        groups: ['validation']
    )]
    #[Assert\PositiveOrZero(message: 'Le bilan carbone domicile-travail de la plateforme doit être supérieur ou égal à 0.')]
    public ?float $bges_domicile_travail = null;

    #[Assert\When(
        expression: 'this.bges_realise === true',
        constraints: [
            new Assert\NotBlank(message: 'Le bilan carbone mission de la plateforme est requis si le bilan carbone global a été réalisé.')
        ],
        groups: ['validation']
    )]
    #[Assert\PositiveOrZero(message: 'Le bilan carbone mission de la plateforme doit être supérieur ou égal à 0.')]
    public ?float $bges_mission = null;

    #[Assert\When(
        expression: 'this.bges_realise === true',
        constraints: [
            new Assert\NotBlank(message: 'Le bilan carbone mission externe de la plateforme est requis si le bilan carbone global a été réalisé.')
        ],
        groups: ['validation']
    )]
    #[Assert\PositiveOrZero(message: 'Le bilan carbone mission externe de la plateforme doit être supérieur ou égal à 0.')]
    public ?float $bges_mission_ext = null;

    #[Assert\When(
        expression: 'this.bges_realise === true',
        constraints: [
            new Assert\NotBlank(message: 'Le bilan carbone consommable de la plateforme est requis si le bilan carbone global a été réalisé.')
        ],
        groups: ['validation']
    )]
    #[Assert\PositiveOrZero(message: 'Le bilan carbone consommable de la plateforme doit être supérieur ou égal à 0.')]
    public ?float $bges_consommable = null;

    #[Assert\When(
        expression: 'this.bges_realise === true',
        constraints: [
            new Assert\NotBlank(message: 'Le bilan carbone équipement de la plateforme est requis si le bilan carbone global a été réalisé.')
        ],
        groups: ['validation']
    )]
    #[Assert\PositiveOrZero(message: 'Le bilan carbone équipement de la plateforme doit être supérieur ou égal à 0.')]
    public ?float $bges_equipement = null;

    #[Assert\When(
        expression: 'this.bges_realise === true',
        constraints: [
            new Assert\NotBlank(message: 'Le bilan carbone envoi d\'échantillon de la plateforme est requis si le bilan carbone global a été réalisé.')
        ],
        groups: ['validation']
    )]
    #[Assert\PositiveOrZero(message: 'Le bilan carbone envoi d\'échantillon de la plateforme doit être supérieur ou égal à 0.')]
    public ?float $bges_envoi_echantillon = null;

    #[Assert\When(
        expression: 'this.bges_realise === true',
        constraints: [
            new Assert\NotBlank(message: 'Le bilan carbone bâtiment de la plateforme est requis si le bilan carbone global a été réalisé.')
        ],
        groups: ['validation']
    )]
    #[Assert\PositiveOrZero(message: 'Le bilan carbone bâtiment de la plateforme doit être supérieur ou égal à 0.')]
    public ?float $bges_batiment = null;

    #[Assert\When(
        expression: 'this.bges_realise === true',
        constraints: [
            new Assert\NotBlank(message: 'Le bilan carbone déchet de la plateforme est requis si le bilan carbone global a été réalisé.')
        ],
        groups: ['validation']
    )]
    #[Assert\PositiveOrZero(message: 'Le bilan carbone déchet de la plateforme doit être supérieur ou égal à 0.')]
    public ?float $bges_dechet = null;

    # Publication
    #[Assert\NotBlank(groups: ['validation'], message: 'Le nombre de publications de l\'équipe opérante de la plateforme est requis.')]
    #[Assert\PositiveOrZero(message: 'Le nombre de publications de l\'équipe opérante de la plateforme doit être supérieur ou égal à 0.')]
    public ?int $publication_equipe_operante = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le nombre de publications de collaboration interne de la plateforme est requis.')]
    #[Assert\PositiveOrZero(message: 'Le nombre de publications de collaboration interne de la plateforme doit être supérieur ou égal à 0.')]
    public ?int $publication_collaboration_interne = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le nombre de publications de collaboration externe de la plateforme est requis.')]
    #[Assert\PositiveOrZero(message: 'Le nombre de publications de collaboration externe de la plateforme doit être supérieur ou égal à 0.')]
    public ?int $publication_collaboration_externe = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le nombre d\'autre type de publication de la plateforme est requis.')]
    #[Assert\PositiveOrZero(message: 'Le nombre d\'autre type de publication de la plateforme doit être supérieur ou égal à 0.')]
    public ?int $publication_autre_type = null;

    #[Assert\NotBlank(groups: ['validation'], message: 'Le nombre de publications de thèse de la plateforme est requis.')]
    #[Assert\PositiveOrZero(message: 'Le nombre de publications de thèse de la plateforme doit être supérieur ou égal à 0.')]
    public ?int $publication_these = null;

    #[Assert\All([
        new Assert\Collection(
            fields: [
                'numero' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'Le numéro de publication est requis.'),
                        new Assert\Type('integer'),
                    ])
                ],
                'doi' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'Le DOI de publication est requis.'),
                    ])
                ]
            ],
            allowExtraFields: true
        )
    ])]
    public array $publications = [];
}
