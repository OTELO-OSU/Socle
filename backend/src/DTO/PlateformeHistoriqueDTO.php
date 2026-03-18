<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Groupes :
 ** null : Seule le nom_officiel est obligatoire
 ** validation : Le groupe de validation sert à vérifier que toutes les infos minimum nécessaires sont là
 ** post_validation : Le groupe doit être executé pour la run de validation, après la run de validation classique, il sert à vérifier les règles de validation qui nécessitent que plusieurs champs soient présents (ex: somme des pourcentages égale à 100%)
 */

class PlateformeHistoriqueDTO
{
    public ?int $id = null;
    public ?int $plateforme_id = null;

    # Présentation
    ## Principale
    #[Assert\NotBlank(groups: ['validation'])]
    public ?string $pseudo_id = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?string $nom_officiel = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?string $forme_developpee_nom_officiel = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?string $site_web = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?int $ville_id = null;

    public ?string $logo_url = null;

    public ?string $autre_type_labellisation = null;

    ## Appartenance
    #[Assert\NotBlank(groups: ['validation'])]
    public ?int $laboratoire_operateur_id = null;

    #[Assert\All([
        new Assert\NotBlank(groups: ['validation']),
        new Assert\Type('integer'),
    ])]
    public ?array $tutelle_principale = null;

    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    public ?array $tutelle_secondaire = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?float $latitude = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?float $longitude = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?float $espace_bureau = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?float $espace_laboratoire = null;

    # Personnel
    #[Assert\All([
        new Assert\NotBlank(groups: ['validation']),
        new Assert\Type('integer'),
    ])]
    public ?array $responsable_scientifique = null;

    #[Assert\All([
        new Assert\NotBlank(groups: ['validation']),
        new Assert\Type('integer'),
    ])]
    public ?array $responsable_technique = null;

    #[Assert\All([
        new Assert\NotBlank(groups: ['validation']),
        new Assert\Type('integer'),
    ])]
    public ?array $personnel = null;

    #[Assert\All([
        new Assert\Collection(
            fields: [
                'employeur_id' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'corps_id' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'bap_id' => [
                    new Assert\Required([
                        new Assert\All([
                            new Assert\NotBlank(),
                            new Assert\Type('integer'),
                        ])
                    ])
                ],
                'emploi_type_id' => [
                    new Assert\Required([
                        new Assert\All([
                            new Assert\NotBlank(),
                            new Assert\Type('integer'),
                        ])
                    ])
                ],
                'quotite' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'rang_numerateur' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'rang_denominateur' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
            ]
        )
    ])]
    public ?array $postes_futurs = null;

    # Equipements
    #[Assert\All([
        new Assert\Collection(
            fields: [
                'nom_officiel' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('string'),
                    ])
                ],
                'marque' => [
                    new Assert\Required([
                        new Assert\Type('string'),
                        new Assert\Optional(),
                    ])
                ],
                'modele' => [
                    new Assert\Required([
                        new Assert\Type('string'),
                        new Assert\Optional(),
                    ])
                ],
                'annee_acquisition' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('string'),
                    ])
                ],
                'cout' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'valeur_remplacement' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'duree_utilisation' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'source_financement' => [
                    new Assert\Required([
                        new Assert\All([
                            new Assert\Collection(
                                fields: [
                                    "financeur_id" => [
                                        new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Type('integer'),
                                        ])
                                    ],
                                    "pourcentage" => [
                                        new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Type('integer'),
                                        ])
                                    ],
                                ]
                            )
                        ])
                    ])
                ],
                'maintenance' => [
                    new Assert\Required([
                        new Assert\All([
                            new Assert\Collection(
                                fields: [
                                    "type_contrat_id" => [
                                        new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Type('integer'),
                                        ])
                                    ],
                                    "commentaire_nature_contrat" => [
                                        new Assert\Required([
                                            new Assert\Optional(),
                                            new Assert\Type('string'),
                                        ])
                                    ],
                                    "fournisseur" => [
                                        new Assert\Required([
                                            new Assert\Optional(),
                                            new Assert\Type('string'),
                                        ])
                                    ],
                                    "mutualisation_id" => [
                                        new Assert\Required([
                                            new Assert\Optional(),
                                            new Assert\Type('int'),
                                        ])
                                    ],
                                    "mutualisation_autre_instrument_detaille" => [
                                        new Assert\Required([
                                            new Assert\Optional(),
                                            new Assert\All([
                                                new Assert\NotBlank(),
                                                new Assert\Type('integer'),
                                            ])
                                        ])
                                    ],
                                    "cout" => [
                                        new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Type('integer'),
                                        ])
                                    ]
                                ]
                            )
                        ])
                    ])
                ]
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
                    $somme += $source['pourcentage'];
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
                'nom_officiel' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('string'),
                    ])
                ],
                'marque' => [
                    new Assert\Required([
                        new Assert\Type('string'),
                        new Assert\Optional(),
                    ])
                ],
                'modele' => [
                    new Assert\Required([
                        new Assert\Type('string'),
                        new Assert\Optional(),
                    ])
                ],
                'operation_id' => [
                    new Assert\Required([
                        new Assert\Type('integer'),
                        new Assert\Optional(),
                    ])
                ],
                'date_achat_envisagee' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'cout' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'source_financement' => [
                    new Assert\Required([
                        new Assert\All([
                            new Assert\Collection(
                                fields: [
                                    "financeur_id" => [
                                        new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Type('integer'),
                                        ])
                                    ],
                                    "pourcentage" => [
                                        new Assert\Required([
                                            new Assert\NotBlank(),
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
                    $somme += $source['pourcentage'];
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
    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero]
    public ?float $materiel_consommable = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero]
    public ?float $contrat_entretien = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero]
    public ?float $etude_prestation = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero]
    public ?float $fluide_energie = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\PositiveOrZero]
    public ?float $mission_frais_generaux = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?int $determination_tarif_plateforme_id = null;

    ## Ressource
    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $contribution_tutelle_pourcentage = null;

    #[Assert\All([
        new Assert\Collection(
            fields: [
                'tutelle_id' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ])
                ],
                'pourcentage' => [
                    new Assert\Required([
                        new Assert\NotBlank(),
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
        if ($this->contribution_tutelle_pourcentage !== null && $this->contribution_tutelle_pourcentage !== 0) {
            $somme = 0;

            if ($this->contribution_tutelle_detail !== null) {
                foreach ($this->contribution_tutelle_detail as $detail) {
                    $somme += $detail['pourcentage'];
                }
            }

            if ($somme !== 100) {
                $context->buildViolation('La somme des pourcentages de contribution tutelle doit être égale à 100%.')
                    ->atPath('contribution_tutelle_detail')
                    ->addViolation();
            }
        }
    }

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $contribution_fond_public = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $contribution_fond_prive = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $contribution_credit = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $contribution_projet_recherche = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $contribution_structure_pia = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function contributionsEgalCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->contribution_fond_public;
        $somme += $this->contribution_fond_prive;
        $somme += $this->contribution_credit;
        $somme += $this->contribution_projet_recherche;
        $somme += $this->contribution_structure_pia;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de contribution doit être égale à 100%.')
                ->atPath('contribution_structure_pia')
                ->addViolation();
        }
    }

    public ?int $dotation_instrument_national = null;

    # Activités
    ## Accesibilité
    #[Assert\NotBlank(groups: ['validation'])]
    public ?bool $labellisation_qualite = null;

    public ?string $nom_labelisation_qualite = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?bool $procedure_transparente_tous = null;

    #[Assert\NotBlank(groups: ['validation'])]
    public ?bool $procedure_transparente_prestation_exterieure = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $pourcentage_accueil_international = null;

    ## Utilisation
    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $modele_77_2424 = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $modele_77_1224 = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $modele_57_2424 = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $modele_57_1224 = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function ModelesSommeCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->modele_77_2424;
        $somme += $this->modele_77_1224;
        $somme += $this->modele_57_2424;
        $somme += $this->modele_57_1224;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de modèles d\'utilisation doit être égale à 100%.')
                ->atPath('modele_57_1224')
                ->addViolation();
        }
    }

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $taux_utilisation_fonctionnement = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $taux_utilisation_maintenance = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $taux_utilisation_developemment = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $taux_utilisation_panne = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $taux_utilisation_temps_mort = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function TauxUtilisationPlateformeSommeCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->taux_utilisation_fonctionnement;
        $somme += $this->taux_utilisation_maintenance;
        $somme += $this->taux_utilisation_developemment;
        $somme += $this->taux_utilisation_panne;
        $somme += $this->taux_utilisation_temps_mort;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de taux d\'utilisation doit être égale à 100%.')
                ->atPath('taux_utilisation_temps_mort')
                ->addViolation();
        }
    }

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $taux_utilisation_equipe_operante = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $taux_utilisation_collaboration_interne = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $taux_utilisation_collaboration_externe = null;

    #[Assert\NotBlank(groups: ['validation'])]
    #[Assert\Range(min: 0, max: 100)]
    public ?int $taux_utilisation_prestation_service = null;

    #[Assert\Callback(groups: ['post_validation'])]
    public function TauxUtilisationDetaillePlateformeSommeCentPourcent(ExecutionContextInterface $context): void
    {
        $somme = 0;
        $somme += $this->taux_utilisation_fonctionnement;
        $somme += $this->taux_utilisation_maintenance;
        $somme += $this->taux_utilisation_developemment;
        $somme += $this->taux_utilisation_panne;
        $somme += $this->taux_utilisation_temps_mort;

        if ($somme !== 100) {
            $context->buildViolation('La somme des pourcentages de taux d\'utilisation doit être égale à 100%.')
                ->atPath('taux_utilisation_prestation_service')
                ->addViolation();
        }
    }

    ## Empreinte Carbone
    #[Assert\NotBlank(groups: ['validation'])]
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
