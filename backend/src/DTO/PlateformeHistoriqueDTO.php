<?php

namespace App\DTO;

use App\Validator\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;
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
    public array $tutelle_principale = [];

    #[Assert\All([
        new Assert\Type('integer'),
        new EntityExists(entity: 'App\Entity\Entite'),
    ])]
    public array $tutelle_secondaire = [];

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
                'responsable_scientifique' =>
                new Assert\Required([
                    new Assert\NotNull(),
                    new Assert\Type('boolean'),
                ]),
                'responsable_technique' =>
                new Assert\Required([
                    new Assert\NotNull(),
                    new Assert\Type('boolean'),
                ]),
                'type_contrat_id' =>
                new Assert\Required([
                    new Assert\NotNull(message: 'Le type de contrat du personnel est requis.'),
                    new Assert\Type('integer'),
                ]),
                'quotite' =>
                new Assert\Required([
                    new Assert\NotNull(message: 'La quotité du personnel est requise.'),
                    new Assert\Type('integer'),
                    new Assert\Range(min: 0, max: 100),
                ])

            ]
        )
    ])]
    public ?array $personnel = null;

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
                'bap' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'Au moins une BAP est requise pour le poste futur.'),
                    new Assert\All([
                        new Assert\Type('integer'),
                    ])
                ]),
                'emploi_type' =>
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
                new Assert\Required([
                    new Assert\NotBlank(message: 'L\'année d\'acquisition de l\'équipement est requise.', groups: ['validation']),
                    new Assert\Type('string'),
                ]),
                'cout' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'Le coût de l\'équipement est requis.', groups: ['validation']),
                    new Assert\Type('float'),
                ]),
                'valeur_remplacement' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'La valeur de remplacement de l\'équipement est requise.', groups: ['validation']),
                    new Assert\Type('float'),
                ]),
                'duree_utilisation' =>
                new Assert\Required([
                    new Assert\NotBlank(message: 'La durée d\'utilisation de l\'équipement est requise.', groups: ['validation']),
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
                "mutualisation_autre_instrument" =>
                new Assert\Optional([
                    new Assert\Type('boolean'),
                ]),
                "mutualisation_site" =>
                new Assert\Optional([
                    new Assert\Type('boolean'),
                ]),
                "mutualisation_umr_osu" =>
                new Assert\Optional([
                    new Assert\Type('boolean'),
                ]),
                "mutualisation_autre_instrument_detaille" =>
                new Assert\Optional([
                    new Assert\All([
                        new Assert\Type('integer'),
                    ])
                ]),
                "cout_maintenance" =>
                new Assert\Optional([
                    new Assert\Type('float'),
                ])
            ]
        )
    ])]
    public ?array $instrument_actuel = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function sourceFinancementEquippementActuelCentPourcent(ExecutionContextInterface $context): void
    {
        if ($this->instrument_actuel !== null) {
            foreach ($this->instrument_actuel as $index => $instrument) {
                $somme = 0;
                foreach ($instrument['source_financement'] as $source) {
                    $somme += $source['taux'];
                }

                if ($somme !== 100) {
                    $context->buildViolation('La somme des pourcentages de source de financement doit être égale à 100%.')
                        ->atPath("instrument_actuel[$index].source_financement")
                        ->addViolation();
                }
            }
        }
    }

    #[Assert\All([
        new Assert\Collection(
            fields: [
                'nom' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'Le nom de l\'équipement est requis.'),
                        new Assert\Type('string'),
                    ])
                ],
                'marque' => [
                    new Assert\Optional([
                        new Assert\Type('string'),
                    ])
                ],
                'modele' => [
                    new Assert\Optional([
                        new Assert\Type('string'),
                    ])
                ],
                'operation_id' => [
                    new Assert\Optional([
                        new Assert\Type('integer'),
                    ])
                ],
                'annee_achat_envisagee' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'L\'année d\'achat envisagée de l\'équipement est requise.', groups: ['validation']),
                        new Assert\Type('integer'),
                    ])
                ],
                'cout' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'Le coût de l\'équipement est requis.', groups: ['validation']),
                        new Assert\Type('float'),
                    ])
                ],
                'source_financement' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'La source de financement de l\'équipement est requise.', groups: ['validation']),
                        new Assert\All([
                            new Assert\Collection(
                                fields: [
                                    "financeur_id" => [
                                        new Assert\Required([
                                            new Assert\NotBlank(message: 'Le financeur de l\'équipement est requis.'),
                                            new Assert\Type('integer'),
                                        ])
                                    ],
                                    "taux" => [
                                        new Assert\Required([
                                            new Assert\NotBlank(message: 'Le taux de financement de l\'équipement est requis.'),
                                            new Assert\Type('integer'),
                                        ])
                                    ],
                                ]
                            )
                        ])
                    ])
                ],
            ]
        )
    ])]
    public ?array $instrument_futur = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function sourceFinancementEquippementFuturCentPourcent(ExecutionContextInterface $context): void
    {
        if ($this->instrument_futur !== null) {
            foreach ($this->instrument_futur as $index => $instrument) {
                $somme = 0;
                foreach ($instrument['source_financement'] as $source) {
                    $somme += $source['taux'];
                }

                if ($somme !== 100) {
                    $context->buildViolation('La somme des pourcentages de source de financement doit être égale à 100%.')
                        ->atPath("instrument_futur[$index].source_financement")
                        ->addViolation();
                }
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

    #[Assert\All([
        new Assert\Collection(
            fields: [
                'tutelle_id' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'L\'ID de la tutelle est requis.'),
                        new Assert\Type('integer'),
                    ])
                ],
                'taux' => [
                    new Assert\Required([
                        new Assert\NotBlank(message: 'Le taux de contribution est requis.'),
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

    #[Assert\NotBlank(groups: ['instrument_national'])]
    public ?float $dotation_instrument_national = null;

    # Activités
    ## Accesibilité
    #[Assert\NotNull(groups: ['validation'])]
    public ?bool $labellisation_qualite = null;

    public ?string $nom_labelisation_qualite = null;

    #[Assert\NotNull(groups: ['validation'])]
    public ?bool $procedure_transparente_tous = null;

    #[Assert\NotNull(groups: ['validation'])]
    public ?bool $procedure_transparente_prestation_exterieure = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $accueil_international_taux = null;

    ## Utilisation
    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $modele_77_2424_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $modele_77_1224_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $modele_57_2424_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
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

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $utilisation_fonctionnement_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $utilisation_maintenance_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $utilisation_developemment_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $utilisation_panne_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $utilisation_temps_mort_taux = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function TauxUtilisationPlateformeSommeCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->utilisation_fonctionnement_taux;
        $somme += $this->utilisation_maintenance_taux;
        $somme += $this->utilisation_developemment_taux;
        $somme += $this->utilisation_panne_taux;
        $somme += $this->utilisation_temps_mort_taux;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de taux d\'utilisation doit être égale à 100%.')
                ->atPath('utilisation_temps_mort_taux')
                ->addViolation();
        }
    }

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $utilisation_equipe_operante_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $utilisation_collaboration_interne_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $utilisation_collaboration_externe_taux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $utilisation_prestation_service_taux = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function TauxUtilisationDetaillePlateformeSommeCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->utilisation_fonctionnement_taux;
        $somme += $this->utilisation_maintenance_taux;
        $somme += $this->utilisation_developemment_taux;
        $somme += $this->utilisation_panne_taux;
        $somme += $this->utilisation_temps_mort_taux;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de taux d\'utilisation doit être égale à 100%.')
                ->atPath('utilisation_prestation_service_taux')
                ->addViolation();
        }
    }

    ## Empreinte Carbone
    #[Assert\NotNull(groups: ['validation'])]
    public ?bool $bges_realise = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?float $bges_domicile_travail = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?float $bges_mission = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?float $bges_mission_ext = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?float $bges_consommable = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?float $bges_equipement = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?float $bges_envoi_echantillon = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?float $bges_batiment = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?float $bges_dechet = null;

    # Publication
    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?int $publication_equipe_operante = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?int $publication_collaboration_interne = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?int $publication_collaboration_externe = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?int $publication_autre_type = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero()]
    public ?int $publication_these = null;
}
