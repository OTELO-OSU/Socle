<?php

namespace App\Service;

use App\Entity\Bap;
use App\Entity\Cnu;
use App\Entity\ContratMaintenance;
use App\Entity\Corps;
use App\Entity\DeterminationTarifMethode;
use App\Entity\EmploiType;
use App\Entity\Entite;
use App\Entity\Grade;
use App\Entity\Instrument;
use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\SectionCnrs;
use App\Entity\TypeContrat;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Security\Voter\PlateformeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\SecurityBundle\Security;

class ExcelService
{

    public function __construct(
        private EntityManagerInterface $em,
        private PlateformeService $platformeService,
        private Security $security,
    ) {}

    function convertNameArrayToString($obj)
    {
        if ($obj) {
            $extra   = $obj["extra"]   ?? null;
            $acronym = $obj["acronym"] ?? null;
            $label   = $obj["label"]   ?? '';
            if ($extra == "epic") $extra = \null;
            if ($extra == "epscp") $extra = \null;
            if ($extra == "epst") $extra = \null;
            if ($extra == "epe") $extra = \null;
            if ($extra == "asso") $extra = \null;
            if ($extra && $acronym) {
                $name = "$extra $acronym - $label";
            } elseif ($extra) {
                $name = "$extra - $label";
            } elseif ($acronym) {
                $name = "$acronym - $label";
            } else {
                $name = $label;
            }
        } else {
            $name = '';
        }

        return $name;
    }

    // function exportAllPossiblePlatformToExcel()
    // {
    //     /** @var User $user */
    //     $user = $this->security->getUser();
    //     if (!$user) {
    //         // Lève une exception si l'utilisateur n'est pas connecté
    //         throw new UserNotFoundException();
    //     }
    //     $id = (string) $user->getId();

    //     // Si l'utilisateur est admin, on récupère toutes les plateformes
    //     if (\in_array("ROLE_ADMIN", $user->getRoles())) {
    //         $platforms = $this->dm->createQueryBuilder(Platforms::class)
    //             ->sort("data.name")
    //             ->getQuery()
    //             ->execute();
    //     }
    //     // Sinon, on récupère les plateformes où l'utilisateur est hôte OU des réseaux où il est hôte
    //     else {
    //         // Récupère les réseaux où l'utilisateur est host
    //         $networks = $this->dm->createQueryBuilder(Networks::class)
    //             ->field("hosts")->in([$id])
    //             ->getQuery()->execute();

    //         $networkIds = [];
    //         /** @var Network $network */
    //         foreach ($networks as $network) {
    //             $networkIds[] = $network->getId();
    //         }

    //         // Récupère les plateformes où l'utilisateur est host OU dont le network est hosté par lui
    //         $platforms = $this->dm->createQueryBuilder(Platforms::class)
    //             ->addOr(
    //                 $this->dm->createQueryBuilder(Platforms::class)->expr()->field("hosts")->in([$id])
    //             )
    //             ->addOr(
    //                 $this->dm->createQueryBuilder(Platforms::class)->expr()->field("data.network")->in($networkIds)
    //             )
    //             ->sort("data.name")
    //             ->getQuery()
    //             ->execute();
    //     }

    //     return $this->exportPlatformsToExcel($platforms);
    // }

    function exportSinglePlatformToExcel(int $platformId)
    {
        // On récupère la plateforme
        $platform = $this->em->getRepository(Plateforme::class)->find($platformId);
        if (!$platform) {
            // Lève une exception si la plateforme n'existe pas
            throw new Exception("Plateforme non trouvée", 404);
        }
        if (!$this->security->isGranted(PlateformeVoter::EXPORTER, $platform)) {
            throw new Exception("Accès refusé", 403);
        }

        $lastHistorique = $platform->getLastHistorique();

        return $this->exportPlatformeArrayEnExcel([$lastHistorique]);
    }

    // function exportAllPlatformFromNetworkToExcel($networkName)
    // {
    //     // On récupère le réseau
    //     $network = $this->dm->getRepository(Networks::class)->findOneBy(['label' => $networkName]);
    //     if (!$network) {
    //         // Lève une exception si le réseau n'existe pas
    //         throw new NetworkNotFoundException();
    //     }

