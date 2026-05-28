<?php

namespace App\Service;

use App\Entity\Bap;
use App\Entity\Corps;
use App\Entity\EmploiType;
use App\Entity\Entite;
use App\Entity\Grade;
use App\Entity\HostOrganizations;
use App\Entity\Laboratory;
use App\Entity\PlateformeHistorique;
use App\Entity\TypeContrat;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
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

    // function exportSinglePlatformToExcel($platformId)
    // {
    //     // On récupère la plateforme
    //     $platform = $this->dm->getRepository(Platforms::class)->find($platformId);
    //     if (!$platform) {
    //         // Lève une exception si la plateforme n'existe pas
    //         throw new PlatformNotFoundException();
    //     }
    //     /** @var User $user */
    //     $user = $this->security->getUser();
    //     if (!$user) {
    //         // Lève une exception si l'utilisateur n'est pas connecté
    //         throw new UserNotFoundException();
    //     }

    //     // Si l'utilisateur n'est pas admin on vérifie qu'il y a droit
    //     if (!\in_array("ROLE_ADMIN", $user->getRoles())) {

    //         $boo1 = $this->platformService->isUserInPlatformHosts($platformId, $user);
    //         $boo2 = $this->platformService->isUserInPlatformLaboratoryHosts($platformId, $user);
    //         $boo3 = $this->networkService->isUserNetworkHost($platform->getData()["network"] ?? '', $user->getEmail());

    //         if (!$boo1 && !$boo2 && !$boo3) {
    //             throw new PlatformNotFoundException();
    //         }
    //     }

    //     // Contrôle passé on exporte
    //     return $this->exportPlatformsToExcel([$platform]);
    // }

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

    /**
     * @param PlateformeHistorique[] $platforms
     */
    public function exportPlatformeArrayEnExcel(array $platforms)
    {
        // TODO
        // $sheet1Array = [];
        // $sheet1Array[] = [
        //     "ID_Plateforme",
        //     "Nom_Officiel",
        //     "Forme_Développée_Nom_Officiel",
        //     "Instrument_National",
        //     "Autre_Labellisation",
        //     "Site_Web",
        //     "Ville",
        //     "Logo_URL"
        // ];

        // $sheet3Array = [];
        // $sheet3Array[] = [
        //     "ID_Plateforme",
        //     "Laboratoire_INSU_OSU_hebergeant_la_plateforme",
        //     "Nom_Directeur",
        //     "Prénom_Directeur",
        //     "Email_Directeur",
        //     "Latitude_Plateforme",
        //     "Longitude_Plateforme",
        // ];

        // $sheet4Array = [];
        // $sheet4Array[] = [
        //     "ID_Plateforme",
        //     "Statut",
        //     "Tutelle"
        // ];

        // $sheet5Array = [];
        // $sheet5Array[] = [
        //     "ID_Plateforme",
        //     "Responsable_Scientifique",
        //     "Responsable_Technique",
        //     "Responsable_Plateforme",
        //     "Contrat",
        //     "Nom",
        //     "Prenom",
        //     "Email",
        //     "Employeur",
        //     "Corps",
        //     "Grade",
        //     "BAP_1",
        //     "BAP_2",
        //     "Emploi_Type_1",
        //     "Emploi_Type_2",
        //     "Quotite"
        // ];


        // /** @var PlateformeHistorique $plat */
        // foreach ($platforms as $plat) {
        //     $sheet1Array[] = [
        //         $plat->getId(),
        //         $plat->getNomOfficiel(),
        //         $plat->getFormeDeveloppeeNomOfficiel(),
        //         $plat->getPlateforme()->isInstrumentNational() ? 'Oui' : 'Non',
        //         $plat->getAutreTypeLabellisation() ?? '',
        //         $plat->getSiteWeb() ?? '',
        //         $this->formatVille($plat->getVille()),
        //         $plat->getLogoUrl() ?? '',
        //     ];

        //     $prenomDirLab = '';
        //     $nomDirLab = '';
        //     $emailDirLab = '';
        //     $laboOperateur = $plat->getLaboratoireOperateur();
        //     if ($laboOperateur) {
        //         $dir = $laboOperateur->getDirecteur();
        //         if ($dir) {
        //             $prenomDirLab = $dir->getPrenom() ?? '';
        //             $nomDirLab = $dir->getNom() ?? '';
        //             $emailDirLab = $dir->getEmail() ?? '';
        //         }
        //     }

        //     $sheet3Array[] = [
        //         $plat->getId(),
        //         $this->formatLabo($plat->getLaboratoireOperateur()),
        //         $nomDirLab,
        //         $prenomDirLab,
        //         $emailDirLab,
        //         $plat->getLatitude() ?? '',
        //         $plat->getLongitude() ?? '',
        //     ];

        //     $tutelles = $plat->getPlateformeHistoriqueTutelles();

        //     foreach ($tutelles as $tutelle) {
        //         $sheet4Array[] = [
        //             $plat->getId(),
        //             $tutelle->isPrincipale() ? 'Principale' : 'Secondaire',
        //             $this->formatFinanceur($tutelle->getTutelle()),
        //         ];
        //     }

        //     $personnels = $plat->getPlateformeHistoriquePersonnels();

        //     foreach ($personnels as $pers) {
        //         $baps = $pers->getUtilisateur()->getUtilisateurBaps();
        //         $emploiTypes = $pers->getUtilisateur()->getUtilisateurEmploiTypes();
        //         $sheet5Array[] = [
        //             $plat->getId(),
        //             $pers->isResponsableScientifique() ? 'Oui' : 'Non',
        //             $pers->isResponsableTechnique() ? 'Oui' : 'Non',
        //             $pers->isResponsablePlateforme() ? 'Oui' : 'Non',
        //             $this->formatContratPersonnel($pers->getUtilisateur()->getTypeContrat()),
        //             $pers->getUtilisateur()->getNom() ?? '',
        //             $pers->getUtilisateur()->getPrenom() ?? '',
        //             $pers->getUtilisateur()->getEmail() ?? '',
        //             $this->formatFinanceur($pers->getUtilisateur()->getEmployeur()),
        //             $this->formatCorps($pers->getUtilisateur()->getCorps()),
        //             $this->formatGrade($pers->getUtilisateur()->getGrade()),
        //             $baps[0] ? $this->formatBap($baps[0]->getBap()) : '',
        //             $baps[1] ? $this->formatBap($baps[1]->getBap()) : '',
        //             $emploiTypes[0] ? $this->formatEmploiType($emploiTypes[0]->getEmploiType()) : '',
        //             $emploiTypes[1] ? $this->formatEmploiType($emploiTypes[1]->getEmploiType()) : '',
        //             $pers->getQuotite() ?? '',
        //         ];
        //     }
        // }


        // $spreadsheet = new Spreadsheet();
        // $sheet1 = $spreadsheet->getActiveSheet();

        // $sheet1->setTitle('Renseignements_Généraux');
        // $sheet1->getStyle('1')->getFont()->setBold(true);


        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet1Array[0]) + 1);

        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet1Array); $row++) {
        //         $sheet1->setCellValue($col . $row, $sheet1Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }


        // $sheet3 = $spreadsheet->createSheet();
        // $sheet3->setTitle('Appartenance');
        // $sheet3->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet3Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet3Array); $row++) {
        //         $sheet3->setCellValue($col . $row, $sheet3Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }


        // $sheet4 = $spreadsheet->createSheet();
        // $sheet4->setTitle('Tutelles');
        // $sheet4->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet4Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet4Array); $row++) {
        //         $sheet4->setCellValue($col . $row, $sheet4Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet5 = $spreadsheet->createSheet();
        // $sheet5->setTitle('Responsables_de_Plateforme');
        // $sheet5->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet5Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet5Array); $row++) {
        //         $sheet5->setCellValue($col . $row, $sheet5Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet6Array = [];
        // $sheet6Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "Type_Personnel",
        //     "Nom_Personnel",
        //     "Prenom_Personnel",
        //     "Email_Personnel",
        //     "Employeur_Personnel",
        //     "Corps_Personnel",
        //     "BAP_1_Personnel",
        //     "BAP_2_Personnel",
        //     "Emploi_Type_1_Personnel",
        //     "Emploi_Type_2_Personnel",
        //     "Section_CNRS_Personnel",
        //     "CNU_Personnel",
        //     "Grade_Personnel",
        //     "Origine_Financement_Personnel",
        //     "Universite_Rattachement_Personnel",
        //     "ETPT_Personnel",
        //     "Nombre"
        // ];

        // /** @var Platform $plat */
        // foreach ($platforms as $plat) {
        //     foreach ($plat->getData()["statutoryTechnicalEngineerStaff"] as $personnel) {
        //         $sheet6Array[] = [
        //             $plat->getId(),
        //             $plat->getData()["name"] ?? '',
        //             "IT statutaire",
        //             $personnel["name"] ?? '',
        //             $personnel["firstname"] ?? '',
        //             $personnel["email"] ?? '',
        //             $this->convertNameArrayToString($personnel["employer"] ?? []),
        //             $personnel["body"]["label"] ?? '',
        //             $personnel["pba"][0] ?? '',
        //             $personnel["pba"][1] ?? '',
        //             $personnel["typicalJob"][0] ?? '',
        //             $personnel["typicalJob"][1] ?? '',
        //             $personnel["cnrsSection"] ?? '',
        //             $personnel["ncu"] ?? '',
        //             $personnel["grade"] ?? '',
        //             "",
        //             "",
        //             $personnel["quota"] ?? '',
        //             ""
        //         ];
        //     }

        //     foreach ($plat->getData()["ftcTechnicalEngineerStaff"] as $personnel) {
        //         $sheet6Array[] = [
        //             $plat->getId(),
        //             $plat->getData()["name"] ?? '',
        //             "IT CDD",
        //             $personnel["name"] ?? '',
        //             $personnel["firstname"] ?? '',
        //             $personnel["email"] ?? '',
        //             $this->convertNameArrayToString($personnel["employer"] ?? []),
        //             $personnel["body"]["label"] ?? '',
        //             $personnel["pba"][0] ?? '',
        //             $personnel["pba"][1] ?? '',
        //             $personnel["typicalJob"][0] ?? '',
        //             $personnel["typicalJob"][1] ?? '',
        //             $personnel["cnrsSection"] ?? '',
        //             $personnel["ncu"] ?? '',
        //             $personnel["grade"] ?? '',
        //             $this->convertNameArrayToString($personnel["funder"] ?? []),
        //             "",
        //             $personnel["quota"] ?? '',
        //             ""
        //         ];
        //     }

        //     foreach ($plat->getData()["statutoryResearchStaff"] as $personnel) {
        //         $sheet6Array[] = [
        //             $plat->getId(),
        //             $plat->getData()["name"] ?? '',
        //             "Chercheur statutaire",
        //             $personnel["name"] ?? '',
        //             $personnel["firstname"] ?? '',
        //             $personnel["email"] ?? '',
        //             $this->convertNameArrayToString($personnel["employer"] ?? []),
        //             $personnel["body"]["label"] ?? '',
        //             $personnel["pba"][0] ?? '',
        //             $personnel["pba"][1] ?? '',
        //             $personnel["typicalJob"][0] ?? '',
        //             $personnel["typicalJob"][1] ?? '',
        //             $personnel["cnrsSection"] ?? '',
        //             $personnel["ncu"] ?? '',
        //             $personnel["grade"] ?? '',
        //             "",
        //             "",
        //             $personnel["quota"] ?? '',
        //             ""
        //         ];
        //     }

        //     foreach ($plat->getData()["ftcResearchStaff"] as $personnel) {
        //         $sheet6Array[] = [
        //             $plat->getId(),
        //             $plat->getData()["name"] ?? '',
        //             "Chercheur CDD",
        //             $personnel["name"] ?? '',
        //             $personnel["firstname"] ?? '',
        //             $personnel["email"] ?? '',
        //             $this->convertNameArrayToString($personnel["employer"] ?? []),
        //             $personnel["body"]["label"] ?? '',
        //             $personnel["pba"][0] ?? '',
        //             $personnel["pba"][1] ?? '',
        //             $personnel["typicalJob"][0] ?? '',
        //             $personnel["typicalJob"][1] ?? '',
        //             $personnel["cnrsSection"] ?? '',
        //             $personnel["ncu"] ?? '',
        //             $personnel["grade"] ?? '',
        //             $this->convertNameArrayToString($personnel["funder"] ?? []),
        //             "",
        //             $personnel["quota"] ?? '',
        //             ""
        //         ];
        //     }

        //     foreach ($plat->getData()["phdStudent"] as $personnel) {
        //         $sheet6Array[] = [
        //             $plat->getId(),
        //             $plat->getData()["name"] ?? '',
        //             "Doctorant",
        //             $personnel["name"] ?? '',
        //             $personnel["firstname"] ?? '',
        //             $personnel["email"] ?? '',
        //             $this->convertNameArrayToString($personnel["employer"] ?? []),
        //             '',
        //             '',
        //             '',
        //             '',
        //             '',
        //             '',
        //             '',
        //             '',
        //             "",
        //             $personnel["affiliatedUniversity"] ?? "",
        //             $personnel["quota"] ?? '',
        //             ""
        //         ];
        //     }

        //     $sheet6Array[] = [
        //         $plat->getId(),
        //         $plat->getData()["name"] ?? '',
        //         "Autres Étudiants",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         $plat->getData()["numberOtherStudents"] ?? '',
        //     ];

        //     $sheet6Array[] = [
        //         $plat->getId(),
        //         $plat->getData()["name"] ?? '',
        //         "Apprentis/Alternants",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         "",
        //         $plat->getData()["numberApprenticesWorkStudyStudents"] ?? '',
        //     ];
        // }

        // $sheet6 = $spreadsheet->createSheet();
        // $sheet6->setTitle('Personnels');
        // $sheet6->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet6Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet6Array); $row++) {
        //         $sheet6->setCellValue($col . $row, $sheet6Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet6BisArray = [];
        // $sheet6BisArray[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "Employer",
        //     "Corps",
        //     "BAP",
        //     "Emploi_Type",
        //     "Quotite_Temps_Plateforme",
        //     "Rang_Priorités",
        // ];

        // foreach ($platforms as $plt) {
        //     if (\array_key_exists("futurJobs", $plt->getData())) {
        //         foreach ($plt->getData()["futurJobs"] as $fj) {
        //             $pbaChain = "";
        //             if (!empty($fj["pba"])) {
        //                 foreach ($fj["pba"] as $key => $pbaAcronym) {
        //                     if ($key > 0) {
        //                         $pbaChain .= " ; ";
        //                     }
        //                     $pbaChain .= $pbaAcronym;
        //                 }
        //             }

        //             $typicalJobChain = "";
        //             if (!empty($fj["typicalJob"])) {
        //                 foreach ($fj["typicalJob"] as $key => $typicalJobLabel) {
        //                     if ($key > 0) {
        //                         $typicalJobChain .= " ; ";
        //                     }
        //                     $typicalJobChain .= $typicalJobLabel;
        //                 }
        //             }
        //             $sheet6BisArray[] = [
        //                 $plat->getId(),
        //                 $plat->getData()["name"] ?? '',
        //                 isset($fj["employer"]["acronym"]) ? (isset($fj["employer"]["label"]) ? ($fj["employer"]["acronym"] . ' - ' . $fj["employer"]["label"]) : (isset($fj["employer"]["label"]) ? $fj["employer"]["label"] : '')) : '',
        //                 isset($fj["body"]["label"]) ? $fj["body"]["label"] : '',
        //                 $pbaChain,
        //                 $typicalJobChain,
        //                 isset($fj["quota"]) ? $fj["quota"] : '',
        //                 isset($fj["number"]) ? $fj["number"] : 'N/A',
        //             ];
        //         }
        //     }
        // }

        // $sheet6Bis = $spreadsheet->createSheet();
        // $sheet6Bis->setTitle('Personnels_Futurs');
        // $sheet6Bis->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet6BisArray[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet6BisArray); $row++) {
        //         $sheet6Bis->setCellValue($col . $row, $sheet6BisArray[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }


        // $sheet7Array = [];
        // $sheet7Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "#TID_Equipement",
        //     "#Equipement",
        //     "Nom_Equipement",
        //     "Marque",
        //     "Modèle",
        //     "Annee_achat/construction_Equipement",
        //     "Cout_Achat_k€_Equipemenent",
        //     "Valeur_Actuelle_k€_Equipement",
        //     "Amortissement_annee",

        // ];

        // $sheet8Array = [];
        // $sheet8Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "#TID_Equipement",
        //     "#Equipement",
        //     "Contributeur",
        //     "Pourcentage_Contribution",
        // ];

        // $sheet8BisBisArray = [];
        // $sheet8BisBisArray[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "#TID_Equipement",
        //     "#Equipement",
        //     "Type_Contrat_Maintenance",
        //     "Commentaire_Nature_Contrat",
        //     "Fournisseur_Entreprise_Contrat",
        //     "Contrat_Mutualisé_Autres_Instruments",
        //     "Cout_K_AN",
        // ];

        // $sheet8BisArray = [];
        // $sheet8BisArray[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "#TID_Equipement",
        //     "#Equipement",
        //     "#TID_Equipement_Mutualise",
        //     "#Equipement_Mutualise"
        // ];

        // /** @var Platform $plat */
        // foreach ($platforms as $plat) {
        //     foreach ($plat->getData()["currentEquipments"] as $index => $equipement) {
        //         $index = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
        //         $nomEquippement = $plat->getData()["name"] . "_EQ_" . ($index);
        //         $date = "";
        //         if (isset($equipement["purchaseDate"])) {
        //             if ($equipement["purchaseDate"] == "Avant 1998" || $equipement["purchaseDate"] == "avant 1998") $date = "1998";
        //             else $date = $equipement["purchaseDate"];
        //         }
        //         $sheet7Array[] = [
        //             $plat->getId(),
        //             $plat->getData()["name"] ?? '',
        //             $equipement["uid"] ?? "",
        //             $nomEquippement ?? "",
        //             $equipement["name"] ?? "",
        //             $equipement["brand"] ?? "",
        //             $equipement["model"] ?? "",
        //             $date ?? "",
        //             $equipement["cost"] ?? "",
        //             $equipement["replacementValue"] ?? "",
        //             $equipement["amortizationPeriod"] ?? "",

        //         ];

        //         foreach ($equipement["financingDetails"] as $financement) {
        //             $sheet8Array[] = [
        //                 $plat->getId(),
        //                 $plat->getData()["name"] ?? '',
        //                 $equipement["uid"] ?? "",
        //                 $nomEquippement ?? "",
        //                 $this->convertNameArrayToString($financement["label"] ?? []),
        //                 $financement["percent"] ?? ""
        //             ];
        //         }

        //         $sheet8BisBisArray[] = [
        //             $plat->getId(),
        //             $plat->getData()["name"] ?? '',
        //             $equipement["uid"] ?? "",
        //             $nomEquippement ?? "",
        //             $equipement["maintenanceContract"] ?? "",
        //             $equipement["maintenanceComment"] ?? "",
        //             $equipement["maintenanceFournisseur"] ?? "",
        //             $equipement["maintenanceContractOtherInstrument"] ?? "",
        //             $equipement["maintenanceCost"] ?? "",
        //         ];

        //         if (\array_key_exists("maintenanceMutuContract", $equipement)) {
        //             foreach ($equipement["maintenanceMutuContract"] as $mutu) {

        //                 $equipementMutuIndex = 0;

        //                 foreach ($plat->getData()["currentEquipments"] as $idx => $eqm) {
        //                     if ($eqm["uid"] == $mutu) {
        //                         $equipementMutuIndex = $idx;
        //                         break;
        //                     }
        //                 }

        //                 $nomEquippementMutu = $plat->getData()["name"] . "_EQ_" . str_pad($equipementMutuIndex + 1, 3, '0', STR_PAD_LEFT);

        //                 $sheet8BisArray[] = [
        //                     $plat->getId(),
        //                     $plat->getData()["name"] ?? '',
        //                     $equipement["uid"] ?? "",
        //                     $nomEquippement ?? "",
        //                     $mutu,
        //                     $nomEquippementMutu
        //                 ];
        //             }
        //         }
        //     }
        // }

        // $sheet7 = $spreadsheet->createSheet();
        // $sheet7->setTitle('Equipements');
        // $sheet7->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet7Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet7Array); $row++) {
        //         $sheet7->setCellValue($col . $row, $sheet7Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet8 = $spreadsheet->createSheet();
        // $sheet8->getStyle('1')->getFont()->setBold(true);
        // $sheet8->setTitle('Equipements_Contributeurs');
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet8Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet8Array); $row++) {
        //         $sheet8->setCellValue($col . $row, $sheet8Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet8BisBis = $spreadsheet->createSheet();
        // $sheet8BisBis->getStyle('1')->getFont()->setBold(true);
        // $sheet8BisBis->setTitle('Equipements_Maintenance');
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet8BisBisArray[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet8BisBisArray); $row++) {
        //         $sheet8BisBis->setCellValue($col . $row, $sheet8BisBisArray[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet8Bis = $spreadsheet->createSheet();
        // $sheet8Bis->getStyle('1')->getFont()->setBold(true);
        // $sheet8Bis->setTitle('Equipements_Contrats_Mutualisés');
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet8BisArray[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet8BisArray); $row++) {
        //         $sheet8Bis->setCellValue($col . $row, $sheet8BisArray[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet9Array = [];
        // $sheet9Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "#TID_Equipement",
        //     "#Equipement",
        //     "Nom_Equipement_Prospective",
        //     "Marque",
        //     "Modèle",
        //     "Annee_achat_Equipement_Prospective",
        //     "Cout_Achat_k€_Equipemenent_Prospective",
        // ];

        // $sheet10Array = [];
        // $sheet10Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "#TID_Equipement",
        //     "#Equipement",
        //     "Prospective_Contributeur",
        //     "Prospective_Pourcentage_Contribution",
        // ];

        // /** @var Platform $plat */
        // foreach ($platforms as $plat) {
        //     foreach ($plat->getData()["futureEquipments"] as $index => $equipement) {
        //         $index = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
        //         $nomEquippement = $plat->getData()["name"] . "_EQ_PRO_" . ($index);
        //         $sheet9Array[] = [
        //             $plat->getId(),
        //             $plat->getData()["name"] ?? '',
        //             $equipement["uid"] ?? "",
        //             $nomEquippement ?? "",
        //             $equipement["name"] ?? "",
        //             $equipement["brand"] ?? "",
        //             $equipement["model"] ?? "",
        //             $equipement["plannedPurchaseDate"] ?? "",
        //             $equipement["cost"] ?? "",
        //         ];

        //         foreach ($equipement["financingDetails"] as $financement) {
        //             $sheet10Array[] = [
        //                 $plat->getId(),
        //                 $plat->getData()["name"] ?? '',
        //                 $equipement["uid"] ?? "",
        //                 $nomEquippement ?? "",
        //                 $this->convertNameArrayToString($financement["label"] ?? []),
        //                 $financement["percent"] ?? ""
        //             ];
        //         }
        //     }
        // }

        // $sheet9 = $spreadsheet->createSheet();
        // $sheet9->setTitle('Prospectives_Equipements');
        // $sheet9->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet9Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet9Array); $row++) {
        //         $sheet9->setCellValue($col . $row, $sheet9Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet10 = $spreadsheet->createSheet();
        // $sheet10->setTitle('Prospectives_Eq_Contributeurs');
        // $sheet10->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet10Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet10Array); $row++) {
        //         $sheet10->setCellValue($col . $row, $sheet10Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet11Array = [];
        // $sheet11Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "Materiel_Consommables_k€",
        //     "Contrats_Entretien_k€",
        //     "Etudes_Prestations_k€",
        //     "Fluides_Energies_k€",
        //     "Missions_Frais_Generaux_k€",
        //     "Somme_Coûts_k€",
        // ];



        // /** @var Platform $plat */
        // foreach ($platforms as $plat) {
        //     $sheet11Array[] = [
        //         $plat->getId(),
        //         $plat->getData()["name"] ?? '',
        //         $plat->getData()["materialsConsumablesCost"] ?? "",
        //         $plat->getData()["maintenanceContractsCost"] ?? "",
        //         $plat->getData()["studiesServicesCost"] ?? "",
        //         $plat->getData()["fluidsEnergyCost"] ?? "",
        //         $plat->getData()["missionOverheadsCost"] ?? "",
        //         (int)$plat->getData()["materialsConsumablesCost"] + (int)$plat->getData()["maintenanceContractsCost"] + (int)$plat->getData()["studiesServicesCost"] + (int)$plat->getData()["fluidsEnergyCost"] + (int)$plat->getData()["missionOverheadsCost"] ?? "",

        //     ];
        // }

        // $sheet11 = $spreadsheet->createSheet();
        // $sheet11->setTitle('Coût_Fonctionnement');
        // $sheet11->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet11Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet11Array); $row++) {
        //         $sheet11->setCellValue($col . $row, $sheet11Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }



        // $sheet12Array = [];
        // $sheet12Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "Bureaux_m2",
        //     "Laboratoire_m2",
        // ];

        // /** @var Platform $plat */
        // foreach ($platforms as $plat) {
        //     $sheet12Array[] = [
        //         $plat->getId(),
        //         $plat->getData()["name"] ?? '',
        //         $plat->getData()["officeSpace"] ?? "",
        //         $plat->getData()["laboratorySpace"] ?? "",
        //     ];
        // }

        // $sheet12 = $spreadsheet->createSheet();
        // $sheet12->setTitle('Locaux');
        // $sheet12->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet12Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet12Array); $row++) {
        //         $sheet12->setCellValue($col . $row, $sheet12Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet13Array = [];
        // $sheet13Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "Ressources_Tutelles",
        //     "Ressources_Prestations_Fonds_Publics",
        //     "Ressources_Prestations_Fonds_Privés",
        //     "Ressources_Crédits_Laboratoire",
        //     "Ressources_Projet_Recherche",
        //     "Ressources_Structures_PIA",
        //     "Dotation_INSU_IN",
        // ];

        // $sheet14Array = [];
        // $sheet14Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "Tutelle",
        //     "taux_Contribution_Tutelle",
        // ];

        // /** @var Platform $plat */
        // foreach ($platforms as $plat) {
        //     $endowment = "N/A";
        //     if ($plat->isNationalInstrument()) {
        //         $endowment = $plat->getData()["endowmentFromInsu"] ?? "Non Renseigné";
        //     }
        //     $sheet13Array[] = [
        //         $plat->getId(),
        //         $plat->getData()["name"] ?? '',
        //         $plat->getData()["hostInstitutionPricingContribution"]["percent"] ?? "",
        //         $plat->getData()["publicFundsPricingContribution"] ?? "",
        //         $plat->getData()["privateFundsPricingContribution"] ?? "",
        //         $plat->getData()["laboratoryContribution"] ?? "",
        //         $plat->getData()["researchProjectsContribution"] ?? "",
        //         $plat->getData()["piaStructuresContribution"] ?? "",
        //         $endowment,

        //     ];

        //     if (!empty($plat->getData()["hostInstitutionPricingContribution"]["percent"])) {
        //         foreach ($plat->getData()["hostInstitutionPricingContribution"]["details"] as $detail) {
        //             $sheet14Array[] = [
        //                 $plat->getId(),
        //                 $plat->getData()["name"] ?? '',
        //                 $this->convertNameArrayToString($detail["label"] ?? []),
        //                 $detail["percent"],
        //             ];
        //         }
        //     }
        // }

        // $sheet13 = $spreadsheet->createSheet();
        // $sheet13->setTitle('Origine_ressources');
        // $sheet13->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet13Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet13Array); $row++) {
        //         $sheet13->setCellValue($col . $row, $sheet13Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet14 = $spreadsheet->createSheet();
        // $sheet14->setTitle('Détail_Ressources_Tutelles');
        // $sheet14->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet14Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet14Array); $row++) {
        //         $sheet14->setCellValue($col . $row, $sheet14Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet15Array = [];
        // $sheet15Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "Tarifs_Plateforme",
        //     "Certification",
        //     "Libellé_Certification",
        //     "Procedure_acces_prestations_exterieures",
        //     "Procedure_acces_tous_projets",
        //     "Fonctionnement_7/7_24/24",
        //     "Fonctionnement_7/7_12/24",
        //     "Fonctionnement_5/7_24/24",
        //     "Fonctionnement_5/7_12/24",
        //     "Taux_Utilisation_Fonctionnement",
        //     "Taux_Utilisation_Maintenance",
        //     "Taux_Utilisation_Developpement",
        //     "Taux_Utilisation_En_Panne",
        //     "Taux_Utilisation_Temps_Mort",
        //     "Taux_Utilisation_Equipe",
        //     "Taux_Utilisation_Collaboration_Interne",
        //     "Taux_Utilisation_Collaboration_Externe",
        //     "Taux_Utilisation_Prestation",
        //     "Accueil_International_National",
        // ];

        // /** @var Platform $plat */
        // foreach ($platforms as $plat) {
        //     $sheet15Array[] = [
        //         $plat->getId(),
        //         $plat->getData()["name"] ?? '',
        //         $plat->getData()["pricingType"] ?? "",
        //         $plat->getData()["hasQualityLabelCertification"] == true ? "Oui" : "Non",
        //         $plat->getData()["QualityLabelCertification"] ?? "",
        //         $plat->getData()["transparentSelectionProcedureAccessInstrumentExternalService"] == true ? "Oui" : "Non",
        //         $plat->getData()["transparentSelectionProcedureAccessInstrumentAllProjects"] == true ? "Oui" : "Non",
        //         $plat->getData()["model772424"] ?? "",
        //         $plat->getData()["model771224"] ?? "",
        //         $plat->getData()["model572424"] ?? "",
        //         $plat->getData()["model571224"] ?? "",
        //         $plat->getData()["rateUsageOperating"] ?? "",
        //         $plat->getData()["rateUsageMaintenance"] ?? "",
        //         $plat->getData()["rateUsageDevelopment"] ?? "",
        //         $plat->getData()["rateUsageOutOfOrder"] ?? "",
        //         $plat->getData()["rateUsageIdleTime"] ?? "",
        //         $plat->getData()["rateUseOperatingTeam"] ?? "",
        //         $plat->getData()["rateUseInternalCollaboration"] ?? "",
        //         $plat->getData()["rateUseExternalCollaboration"] ?? "",
        //         $plat->getData()["rateUseServiceProvision"] ?? "",
        //         $plat->getData()["rateInternationalGuests"] ?? "",
        //     ];
        // }

        // $sheet15 = $spreadsheet->createSheet();
        // $sheet15->setTitle('Accès_Production');
        // $sheet15->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet15Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet15Array); $row++) {
        //         $sheet15->setCellValue($col . $row, $sheet15Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $sheet16Array = [];
        // $sheet16Array[] = [
        //     "TID_Plateforme",
        //     "ID_Plateforme",
        //     "BGES_Realise",
        //     "Domicile_Travail",
        //     "Missions",
        //     "Missions_Ext",
        //     "Consommables",
        //     "Equipements",
        //     "Envoi_Des_Echantillons",
        //     "Batiments",
        //     "Dechets"
        // ];

        // /** @var Platform $plat */
        // foreach ($platforms as $plat) {
        //     $sheet16Array[] = [
        //         $plat->getId(),
        //         $plat->getData()["name"] ?? '',
        //         ($plat->getData()["isBgesRealised"] ?? false) ? "Oui" : "Non",
        //         $plat->getData()["bgesFieldOfWork"] ?? "",
        //         $plat->getData()["bgesMission"] ?? "",
        //         $plat->getData()["bgesExternalMission"] ?? "",
        //         $plat->getData()["bgesConsumables"] ?? "",
        //         $plat->getData()["bgesEquipments"] ?? "",
        //         $plat->getData()["bgesSampleSending"] ?? "",
        //         $plat->getData()["bgesBuilding"] ?? "",
        //         $plat->getData()["bgesWaste"] ?? "",
        //     ];
        // }

        // $sheet16 = $spreadsheet->createSheet();
        // $sheet16->setTitle('BGES');
        // $sheet16->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet16Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet16Array); $row++) {
        //         $sheet16->setCellValue($col . $row, $sheet16Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }


        // $sheet17Array = [];

        // // Voir le nombre de publications max dans le foreach pour les publications marquantes et les publications prestigieuse
        // $maxPublicationsMarquantes = 0;
        // foreach ($platforms as $plat) {
        //     $publications = $plat->getData()["publications"] ?? [];
        //     //filter les publications marquantes
        //     $publications = array_filter($publications, fn($pub) => $pub["type"] == "Publications marquantes de l'année");
        //     $maxPublicationsMarquantes = max($maxPublicationsMarquantes, count($publications));
        // }

        // $maxPublicationsPrestigieuses = 0;
        // foreach ($platforms as $plat) {
        //     $publications = $plat->getData()["publications"] ?? [];
        //     //filter les publications prestigieuses
        //     $publications = array_filter($publications, fn($pub) => $pub["type"] == "Publications prestigieuses de la plateforme ayant contribuées à des avancées scientifiques majeures");
        //     $maxPublicationsPrestigieuses = max($maxPublicationsPrestigieuses, count($publications));
        // }

        // $sheet17Array[] = [
        //     "TID_Plateforme",
        //     "ID_PLateforme",
        //     "#Publications_Equipe",
        //     "#Publications_Collaboration_Interne",
        //     "#Publications_Collaboration_Externe",
        //     "#Publications_Autres",
        //     "#Thèses",
        // ];

        // // Ajouter les headers des publications marquantes
        // for ($i = 1; $i <= $maxPublicationsMarquantes; $i++) {
        //     $sheet17Array[0][] = "Publication_M_DOI_$i";
        // }

        // // Ajouter les headers des publications prestigieuses
        // for ($i = 1; $i <= $maxPublicationsPrestigieuses; $i++) {
        //     $sheet17Array[0][] = "Publication_R_DOI_$i";
        // }

        // // Récupérer les publications marquantes
        // foreach ($platforms as $plat) {
        //     $publications = $plat->getData()["publications"] ?? [];
        //     //filter les publications marquantes
        //     $publicationsMarquantes = array_filter($publications, fn($pub) => $pub["type"] == "Publications marquantes de l'année");
        //     $publicationsPrestigieuses = array_filter($publications, fn($pub) => $pub["type"] == "Publications prestigieuses de la plateforme ayant contribuées à des avancées scientifiques majeures");
        //     $row = [
        //         $plat->getId(),
        //         $plat->getData()["name"] ?? '',
        //         $plat->getData()["numberPublicationsOperatingTeam"] ?? '',
        //         $plat->getData()["numberPublicationsInternalCollaboration"] ?? '',
        //         $plat->getData()["numberPublicationsExternalCollaboration"] ?? '',
        //         $plat->getData()["numberOthersPublications"] ?? '',
        //         $plat->getData()["thesesLinkedPlatform"] ?? ''
        //     ];

        //     $it = 0;
        //     foreach ($publicationsMarquantes as $pub) {
        //         $row[] = $pub["value"] ?? "";
        //         $it++;
        //     }

        //     if ($it < $maxPublicationsMarquantes) {
        //         for ($i = $it; $i < $maxPublicationsMarquantes; $i++) {
        //             $row[] = "";
        //         }
        //     }

        //     $it = 0;

        //     foreach ($publicationsPrestigieuses as $pub) {
        //         $row[] = $pub["value"] ?? "";
        //         $it++;
        //     }

        //     if ($it < $maxPublicationsPrestigieuses) {
        //         for ($i = $it; $i < $maxPublicationsPrestigieuses; $i++) {
        //             $row[] = "";
        //         }
        //     }

        //     $sheet17Array[] = $row;
        // }

        // $sheet17 = $spreadsheet->createSheet();
        // $sheet17->setTitle('Publications');
        // $sheet17->getStyle('1')->getFont()->setBold(true);
        // $lastLetter = Coordinate::stringFromColumnIndex(count($sheet17Array[0]) + 1);
        // for ($col = "A"; $col !== $lastLetter; $col++) {
        //     for ($row = 1; $row <= count($sheet17Array); $row++) {
        //         $sheet17->setCellValue($col . $row, $sheet17Array[$row - 1][Coordinate::columnIndexFromString($col) - 1]);
        //     }
        // }

        // $writer = new Xlsx($spreadsheet);
        // ob_start();
        // $writer->save('php://output');
        // $content = ob_get_clean();

        // $spreadsheet->disconnectWorksheets();
        // unset($spreadsheet, $writer);

        // return $content;
    }
}
