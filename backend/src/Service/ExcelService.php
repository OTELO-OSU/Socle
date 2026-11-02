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
use App\Entity\ModaliteRecrutement;
use App\Entity\Plateforme;
use App\Entity\SectionCnrs;
use App\Entity\TypeContrat;
use App\Entity\Ville;
use App\Security\Voter\PlateformeVoter;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExcelService
{
    private const EXPORT_REFERENCE_TYPES = [
        'ville_id' => 'ville',
        'laboratoire_operateur_id' => 'entite',
        'tutelles_principales_ids' => 'entite',
        'tutelles_secondaires_ids' => 'entite',
        'financeur_id' => 'entite',
        'employeur_id' => 'entite',
        'universite_rattachement_id' => 'entite',
        'type_contrat_id' => 'contrat_personnel',
        'corps_id' => 'corps',
        'grade_id' => 'grade',
        'baps_ids' => 'bap',
        'emploi_types_ids' => 'emploi_type',
        'cnu_id' => 'cnu',
        'section_cnrs_id' => 'section',
        'modalite_recrutement_id' => 'modalite_recrutement',
        'contrat_maintenance_id' => 'contrat_maintenance',
        'determination_tarif_plateforme_id' => 'determination_tarif',
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private SerializerInterface $serializerInterface,
        private readonly TranslatorInterface $translator,
        private readonly HarvesterService $harvesterService
    ) {}

    /** Écrit la valeur en forçant le type texte si elle pourrait être interprétée comme une formule */
    private function setSafeCellValue(Worksheet $sheet, string $coordinate, mixed $value): void
    {
        if (\is_string($value) && !is_numeric($value) && preg_match('/^\s*[=+\-@]/', $value)) {
            $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
            return;
        }
        $sheet->setCellValue($coordinate, $value);
    }

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

    function exportSinglePlatformToExcel(int $platformId)
    {
        // On récupère la plateforme
        $platform = $this->em->getRepository(Plateforme::class)->find($platformId);
        if (!$platform) {
            // Lève une exception si la plateforme n'existe pas
            throw new NotFoundHttpException($this->translator->trans('plateforme.exception.plateforme_nf'));
        }
        if (!$this->security->isGranted(PlateformeVoter::EXPORTER, $platform)) {
            throw new AccessDeniedHttpException($this->translator->trans('plateforme.exception.exporter'));
        }

        return $this->exportPlatformeArrayEnExcel([$this->preparePlatformePourExport($platform)]);
    }

    public function exportAllPlatformsToExcel(): string
    {
        if (!$this->security->isGranted(PlateformeVoter::EXPORTER_ABSOLUMENT_TOUT, Plateforme::class)) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('plateforme.exception.exporter_tout'));
        }

        return $this->exportPlatformsToExcel();
    }

    public function exportPlatformsByTagToExcel(int $tagId): string
    {
        if (!$this->security->isGranted(PlateformeVoter::EXPORT_TAG, $tagId)) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('plateforme.exception.exporter_par_tag'));
        }
        return $this->exportPlatformsToExcel($tagId);
    }

    public function exportPlatformsByUnitRorToExcel(int $laboId): string
    {
        if (!$this->security->isGranted(PlateformeVoter::EXPORT_UNITE, $this->em->getRepository(Entite::class)->find($laboId))) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('plateforme.exception.exporter_par_unite'));
        }
        return $this->exportPlatformsToExcel(null, $laboId);
    }

    private function exportPlatformsToExcel(?int $tagId = null, ?int $laboId = null): string
    {
        if (!$this->security->getUser()) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('auth.exception.non_connecte'));
        }

        $platforms = [];

        foreach ($this->em->getRepository(Plateforme::class)->findAll() as $platform) {
            $lastHistorique = $platform->getLastHistorique();
            if ($lastHistorique === null) {
                continue;
            }

            if ($tagId !== null && !$platform->getPlateformeTags()->exists(
                static fn(int $key, \App\Entity\PlateformeTags $tag): bool => $tag->getTagId() === $tagId
            )) {
                continue;
            }

            if ($laboId !== null && $lastHistorique->getLaboratoireOperateur()?->getId() !== $laboId) {
                continue;
            }

            $exportData = $this->preparePlatformePourExport($platform);
            $exportData['export_id'] = 'local:' . $platform->getId();
            $platforms[] = $exportData;
        }

        // Une unité est locale : on n'interroge pas les harvesters pour un export par unité
        $plateformesHarvestees = $laboId === null
            ? $this->harvesterService->getToutesLesPlateformesHarvestePourExport($tagId)
            : [];

        foreach ($plateformesHarvestees as $host => $remotePlatforms) {
            foreach ($remotePlatforms as $remotePlatform) {
                $remotePlatformId = $remotePlatform['id'] ?? null;
                if (!is_int($remotePlatformId) && !(is_string($remotePlatformId) && ctype_digit($remotePlatformId))) {
                    throw new \UnexpectedValueException('Identifiant de plateforme distante invalide.');
                }

                $exportData = $this->harvesterService->getPlateformeHarvesteExportDataSansPermission((int) $remotePlatformId, $host);
                $exportData['export_id'] = 'harvester:' . $host . ':' . $remotePlatformId;
                $platforms[] = $exportData;
            }
        }

        return $this->exportPlatformeArrayEnExcel($platforms);
    }

    public function preparePlatformePourExport(Plateforme $platform): array
    {
        $json = $this->serializerInterface->serialize($platform, 'json', ['groups' => ['plateforme:detail']]);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new \UnexpectedValueException('Impossible de préparer les données de la plateforme pour l’export.');
        }

        $references = [];
        $this->collectExportReferences($data, $references);

        return ['platform' => $data, 'references' => $references];
    }

    private function collectExportReferences(array $data, array &$references): void
    {
        foreach ($data as $key => $value) {
            if (isset(self::EXPORT_REFERENCE_TYPES[$key])) {
                $type = self::EXPORT_REFERENCE_TYPES[$key];
                $ids = is_array($value) ? $value : [$value];
                foreach ($ids as $id) {
                    if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
                        continue;
                    }

                    $id = (int) $id;
                    if (!array_key_exists((string) $id, $references[$type] ?? [])) {
                        $references[$type][(string) $id] = $this->resolveReferenceLabel($type, $id);
                    }
                    if ($type === 'entite' && $key === 'laboratoire_operateur_id' && !array_key_exists((string) $id, $references['laboratoire_directeurs'] ?? [])) {
                        $entite = $this->em->getRepository(Entite::class)->find($id);
                        $directeur = $entite?->getDirecteur();
                        $references['laboratoire_directeurs'][(string) $id] = [
                            'prenom' => $directeur?->getPrenom() ?? '',
                            'nom' => $directeur?->getNom() ?? '',
                            'email' => $directeur?->getEmail() ?? '',
                            'rnsr' => $entite?->getNumeroRnsr() ?? '',
                        ];
                    }
                }
            }

            if (is_array($value)) {
                $this->collectExportReferences($value, $references);
            }
        }
    }

    private function resolveReferenceLabel(string $type, int $id): string
    {
        return match ($type) {
            'ville' => $this->formatVille($id),
            'entite' => $this->formatFinanceur($id),
            'contrat_personnel' => $this->formatContratPersonnel($id),
            'contrat_maintenance' => $this->formatContratMaintenance($id),
            'corps' => $this->formatCorps($id),
            'grade' => $this->formatGrade($id),
            'bap' => $this->formatBap($id),
            'emploi_type' => $this->formatEmploiType($id),
            'cnu' => $this->formatCNU($id),
            'section' => $this->formatSection($id),
            'modalite_recrutement' => $this->formatModaliteRecrutement($id),
            'determination_tarif' => $this->formatDeterminationTarif($id),
            default => '',
        };
    }

    private function resolvedReference(?int $id, string $type, ?array $references): ?string
    {
        if ($references === null) {
            return null;
        }

        if (!$id) {
            return '';
        }

        return $references[$type][(string) $id] ?? '';
    }

    private function formatVille(?int $villeId, ?array $references = null): string
    {
        if (!$villeId) return '';
        if (($resolved = $this->resolvedReference($villeId, 'ville', $references)) !== null) return $resolved;
        $ville = $this->em->getRepository(Ville::class)->find($villeId);
        if (!$ville) return '';
        return $ville->getNom() . " (" . $ville->getNumeroDepartement() . " " . $ville->getDepartement() . ")";
    }

    private function formatLabo(?int $laboId, ?array $references = null): string
    {
        if (!$laboId) return '';
        if (($resolved = $this->resolvedReference($laboId, 'entite', $references)) !== null) return $resolved;
        $labo = $this->em->getRepository(Entite::class)->find($laboId);
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

    private function formatFinanceur(?int $financeurId, ?array $references = null): string
    {
        if (!$financeurId) return '';
        if (($resolved = $this->resolvedReference($financeurId, 'entite', $references)) !== null) return $resolved;
        $financeur = $this->em->getRepository(Entite::class)->find($financeurId);
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

    private function formatModaliteRecrutement(?int $modaliteId, ?array $references = null): string
    {
        if (!$modaliteId) return '';
        if (($resolved = $this->resolvedReference($modaliteId, 'modalite_recrutement', $references)) !== null) return $resolved;
        $modalite = $this->em->getRepository(ModaliteRecrutement::class)->find($modaliteId);
        if (!$modalite) return '';
        return $modalite->getNom();
    }

    private function formatContratPersonnel(?int $contratId, ?array $references = null): string
    {
        if (!$contratId) return '';
        if (($resolved = $this->resolvedReference($contratId, 'contrat_personnel', $references)) !== null) return $resolved;
        $contrat = $this->em->getRepository(TypeContrat::class)->find($contratId);
        if (!$contrat) return '';
        return $contrat->getNom();
    }

    private function formatContratMaintenance(?int $contratId, ?array $references = null): string
    {
        if (!$contratId) return '';
        if (($resolved = $this->resolvedReference($contratId, 'contrat_maintenance', $references)) !== null) return $resolved;
        $contrat = $this->em->getRepository(ContratMaintenance::class)->find($contratId);
        if (!$contrat) return '';
        return $contrat->getNom();
    }

    private function formatCorps(?int $corpsId, ?array $references = null): string
    {
        if (!$corpsId) return '';
        if (($resolved = $this->resolvedReference($corpsId, 'corps', $references)) !== null) return $resolved;
        $corps = $this->em->getRepository(Corps::class)->find($corpsId);
        if (!$corps) return '';
        $chain = "";
        if ($corps->getAcronyme()) {
            $chain .= $corps->getAcronyme() . " - ";
        }
        $chain .= $corps->getNom();
        return $chain;
    }

    private function formatGrade(?int $gradeId, ?array $references = null): string
    {
        if (!$gradeId) return '';
        if (($resolved = $this->resolvedReference($gradeId, 'grade', $references)) !== null) return $resolved;
        $grade = $this->em->getRepository(Grade::class)->find($gradeId);
        if (!$grade) return '';
        return $grade->getNom();
    }

    private function formatBap(?int $bapId, ?array $references = null): string
    {
        if (!$bapId) return '';
        if (($resolved = $this->resolvedReference($bapId, 'bap', $references)) !== null) return $resolved;
        $bap = $this->em->getRepository(Bap::class)->find($bapId);
        if (!$bap) return '';
        return $bap->getLettre();
    }

    private function formatEmploiType(?int $emploiTypeId, ?array $references = null): string
    {
        if (!$emploiTypeId) return '';
        if (($resolved = $this->resolvedReference($emploiTypeId, 'emploi_type', $references)) !== null) return $resolved;
        $emploiType = $this->em->getRepository(EmploiType::class)->find($emploiTypeId);
        if (!$emploiType) return '';
        $chain = "";
        if ($emploiType->getAcronyme()) {
            $chain .= $emploiType->getAcronyme() . " - ";
        }
        $chain .= $emploiType->getNom();
        return $chain;
    }

    private function formatCNU(?int $cnuId, ?array $references = null): string
    {
        if (!$cnuId) return '';
        if (($resolved = $this->resolvedReference($cnuId, 'cnu', $references)) !== null) return $resolved;
        $cnu = $this->em->getRepository(Cnu::class)->find($cnuId);
        if (!$cnu) return '';
        return $cnu->getNumero();
    }

    private function formatContratMutaliseType(array $instrument): string
    {
        if ($instrument["mutualise_site"]) return "Site";
        if ($instrument["mutualise_umr_osu"]) return "UMR/OSU";
        if ($instrument["mutualise_autre_instrument"]) return "Autre Instrument";
        return "";
    }

    private function formatSection(?int $sectionId, ?array $references = null): string
    {
        if (!$sectionId) return '';
        if (($resolved = $this->resolvedReference($sectionId, 'section', $references)) !== null) return $resolved;
        $section = $this->em->getRepository(SectionCnrs::class)->find($sectionId);
        if (!$section) return '';
        return $section->getNumero();
    }

    private function formatDeterminationTarif(?int $dtmId, ?array $references = null): string
    {
        if (!$dtmId) return '';
        if (($resolved = $this->resolvedReference($dtmId, 'determination_tarif', $references)) !== null) return $resolved;
        $dtm = $this->em->getRepository(DeterminationTarifMethode::class)->find($dtmId);
        if (!$dtm) return '';
        return $dtm->getNom();
    }

    private function formatRang(?int $rangNumerateur, ?int $rangDenominateur): string
    {
        if (!$rangNumerateur || !$rangDenominateur) return '';
        return $rangNumerateur . '/' . $rangDenominateur;
    }

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
            "Numéro_RNSR",
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
            "Modalité_Recrutement",
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

        foreach ($platforms as $plat) {
            $references = null;
            if (is_string($plat)) {
                $plat = \json_decode($plat, true);
            } elseif (is_array($plat) && isset($plat['platform'], $plat['references'])) {
                $references = $plat['references'];
                $plat = $plat['platform'];
            }

            if (!is_array($plat)) {
                throw new \UnexpectedValueException('Format de données de plateforme invalide pour l’export.');
            }

            if (isset($plat['export_id'])) {
                $plat['id'] = $plat['export_id'];
            }

            $dernierHistorique = $plat["dernier_historique"];
            $sheet1Array[] = [
                $plat["id"],
                $dernierHistorique["nom_officiel"],
                $dernierHistorique["forme_developpee_nom_officiel"],
                $plat["instrument_national"] ? 'Oui' : 'Non',
                $dernierHistorique["autre_type_labellisation"] ?? '',
                $dernierHistorique["site_web"] ?? '',
                $this->formatVille($dernierHistorique["ville_id"], $references),
                $dernierHistorique["logo_url"] ?? '',
            ];

            $prenomDirLab = '';
            $nomDirLab = '';
            $emailDirLab = '';
            $rnsrLab = '';
            $laboOperateurId = $dernierHistorique["laboratoire_operateur_id"] ?? null;
            if ($laboOperateurId) {
                if ($references !== null) {
                    $dir = $references['laboratoire_directeurs'][(string) $laboOperateurId] ?? [];
                    $prenomDirLab = $dir['prenom'] ?? '';
                    $nomDirLab = $dir['nom'] ?? '';
                    $emailDirLab = $dir['email'] ?? '';
                    $rnsrLab = $dir['rnsr'] ?? '';
                } else {
                    $laboOperateur = $this->em->getRepository(Entite::class)->find($laboOperateurId);
                    $rnsrLab = $laboOperateur?->getNumeroRnsr() ?? '';
                    $dir = $laboOperateur?->getDirecteur();
                    if ($dir) {
                        $prenomDirLab = $dir->getPrenom() ?? '';
                        $nomDirLab = $dir->getNom() ?? '';
                        $emailDirLab = $dir->getEmail() ?? '';
                    }
                }
            }

            $sheet3Array[] = [
                $plat["id"],
                $this->formatLabo($dernierHistorique["laboratoire_operateur_id"], $references),
                $rnsrLab,
                $nomDirLab,
                $prenomDirLab,
                $emailDirLab,
                $dernierHistorique["latitude"] ?? '',
                $dernierHistorique["longitude"] ?? '',
            ];

            $tutellesPrincipales = $dernierHistorique["tutelles_principales_ids"] ?? [];

            foreach ($tutellesPrincipales as $tutelleId) {
                $sheet4Array[] = [
                    $plat["id"],
                    'Principale',
                    $this->formatFinanceur($tutelleId, $references),
                ];
            }

            $tutellesSecondaires = $dernierHistorique["tutelles_secondaires_ids"] ?? [];

            foreach ($tutellesSecondaires as $tutelleId) {
                $sheet4Array[] = [
                    $plat["id"],
                    'Secondaire',
                    $this->formatFinanceur($tutelleId, $references),
                ];
            }

            $personnels = $dernierHistorique["personnels"] ?? [];


            foreach ($personnels as $pers) {
                $sheet5Array[] = [
                    $plat["id"],
                    $this->formatContratPersonnel($pers["utilisateur"]["type_contrat_id"] ?? null, $references),
                    $pers["responsable_scientifique"] ? 'Oui' : 'Non',
                    $pers["responsable_technique"] ? 'Oui' : 'Non',
                    $pers["responsable_plateforme"] ? 'Oui' : 'Non',
                    $pers["utilisateur"]["doctorant"] ? 'Oui' : 'Non',
                    $pers["utilisateur"]["nom"] ?? '',
                    $pers["utilisateur"]["prenom"] ?? '',
                    $pers["utilisateur"]["email"] ?? '',
                    $this->formatFinanceur($pers["utilisateur"]["employeur_id"], $references),
                    $this->formatFinanceur($pers["utilisateur"]["universite_rattachement_id"], $references),
                    $this->formatCorps($pers["utilisateur"]["corps_id"], $references),
                    $this->formatGrade($pers["utilisateur"]["grade_id"], $references),
                    array_key_exists(0, $pers["utilisateur"]["baps_ids"]) ? $this->formatBap($pers["utilisateur"]["baps_ids"][0], $references) : '',
                    array_key_exists(1, $pers["utilisateur"]["baps_ids"]) ? $this->formatBap($pers["utilisateur"]["baps_ids"][1], $references) : '',
                    array_key_exists(0, $pers["utilisateur"]["emploi_types_ids"]) ? $this->formatEmploiType($pers["utilisateur"]["emploi_types_ids"][0], $references) : '',
                    array_key_exists(1, $pers["utilisateur"]["emploi_types_ids"]) ? $this->formatEmploiType($pers["utilisateur"]["emploi_types_ids"][1], $references) : '',
                    $this->formatCNU($pers["utilisateur"]["cnu_id"], $references),
                    $this->formatSection($pers["utilisateur"]["section_cnrs_id"], $references),
                    $pers["quotite"] ?? '',
                    '',
                ];
            }

            $sheet5Array[] = [
                $plat["id"],
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
                $dernierHistorique["nombre_autres_etudiants"] ?? '',
            ];

            $sheet5Array[] = [
                $plat["id"],
                "Apprentis / Alternants",
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
                $dernierHistorique["nombre_apprentis_alternants"] ?? '',
            ];

            $posteFuturs = $dernierHistorique["postes_futurs"] ?? [];
            foreach ($posteFuturs as $pf) {
                $bapChaine = "";
                foreach ($pf["baps_ids"] as $bap) {
                    if (!empty($bapChaine)) {
                        $bapChaine .= " ; ";
                    }

                    $bapChaine .= $this->formatBap($bap, $references);
                }

                $emploiTypeChaine = "";
                foreach ($pf["emploi_types_ids"] as $emploiType) {
                    if (!empty($emploiTypeChaine)) {
                        $emploiTypeChaine .= " ; ";
                    }

                    $emploiTypeChaine .= $this->formatEmploiType($emploiType, $references);
                }


                $sheet6Array[] = [
                    $plat["id"],
                    $this->formatFinanceur($pf["employeur_id"], $references),
                    $this->formatModaliteRecrutement($pf["modalite_recrutement_id"], $references),
                    $this->formatCorps($pf["corps_id"], $references),
                    $bapChaine,
                    $emploiTypeChaine,
                    $pf["quotite"] ?? '',
                    $this->formatRang($pf["rang_numerateur"] ?? '', $pf["rang_denominateur"] ?? ''),
                ];
            }

            foreach ($dernierHistorique["instruments"] ?? [] as $instrument) {
                if (!$instrument["futur"]) {
                    $sheet7Array[] = [
                        $plat["id"],
                        $instrument["id"] ?? '',
                        $instrument["nom"] ?? '',
                        $instrument["marque"] ?? '',
                        $instrument["modele"] ?? '',
                        $instrument["annee_acquisition"] ?? '',
                        $instrument["cout"] ?? '',
                        $instrument["valeur_remplacement"] ?? '',
                        $instrument["duree_utilisation"] ?? '',
                    ];

                    foreach ($instrument["source_financement"] ?? [] as $financement) {
                        $sheet8Array[] = [
                            $plat["id"] ?? '',
                            $instrument["id"] ?? '',
                            $this->formatFinanceur($financement["financeur_id"], $references),
                            $financement["taux"] ?? '',
                        ];
                    }

                    $sheet8BisBisArray[] = [
                        $plat["id"] ?? '',
                        $instrument["id"] ?? '',
                        $this->formatContratMaintenance($instrument["contrat_maintenance_id"] ?? null, $references),
                        $instrument["commentaire_nature_contrat"] ?? '',
                        $instrument["fournisseur_contrat"] ?? '',
                        $this->formatContratMutaliseType($instrument),
                        $instrument["cout_maintenance"] ?? '',
                    ];

                    foreach ($instrument["mutualise_autre_instrument_ids"] ?? [] as $mutualisation) {
                        $sheet8BisArray[] = [
                            $plat["id"] ?? '',
                            $instrument["id"] ?? '',
                            $mutualisation
                        ];
                    }
                } else {
                    $sheet9Array[] = [
                        $plat["id"] ?? '',
                        $instrument["id"] ?? '',
                        $instrument["nom"] ?? '',
                        $instrument["marque"] ?? '',
                        $instrument["modele"] ?? '',
                        $instrument["annee_acquisition"] ?? '',
                        $instrument["cout"] ?? '',
                    ];

                    foreach ($instrument["source_financement"] ?? [] as $financement) {
                        $sheet10Array[] = [
                            $plat["id"] ?? '',
                            $instrument["id"] ?? '',
                            $this->formatFinanceur($financement["financeur_id"], $references),
                            $financement["taux"] ?? '',
                        ];
                    }
                }
            }

            $total = 0;
            if ($dernierHistorique["materiel_consommable"]) $total += $dernierHistorique["materiel_consommable"];
            if ($dernierHistorique["contrat_entretien"]) $total += $dernierHistorique["contrat_entretien"];
            if ($dernierHistorique["etude_prestation"]) $total += $dernierHistorique["etude_prestation"];
            if ($dernierHistorique["fluide_energie"]) $total += $dernierHistorique["fluide_energie"];
            if ($dernierHistorique["mission_frais_generaux"]) $total += $dernierHistorique["mission_frais_generaux"];

            $sheet11Array[] = [
                $plat["id"] ?? '',
                $dernierHistorique["materiel_consommable"] ?? '',
                $dernierHistorique["contrat_entretien"] ?? '',
                $dernierHistorique["etude_prestation"] ?? '',
                $dernierHistorique["fluide_energie"] ?? '',
                $dernierHistorique["mission_frais_generaux"] ?? '',
                $total,
            ];

            $sheet12Array[] = [
                $plat["id"] ?? '',
                $dernierHistorique["espace_bureau"] ?? '',
                $dernierHistorique["espace_laboratoire"] ?? '',
            ];

            $sheet13Array[] = [
                $plat["id"] ?? '',
                $dernierHistorique["contribution_tutelle_taux"] ?? '',
                $dernierHistorique["contribution_fond_public_taux"] ?? '',
                $dernierHistorique["contribution_fond_prive_taux"] ?? '',
                $dernierHistorique["contribution_credit_taux"] ?? '',
                $dernierHistorique["contribution_projet_recherche_taux"] ?? '',
                $dernierHistorique["contribution_structure_pia_taux"] ?? '',
                $dernierHistorique["dotation_instrument_national"] ?? '',
            ];

            foreach ($dernierHistorique["contribution_tutelles"] ?? [] as $contribTutelle) {
                $sheet14Array[] = [
                    $plat["id"] ?? '',
                    $this->formatFinanceur($contribTutelle["financeur_id"], $references),
                    $contribTutelle["taux"] ?? '',
                ];
            }

            $sheet15Array[] = [
                $plat["id"] ?? '',
                $this->formatDeterminationTarif($dernierHistorique["determination_tarif_plateforme_id"], $references),
                $dernierHistorique["labellisation_qualite"] ? "Oui" : "Non",
                $dernierHistorique["nom_labellisation_qualite"] ?? '',
                $dernierHistorique["procedure_transparente_prestation_exterieure"] ? "Oui" : "Non",
                $dernierHistorique["procedure_transparente_tous"] ? "Oui" : "Non",
                $dernierHistorique["modele772424_taux"] ?? '',
                $dernierHistorique["modele771224_taux"] ?? '',
                $dernierHistorique["modele572424_taux"] ?? '',
                $dernierHistorique["modele571224_taux"] ?? '',
                $dernierHistorique["utilisation_fonctionnement_taux"] ?? '',
                $dernierHistorique["utilisation_maintenance_taux"] ?? '',
                $dernierHistorique["utilisation_developpement_taux"] ?? '',
                $dernierHistorique["utilisation_panne_taux"] ?? '',
                $dernierHistorique["utilisation_temps_mort_taux"] ?? '',
                $dernierHistorique["utilisation_equipe_operante_taux"] ?? '',
                $dernierHistorique["publication_collaboration_interne"] ?? '',
                $dernierHistorique["publication_collaboration_externe"] ?? '',
                $dernierHistorique["utilisation_prestation_service_taux"] ?? '',
                $dernierHistorique["accueil_international_taux"] ?? '',
            ];

            $sheet16Array[] = [
                $plat["id"] ?? '',
                $dernierHistorique["bges_realise"] ? "Oui" : "Non",
                $dernierHistorique["bges_domicile_travail"] ?? '',
                $dernierHistorique["bges_mission"] ?? '',
                $dernierHistorique["bges_mission_ext"] ?? '',
                $dernierHistorique["bges_consommable"] ?? '',
                $dernierHistorique["bges_equipement"] ?? '',
                $dernierHistorique["bges_envoi_echantillon"] ?? '',
                $dernierHistorique["bges_batiment"] ?? '',
                $dernierHistorique["bges_dechet"] ?? '',
            ];

            $sheet17Array[] = [
                $plat["id"] ?? '',
                $dernierHistorique["publication_equipe_operante"] ?? '',
                $dernierHistorique["publication_collaboration_interne"] ?? '',
                $dernierHistorique["publication_collaboration_externe"] ?? '',
                $dernierHistorique["publication_autre_type"] ?? '',
                $dernierHistorique["publication_these"] ?? '',
            ];

            foreach ($plat["publications"] ?? [] as $publication) {
                $sheet18Array[] = [
                    $plat["id"] ?? '',
                    $publication["numero"] ?? '',
                    $publication["doi"] ?? '',
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
                $this->setSafeCellValue($sheet1, $col . $row, $sheet1Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }


        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Appartenance');
        $sheet3->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet3Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet3Array); $row++) {
                $this->setSafeCellValue($sheet3, $col . $row, $sheet3Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }


        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Tutelles');
        $sheet4->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet4Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet4Array); $row++) {
                $this->setSafeCellValue($sheet4, $col . $row, $sheet4Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet5 = $spreadsheet->createSheet();
        $sheet5->setTitle('Personnels_de_la_Plateforme');
        $sheet5->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet5Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet5Array); $row++) {
                $this->setSafeCellValue($sheet5, $col . $row, $sheet5Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet6 = $spreadsheet->createSheet();
        $sheet6->setTitle('Personnels_Futurs');
        $sheet6->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet6Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet6Array); $row++) {
                $this->setSafeCellValue($sheet6, $col . $row, $sheet6Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet7 = $spreadsheet->createSheet();
        $sheet7->setTitle('Equipements');
        $sheet7->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet7Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet7Array); $row++) {
                $this->setSafeCellValue($sheet7, $col . $row, $sheet7Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet8 = $spreadsheet->createSheet();
        $sheet8->getStyle('1')->getFont()->setBold(true);
        $sheet8->setTitle('Equipements_Contributeurs');
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet8Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet8Array); $row++) {
                $this->setSafeCellValue($sheet8, $col . $row, $sheet8Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet8BisBis = $spreadsheet->createSheet();
        $sheet8BisBis->getStyle('1')->getFont()->setBold(true);
        $sheet8BisBis->setTitle('Equipements_Maintenance');
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet8BisBisArray[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet8BisBisArray); $row++) {
                $this->setSafeCellValue($sheet8BisBis, $col . $row, $sheet8BisBisArray[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet8Bis = $spreadsheet->createSheet();
        $sheet8Bis->getStyle('1')->getFont()->setBold(true);
        $sheet8Bis->setTitle('Equipements_Contrats_Mutualisés');
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet8BisArray[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet8BisArray); $row++) {
                $this->setSafeCellValue($sheet8Bis, $col . $row, $sheet8BisArray[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet9 = $spreadsheet->createSheet();
        $sheet9->setTitle('Prospectives_Equipements');
        $sheet9->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet9Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet9Array); $row++) {
                $this->setSafeCellValue($sheet9, $col . $row, $sheet9Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet10 = $spreadsheet->createSheet();
        $sheet10->setTitle('Prospectives_Eq_Contributeurs');
        $sheet10->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet10Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet10Array); $row++) {
                $this->setSafeCellValue($sheet10, $col . $row, $sheet10Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet12 = $spreadsheet->createSheet();
        $sheet12->setTitle('Locaux');
        $sheet12->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet12Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet12Array); $row++) {
                $this->setSafeCellValue($sheet12, $col . $row, $sheet12Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet13 = $spreadsheet->createSheet();
        $sheet13->setTitle('Origine_ressources');
        $sheet13->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet13Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet13Array); $row++) {
                $this->setSafeCellValue($sheet13, $col . $row, $sheet13Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet14 = $spreadsheet->createSheet();
        $sheet14->setTitle('Détail_Ressources_Tutelles');
        $sheet14->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet14Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet14Array); $row++) {
                $this->setSafeCellValue($sheet14, $col . $row, $sheet14Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }



        $sheet15 = $spreadsheet->createSheet();
        $sheet15->setTitle('Accès_Production');
        $sheet15->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet15Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet15Array); $row++) {
                $this->setSafeCellValue($sheet15, $col . $row, $sheet15Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }


        $sheet16 = $spreadsheet->createSheet();
        $sheet16->setTitle('BGES');
        $sheet16->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet16Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet16Array); $row++) {
                $this->setSafeCellValue($sheet16, $col . $row, $sheet16Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet17 = $spreadsheet->createSheet();
        $sheet17->setTitle('Publications_Détails');
        $sheet17->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet17Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet17Array); $row++) {
                $this->setSafeCellValue($sheet17, $col . $row, $sheet17Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
            }
        }

        $sheet18 = $spreadsheet->createSheet();
        $sheet18->setTitle('Publications');
        $sheet18->getStyle('1')->getFont()->setBold(true);
        $lastLetter = Coordinate::stringFromColumnIndex(count($sheet18Array[0]) + 1);
        for ($col = "A"; $col !== $lastLetter; $col++) {
            for ($row = 1; $row <= count($sheet18Array); $row++) {
                $this->setSafeCellValue($sheet18, $col . $row, $sheet18Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
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