    //     /** @var User $user */
    //     $user = $this->security->getUser();
    //     if (!$user) {
    //         // Lève une exception si l'utilisateur n'est pas connecté
    //         throw new UserNotFoundException();
    //     }

    //     // On récupère l'ID de l'utilisateur
    //     $id = (string) $user->getId();

    //     // Si l'utilisateur n'est pas admin on vérifie qu'il y a droit
    //     if (!\in_array("ROLE_ADMIN", $user->getRoles())) {

    //         if (!\in_array($id, $network->getHosts())) {
    //             throw new NetworkNotFoundException();
    //         }
    //     }

    //     // On récupère les plateformes du réseau
    //     $platforms = $this->dm->createQueryBuilder(Platforms::class)
    //         ->field("data.network")->equals($network->getLabel())
    //         ->getQuery()
    //         ->execute();

    //     $array = [];

    //     foreach ($platforms as $platform) {
    //         $array[] = $platform;
    //     }

    //     // On exporte les plateformes
    //     return $this->exportPlatformsToExcel($array);
    // }

    // function exportAllPlatformFromOsuToExcel($osuId)
    // {
    //     // On récupère l'osu
    //     $osu = $this->em->getRepository(Osu::class)->find($osuId);
    //     if (!$osu) {
    //         // Lève une exception si l'osu n'existe pas
    //         throw new OsuNotFoundException();
    //     }

    //     /** @var User $user */
    //     $user = $this->security->getUser();
    //     if (!$user) {
    //         // Lève une exception si l'utilisateur n'est pas connecté
    //         throw new UserNotFoundException();
    //     }

    //     // On récupère l'ID de l'utilisateur
    //     $id = (string) $user->getId();

    //     // Si l'utilisateur n'est pas admin on vérifie qu'il y a droit
    //     if (!\in_array("ROLE_ADMIN", $user->getRoles())) {

    //         if ($id !== (string) $osu->getDirector()->getId()) {
    //             throw new OsuNotFoundException();
    //         }
    //     }

    //     // On récupère les plateformes de l'OSU
    //     $labosOfOsu = $osu->getLaboratories();

    //     $platforms = $this->dm->createQueryBuilder(Platforms::class)
    //         ->field("data.laboratory.origin")->equals("laboratory")
    //         ->field("data.laboratory.id")->in($labosOfOsu->map(fn($labo) => $labo->getId())->toArray())
    //         ->getQuery()
    //         ->execute();

    //     $array = [];

    //     foreach ($platforms as $platform) {
    //         $array[] = $platform;
    //     }

    //     // On exporte les plateformes
    //     return $this->exportPlatformsToExcel($array);
    // }

    // function exportAllPlatformFromLaboratoryToExcel($laboratoryId, $origin)
    // {
    //     $array = [];
    //     if ($origin == "laboratory") {
    //         // On récupère l'osu
    //         $laboratory = $this->em->getRepository(Laboratory::class)->find($laboratoryId);
    //         if (!$laboratory) {
    //             // Lève une exception si le labo n'existe pas
    //             throw new LaboratoryNotFoundException();
    //         }

    //         $platforms = $this->dm->createQueryBuilder(Platforms::class)
    //             ->field("data.laboratory.origin")->equals("laboratory")
    //             ->field("data.laboratory.id")->equals((int) $laboratoryId)
    //             ->getQuery()
    //             ->execute();
    //     } else if ($origin == "hostOrganizations") {
    //         // On récupère l'osu
    //         $laboratory = $this->em->getRepository(HostOrganizations::class)->find($laboratoryId);
    //         if (!$laboratory) {
    //             // Lève une exception si le labo n'existe pas
    //             throw new LaboratoryNotFoundException();
    //         }

    //         $platforms = $this->dm->createQueryBuilder(Platforms::class)
    //             ->field("data.laboratory.origin")->equals("hostOrganizations")
    //             ->field("data.laboratory.id")->equals((int) $laboratoryId)
    //             ->getQuery()
    //             ->execute();
    //     }

    //     foreach ($platforms as $platform) {
    //         $array[] = $platform;
    //     }

    //     // On exporte les plateformes
    //     return $this->exportPlatformsToExcel($array);
    // }

    private function formatVille(?Ville $ville): string
    {
        if (!$ville) return '';
        return $ville->getNom() . " (" . $ville->getNumeroDepartement() . " " . $ville->getDepartement() . ")";
    }

    private function formatLabo(?Entite $labo): string
    {
        if (!$labo) return '';
        $chain = "";
        if ($labo->getAcronyme()) {
            $chain .= $labo->getAcronyme() . " - ";
        }
        $chain .= $labo->getNom();
        if ($labo->getCodeUnite()) {
            $chain .= " (" . $labo->getCodeUnite() . ")";
        }

        return $chain;
    }

    private function formatFinanceur(?Entite $financeur): string
    {
        if (!$financeur) return '';
        $chain = "";
        if ($financeur->getAcronyme()) {
            $chain .= $financeur->getAcronyme() . " - ";
        }
        $chain .= $financeur->getNom();
        if ($financeur->getCodeUnite()) {
            $chain .= " (" . $financeur->getCodeUnite() . ")";
        }

        return $chain;
    }

    private function formatContratPersonnel(?TypeContrat $contrat): string
    {
        if (!$contrat) return '';
        return $contrat->getNom();
    }

    private function formatContratMaintenance(?ContratMaintenance $contrat): string
    {
        if (!$contrat) return '';
        return $contrat->getNom();
    }

    private function formatCorps(?Corps $corps): string
    {
        if (!$corps) return '';
        $chain = "";
        if ($corps->getAcronyme()) {
            $chain .= $corps->getAcronyme() . " - ";
        }
        $chain .= $corps->getNom();
        return $chain;
    }

    private function formatGrade(?Grade $grade): string
    {
        if (!$grade) return '';
        return $grade->getNom();
    }

    private function formatBap(?Bap $bap): string
    {
        if (!$bap) return '';
        return $bap->getLettre();
    }

    private function formatEmploiType(?EmploiType $emploiType): string
    {
        if (!$emploiType) return '';
        $chain = "";
        if ($emploiType->getAcronyme()) {
            $chain .= $emploiType->getAcronyme() . " - ";
        }
        $chain .= $emploiType->getNom();
        return $chain;
    }

    private function formatCNU(?Cnu $cnu): string
    {
        if (!$cnu) return '';
        return $cnu->getNumero();
    }

    private function formatContratMutaliseType(?Instrument $instrument): string
    {
        if (!$instrument) return '';
        if ($instrument->isMutualiseSite()) return "Site";
        if ($instrument->isMutualiseUmrOsu()) return "UMR/OSU";
        if ($instrument->isMutualiseAutreInstrument()) return "Autre Instrument";
        return "";
    }

    private function formatSection(?SectionCnrs $section): string
    {
        if (!$section) return '';
        return $section->getNumero();
    }

    private function formatDeterminationTarif(?DeterminationTarifMethode $dtm): string
    {
        if (!$dtm) return '';
        return $dtm->getNom();
    }

    private function formatRang(?int $rangNumerateur, ?int $rangDenominateur): string
    {
        if (!$rangNumerateur || !$rangDenominateur) return '';
        return $rangNumerateur . '/' . $rangDenominateur;
    }

    /**
     * @param PlateformeHistorique[] $platforms
     */
    public function exportPlatformeArrayEnExcel(array $platforms)
    {
        $sheet1Array = [];
        $sheet1Array[] = [
            "ID_Plateforme",
            "Nom_Officiel",
            "Forme_Développée_Nom_Officiel",
            "Instrument_National",
            "Autre_Labellisation",
            "Site_Web",
            "Ville",
            "Logo_URL"
        ];

        $sheet3Array = [];
        $sheet3Array[] = [
            "ID_Plateforme",
            "Laboratoire_INSU_OSU_hebergeant_la_plateforme",
            "Nom_Directeur",
            "Prénom_Directeur",
            "Email_Directeur",
            "Latitude_Plateforme",
            "Longitude_Plateforme",
        ];

        $sheet4Array = [];
        $sheet4Array[] = [
            "ID_Plateforme",
            "Statut",
            "Tutelle"
        ];

        $sheet5Array = [];
        $sheet5Array[] = [
            "ID_Plateforme",
            "Contrat",
            "Responsable_Scientifique",
            "Responsable_Technique",
            "Responsable_Plateforme",
            "Doctorant",
            "Nom",
            "Prenom",
            "Email",
            "Employeur",
            "Université_Rattachement",
            "Corps",
            "Grade",
            "BAP_1",
            "BAP_2",
            "Emploi_Type_1",
            "Emploi_Type_2",
            "CNU",
            "Section",
            "ETPT",
            "Nombre"
        ];

        $sheet6Array = [];
        $sheet6Array[] = [
            "ID_Plateforme",
            "Employeur",
            "Corps",
            "BAP",
            "Emploi_Type",
            "Quotite_Temps_Plateforme",
            "Rang_Priorités",
        ];

        $sheet7Array = [];
        $sheet7Array[] = [
            "ID_Plateforme",
            "#ID_Equipement",
            "Nom_Equipement",
            "Marque",
            "Modèle",
            "Annee_achat/construction_Equipement",
            "Cout_Achat_k€_Equipemenent",
            "Valeur_Actuelle_k€_Equipement",
            "Amortissement_annee",
        ];

        $sheet8Array = [];
        $sheet8Array[] = [
            "ID_Plateforme",
            "#ID_Equipement",
            "Contributeur",
            "Pourcentage_Contribution",
        ];

        $sheet8BisBisArray = [];
        $sheet8BisBisArray[] = [
            "ID_Plateforme",
            "#ID_Equipement",
            "Type_Contrat_Maintenance",
            "Commentaire_Nature_Contrat",
            "Fournisseur_Entreprise_Contrat",
            "Contrat_Mutualisé_Autres_Instruments",
            "Cout_K_AN",
        ];

        $sheet8BisArray = [];
        $sheet8BisArray[] = [
            "ID_Plateforme",
            "#ID_Equipement",
            "#ID_Equipement_Mutualise",
        ];

        $sheet9Array = [];
        $sheet9Array[] = [
            "ID_Plateforme",
            "#ID_Equipement",
            "Nom_Equipement_Prospective",
            "Marque",
            "Modèle",
            "Annee_achat_Equipement_Prospective",
            "Cout_Achat_k€_Equipemenent_Prospective",
        ];

        $sheet10Array = [];
        $sheet10Array[] = [
            "ID_Plateforme",
            "#ID_Equipement",
            "Prospective_Contributeur",
            "Prospective_Pourcentage_Contribution",
        ];

        $sheet11Array = [];
        $sheet11Array[] = [
            "ID_Plateforme",
            "Materiel_Consommables_k€",
            "Contrats_Entretien_k€",
            "Etudes_Prestations_k€",
            "Fluides_Energies_k€",
            "Missions_Frais_Generaux_k€",
            "Somme_Coûts_k€",
        ];

        $sheet12Array = [];
        $sheet12Array[] = [
            "ID_Plateforme",
            "Bureaux_m2",
            "Laboratoire_m2",
        ];

        $sheet13Array = [];
        $sheet13Array[] = [
            "ID_Plateforme",
            "Ressources_Tutelles",
            "Ressources_Prestations_Fonds_Publics",
            "Ressources_Prestations_Fonds_Privés",
            "Ressources_Crédits_Laboratoire",
            "Ressources_Projet_Recherche",
            "Ressources_Structures_PIA",
            "Dotation_INSU_IN",
        ];

        $sheet14Array = [];
        $sheet14Array[] = [
            "ID_Plateforme",
            "Tutelle",
            "Taux_Contribution_Tutelle",
        ];

        $sheet15Array = [];
        $sheet15Array[] = [
            "ID_Plateforme",
            "Tarifs_Plateforme",
            "Certification",
            "Libellé_Certification",
            "Procedure_acces_prestations_exterieures",
            "Procedure_acces_tous_projets",
            "Fonctionnement_7/7_24/24",
            "Fonctionnement_7/7_12/24",
            "Fonctionnement_5/7_24/24",
            "Fonctionnement_5/7_12/24",
            "Taux_Utilisation_Fonctionnement",
            "Taux_Utilisation_Maintenance",
            "Taux_Utilisation_Developpement",
            "Taux_Utilisation_En_Panne",
            "Taux_Utilisation_Temps_Mort",
            "Taux_Utilisation_Equipe",
            "Taux_Utilisation_Collaboration_Interne",
            "Taux_Utilisation_Collaboration_Externe",
            "Taux_Utilisation_Prestation",
            "Accueil_International_National",
        ];

        $sheet16Array = [];
        $sheet16Array[] = [
            "ID_Plateforme",
            "BGES_Realise",
            "Domicile_Travail",
            "Missions",
            "Missions_Ext",
            "Consommables",
            "Equipements",
            "Envoi_Des_Echantillons",
            "Batiments",
            "Dechets"
        ];

        $sheet17Array[] = [
            "ID_PLateforme",
            "#Publications_Equipe",
            "#Publications_Collaboration_Interne",
            "#Publications_Collaboration_Externe",
            "#Publications_Autres",
            "#Thèses",
        ];

        $sheet18Array[] = [
            "ID_PLateforme",
            "Numero_Publication",
            "DOI"
        ];

        /** @var PlateformeHistorique $plat */
        foreach ($platforms as $plat) {
            $sheet1Array[] = [
                $plat->getId(),
                $plat->getNomOfficiel(),
                $plat->getFormeDeveloppeeNomOfficiel(),
                $plat->getPlateforme()->isInstrumentNational() ? 'Oui' : 'Non',
                $plat->getAutreTypeLabellisation() ?? '',
                $plat->getSiteWeb() ?? '',
                $this->formatVille($plat->getVille()),
                $plat->getLogoUrl() ?? '',
            ];

            $prenomDirLab = '';
            $nomDirLab = '';
            $emailDirLab = '';
            $laboOperateur = $plat->getLaboratoireOperateur();
            if ($laboOperateur) {
                $dir = $laboOperateur->getDirecteur();
                if ($dir) {
                    $prenomDirLab = $dir->getPrenom() ?? '';
                    $nomDirLab = $dir->getNom() ?? '';
                    $emailDirLab = $dir->getEmail() ?? '';
                }
            }

            $sheet3Array[] = [
                $plat->getId(),
                $this->formatLabo($plat->getLaboratoireOperateur()),
                $nomDirLab,
                $prenomDirLab,
                $emailDirLab,
                $plat->getLatitude() ?? '',
                $plat->getLongitude() ?? '',
            ];

            $tutelles = $plat->getPlateformeHistoriqueTutelles();

            foreach ($tutelles as $tutelle) {
                $sheet4Array[] = [
                    $plat->getId(),
                    $tutelle->isPrincipale() ? 'Principale' : 'Secondaire',
                    $this->formatFinanceur($tutelle->getTutelle()),
                ];
            }

            $personnels = $plat->getPlateformeHistoriquePersonnels();

            foreach ($personnels as $pers) {
                $baps = $pers->getUtilisateur()->getUtilisateurBaps();
                $emploiTypes = $pers->getUtilisateur()->getUtilisateurEmploiTypes();
                $sheet5Array[] = [
                    $plat->getId(),
                    $this->formatContratPersonnel($pers->getUtilisateur()->getTypeContrat()),
                    $pers->isResponsableScientifique() ? 'Oui' : 'Non',
                    $pers->isResponsableTechnique() ? 'Oui' : 'Non',
                    $pers->isResponsablePlateforme() ? 'Oui' : 'Non',
                    $pers->getUtilisateur()->isDoctorant() ? 'Oui' : 'Non',
                    $pers->getUtilisateur()->getNom() ?? '',
                    $pers->getUtilisateur()->getPrenom() ?? '',
                    $pers->getUtilisateur()->getEmail() ?? '',
                    $this->formatFinanceur($pers->getUtilisateur()->getEmployeur()),
                    $this->formatFinanceur($pers->getUtilisateur()->getUniversiteRattachement()),
                    $this->formatCorps($pers->getUtilisateur()->getCorps()),
                    $this->formatGrade($pers->getUtilisateur()->getGrade()),
                    $baps[0] ? $this->formatBap($baps[0]->getBap()) : '',
                    $baps[1] ? $this->formatBap($baps[1]->getBap()) : '',
                    $emploiTypes[0] ? $this->formatEmploiType($emploiTypes[0]->getEmploiType()) : '',
                    $emploiTypes[1] ? $this->formatEmploiType($emploiTypes[1]->getEmploiType()) : '',
                    $this->formatCNU($pers->getUtilisateur()->getCnu()),
                    $this->formatSection($pers->getUtilisateur()->getSectionCnrs()),
                    $pers->getQuotite() ?? '',
                    '',
                ];
            }

            $sheet5Array[] = [
                $plat->getId(),
                "Autres Etudiants",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                $plat->getNombreAutresEtudiants() ?? '',
            ];

            $sheet5Array[] = [
                $plat->getId(),
                "Autres Etudiants",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                $plat->getNombreApprentisAlternants() ?? '',
            ];

            $posteFuturs = $plat->getPosteFuturs();
            foreach ($posteFuturs as $pf) {
                $bapChaine = "";
                foreach ($pf->getBaps() as $bap) {
                    if (!empty($bapChaine)) {
                        $bapChaine .= " ; ";
                    }

                    $bapChaine .= $this->formatBap($bap);
                }

                $emploiTypeChaine = "";
                foreach ($pf->getEmploiTypes() as $emploiType) {
                    if (!empty($emploiTypeChaine)) {
                        $emploiTypeChaine .= " ; ";
                    }

                    $emploiTypeChaine .= $this->formatEmploiType($emploiType);
                }


                $sheet6Array[] = [
                    $plat->getId(),
                    $this->formatFinanceur($pf->getEmployeur()),
                    $this->formatCorps($pf->getCorps()),
                    $bapChaine,
                    $emploiTypeChaine,
                    $pf->getQuotite() ?? '',
                    $this->formatRang($pf->getRangNumerateur(), $pf->getRangDenominateur()),
                ];
            }

            foreach ($plat->getInstruments() as $instrument) {
                if (!$instrument->isFutur()) {
                    $sheet7Array[] = [
                        $plat->getId(),
                        $instrument->getId() ?? '',
                        $instrument->getNom() ?? '',
                        $instrument->getMarque() ?? '',
                        $instrument->getModele() ?? '',
                        $instrument->getAnneeAcquisition() ?? '',
                        $instrument->getCout() ?? '',
                        $instrument->getValeurRemplacement() ?? '',
                        $instrument->getDureeUtilisation() ?? '',
                    ];

                    foreach ($instrument->getFinancementInstruments() as $financement) {
                        $sheet8Array[] = [
                            $plat->getId(),
                            $instrument->getId() ?? '',
                            $this->formatFinanceur($financement->getFinanceur()),
                            $financement->getTaux() ?? '',
                        ];
                    }

                    $sheet8BisBisArray[] = [
                        $plat->getId(),
                        $instrument->getId() ?? '',
                        $this->formatContratMaintenance($instrument->getContratMaintenance()),
                        $instrument->getCommentaireNatureContrat() ?? '',
                        $instrument->getFournisseurContrat() ?? '',
                        $this->formatContratMutaliseType($instrument),
                        $instrument->getCoutMaintenance() ?? '',
                    ];

                    foreach ($instrument->getInstrumentMutualisationsIds() as $mutualisation) {
                        $sheet8BisArray[] = [
                            $plat->getId(),
                            $instrument->getId() ?? '',
                            $mutualisation
                        ];
                    }
                } else {
                    $sheet9Array[] = [
                        $plat->getId(),
                        $instrument->getId() ?? '',
                        $instrument->getNom() ?? '',
                        $instrument->getMarque() ?? '',
                        $instrument->getModele() ?? '',
                        $instrument->getAnneeAchatEnvisagee() ?? '',
                        $instrument->getCout() ?? '',
                    ];

                    foreach ($instrument->getFinancementInstruments() as $financement) {
                        $sheet10Array[] = [
                            $plat->getId(),
                            $instrument->getId() ?? '',
                            $this->formatFinanceur($financement->getFinanceur()),
                            $financement->getTaux() ?? '',
                        ];
                    }
                }
            }

            $total = 0;
            if ($plat->getMaterielConsommable()) $total += $plat->getMaterielConsommable();
            if ($plat->getContratEntretien()) $total += $plat->getContratEntretien();
            if ($plat->getEtudePrestation()) $total += $plat->getEtudePrestation();
            if ($plat->getFluideEnergie()) $total += $plat->getFluideEnergie();
            if ($plat->getMissionFraisGeneraux()) $total += $plat->getMissionFraisGeneraux();

            $sheet11Array[] = [
                $plat->getId(),
                $plat->getMaterielConsommable() ?? '',
                $plat->getContratEntretien() ?? '',
                $plat->getEtudePrestation() ?? '',
                $plat->getFluideEnergie() ?? '',
                $plat->getMissionFraisGeneraux() ?? '',
                $total,
            ];

            $sheet12Array[] = [
                $plat->getId(),
                $plat->getEspaceBureau() ?? '',
                $plat->getEspaceLaboratoire() ?? '',
            ];

            $sheet13Array[] = [
                $plat->getId(),
                $plat->getContributionTutelleTaux() ?? '',
                $plat->getContributionFondPublicTaux() ?? '',
                $plat->getContributionFondPriveTaux() ?? '',
                $plat->getContributionCreditTaux() ?? '',
                $plat->getContributionProjetRechercheTaux() ?? '',
                $plat->getContributionStructurePiaTaux() ?? '',
                $plat->getDotationInstrumentNational() ?? '',
            ];

            foreach ($plat->getContributionTutelles() as $contribTutelle) {
                $sheet14Array[] = [
                    $plat->getId(),
                    $this->formatFinanceur($contribTutelle->getFinanceur()),
                    $contribTutelle->getTaux() ?? '',
                ];
            }

            $sheet15Array[] = [
                $plat->getId(),
                $this->formatDeterminationTarif($plat->getDeterminationTarifPlateforme()),
                $plat->isLabellisationQualite() ? "Oui" : "Non",
                $plat->getNomLabellisationQualite() ?? '',
                $plat->isProcedureTransparentePrestationExterieure() ? "Oui" : "Non",
                $plat->isProcedureTransparenteTous() ? "Oui" : "Non",
                $plat->getModele772424Taux(),
                $plat->getModele771224Taux(),
                $plat->getModele572424Taux(),
                $plat->getModele571224Taux(),
                $plat->getUtilisationFonctionnementTaux() ?? '',
                $plat->getUtilisationMaintenanceTaux() ?? '',
                $plat->getUtilisationDeveloppementTaux() ?? '',
                $plat->getUtilisationPanneTaux() ?? '',
                $plat->getUtilisationTempsMortTaux() ?? '',
                $plat->getUtilisationEquipeOperanteTaux() ?? '',
                $plat->getPublicationCollaborationInterne() ?? '',
                $plat->getPublicationCollaborationExterne() ?? '',
                $plat->getUtilisationPrestationServiceTaux() ?? '',
                $plat->getAccueilInternationalTaux() ?? '',
            ];

            $sheet16Array[] = [
                $plat->getId(),
                $plat->isBgesRealise() ? "Oui" : "Non",
                $plat->getBgesDomicileTravail() ?? '',
                $plat->getBgesMission() ?? '',
                $plat->getBgesMissionExt() ?? '',
                $plat->getBgesConsommable() ?? '',
                $plat->getBgesEquipement() ?? '',
                $plat->getBgesEnvoiEchantillon() ?? '',
                $plat->getBgesBatiment() ?? '',
                $plat->getBgesDechet() ?? '',
            ];

            $sheet17Array[] = [
                $plat->getId(),
                $plat->getPublicationEquipeOperante() ?? '',
                $plat->getPublicationCollaborationInterne() ?? '',
                $plat->getPublicationCollaborationExterne() ?? '',
                $plat->getPublicationAutreType() ?? '',
                $plat->getPublicationThese() ?? '',
            ];

            foreach ($plat->getPublications() as $publication) {
                $sheet18Array[] = [
                    $plat->getId(),
                    $publication->getNumero() ?? '',
                    $publication->getDoi() ?? '',
                ];
            }
        }




        $spreadsheet = new Spreadsheet();
        $sheet1 = $spreadsheet->getActiveSheet();

        $sheet1->setTitle('Renseignements_Généraux');
        $sheet1->getStyle('1')->getFont()->setBold(true);


        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet1Array[0]) + 1);

        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet1Array); $row++) {
                $sheet1->setCellValue($col . $row, $sheet1Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }


        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Appartenance');
        $sheet3->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet3Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet3Array); $row++) {
                $sheet3->setCellValue($col . $row, $sheet3Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }


        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Tutelles');
        $sheet4->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet4Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet4Array); $row++) {
                $sheet4->setCellValue($col . $row, $sheet4Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet5 = $spreadsheet->createSheet();
        $sheet5->setTitle('Responsables_de_Plateforme');
        $sheet5->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet5Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet5Array); $row++) {
                $sheet5->setCellValue($col . $row, $sheet5Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet6 = $spreadsheet->createSheet();
        $sheet6->setTitle('Personnels_Futurs');
        $sheet6->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet6Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet6Array); $row++) {
                $sheet6->setCellValue($col . $row, $sheet6Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet7 = $spreadsheet->createSheet();
        $sheet7->setTitle('Equipements');
        $sheet7->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet7Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet7Array); $row++) {
                $sheet7->setCellValue($col . $row, $sheet7Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet8 = $spreadsheet->createSheet();
        $sheet8->getStyle('1')->getFont()->setBold(true);
        $sheet8->setTitle('Equipements_Contributeurs');
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet8Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet8Array); $row++) {
                $sheet8->setCellValue($col . $row, $sheet8Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet8BisBis = $spreadsheet->createSheet();
        $sheet8BisBis->getStyle('1')->getFont()->setBold(true);
        $sheet8BisBis->setTitle('Equipements_Maintenance');
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet8BisBisArray[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet8BisBisArray); $row++) {
                $sheet8BisBis->setCellValue($col . $row, $sheet8BisBisArray[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet8Bis = $spreadsheet->createSheet();
        $sheet8Bis->getStyle('1')->getFont()->setBold(true);
        $sheet8Bis->setTitle('Equipements_Contrats_Mutualisés');
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet8BisArray[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet8BisArray); $row++) {
                $sheet8Bis->setCellValue($col . $row, $sheet8BisArray[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet9 = $spreadsheet->createSheet();
        $sheet9->setTitle('Prospectives_Equipements');
        $sheet9->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet9Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet9Array); $row++) {
                $sheet9->setCellValue($col . $row, $sheet9Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet10 = $spreadsheet->createSheet();
        $sheet10->setTitle('Prospectives_Eq_Contributeurs');
        $sheet10->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet10Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet10Array); $row++) {
                $sheet10->setCellValue($col . $row, $sheet10Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet12 = $spreadsheet->createSheet();
        $sheet12->setTitle('Locaux');
        $sheet12->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet12Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet12Array); $row++) {
                $sheet12->setCellValue($col . $row, $sheet12Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet13 = $spreadsheet->createSheet();
        $sheet13->setTitle('Origine_ressources');
        $sheet13->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet13Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet13Array); $row++) {
                $sheet13->setCellValue($col . $row, $sheet13Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet14 = $spreadsheet->createSheet();
        $sheet14->setTitle('Détail_Ressources_Tutelles');
        $sheet14->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet14Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet14Array); $row++) {
                $sheet14->setCellValue($col . $row, $sheet14Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }



        $sheet15 = $spreadsheet->createSheet();
        $sheet15->setTitle('Accès_Production');
        $sheet15->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet15Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet15Array); $row++) {
                $sheet15->setCellValue($col . $row, $sheet15Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }


        $sheet16 = $spreadsheet->createSheet();
        $sheet16->setTitle('BGES');
        $sheet16->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet16Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet16Array); $row++) {
                $sheet16->setCellValue($col . $row, $sheet16Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet17 = $spreadsheet->createSheet();
        $sheet17->setTitle('Publications_Détails');
        $sheet17->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet17Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet17Array); $row++) {
                $sheet17->setCellValue($col . $row, $sheet17Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet18 = $spreadsheet->createSheet();
        $sheet18->setTitle('Publications');
        $sheet18->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet18Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet18Array); $row++) {
                $sheet18->setCellValue($col . $row, $sheet18Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $writer);

        return $content;
    }
}
