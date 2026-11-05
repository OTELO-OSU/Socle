<?php

namespace App\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:populate',
    description: 'Importe les données statiques dans la base de données (laboratoires, catégories, BAP, etc.)',
)]
class PopulateCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Demander un mot de passe pour l'utilisateur administrateur
        $io = new SymfonyStyle($input, $output);

        $laboParOsu = $unitesParOsu = [
            'OMP' => [
                'Centre National de Recherches Météorologiques',
                'Centre de formation et de recherche sur les environnements méditerranéens',
                "Centre d'études spatiales de la biosphère",
                'Institut de Recherche en Astrophysique et Planétologie',
                'Climat, Environnement, Couplages et Incertitudes',
                "Laboratoire d'aérologie",
                'Géosciences environnement Toulouse',
                "Laboratoire d'études en Géophysique et océanographie spatiales",
                'Observatoire Midi-Pyrénées',
            ],
            'OASU' => [
                "Laboratoire d'astrophysique de Bordeaux",
                'Environnements et paléoenvironnements océaniques et continentaux',
                'Littoral, Environnement et Sociétés',
                "Observatoire aquitain des sciences de l'univers",
            ],
            'OREME' => [
                'Géosciences Montpellier',
                'Laboratoire Univers et Particules Montpellier',
                'Hydrosciences Montpellier',
                "Observatoire de Recherche Montpelliérain de l'Environnement",
            ],
            'OSUPS' => [
                'Astrophysique, Instrumentation et Modélisation, de Paris-Saclay',
                'Géosciences Paris-Saclay',
                "Institut d'astrophysique spatiale",
            ],
            'OBSPM' => [
                'UNité d’Ingénierie et de Développements Instrumentaux pour l’Astrophysique',
                'Station de Radioastronomie de Nançay',
                "Laboratoire d’Instrumentation et de Recherche en Astrophysique",
                "Laboratoire d’étude de l’Univers et des phénomènes eXtrèmes",
                "Laboratoire d'Etudes Spatiales et d'Instrumentation en Astrophysique",
                'Laboratoire Temps Espace',
            ],
            'OSUG' => [
                "Institut des Géosciences de l'Environnement",
                'ENVIRONNEMENTS, DYNAMIQUES ET TERRITOIRES DE LA MONTAGNE',
                'Institut de Planétologie et d’Astrophysique de Grenoble',
                'Institut des Sciences de la Terre',
                'Observatoire des Sciences de l’Univers de Grenoble',
            ],
            'OCA' => [
                'Astrophysique Relativiste, Théories, Expériences, Métrologie, Instrumentation, Signaux',
                'Laboratoire Joseph-Louis Lagrange',
                'Géoazur',
                "Observatoire de la Côte d'Azur",
            ],
            'OSUL' => [
                'Centre de Recherche Astrophysique de Lyon',
                'Laboratoire de géologie de Lyon : Terre, Planètes, Environnement',
                'Observatoire de Lyon',
            ],
            'PYTHEAS' => [
                'Institut PYTHEAS',
                'Institut méditerranéen d’océanologie',
                "Laboratoire d'Astrophysique de Marseille",
                "Centre européen de recherche et d'enseignement de géosciences de l'environnement",
            ],
            'ObAS' => [
                'Observatoire astronomique de Strasbourg',
            ],
            'IAP' => [
                "Institut d'Astrophysique de Paris",
            ],
            'THETA' => [
                'Institut UTINAM (Univers, Transport, Interfaces Nanostructures, Atmosphère et Environnement, Molécules)',
                'Chrono-environnement',
                'Terre-Homme-Environnement-Temps-Astronomie',
            ],
            'OSUC' => [
                "Observatoire des Sciences de l'Univers en région Centre",
                "Institut des Sciences de la Terre d'Orléans",
                "Laboratoire de physique et chimie de l'environnement et de l'Espace",
                'Centre de biophysique moléculaire',
            ],
            'OSUNA' => [
                'Unité Mixte de Service Observatoire des Sciences de l’Univers Nantes Atlantique',
                'Laboratoire de Planétologie et Géosciences',
            ],
            'EFLUVE' => [
                'Laboratoire inter-universitaire des systèmes atmosphèriques',
                'Unité mixte de service "Enveloppes fluides : de la ville à l\'exobiologie"',
            ],
            'OVSQ' => [
                'Laboratoire "Atmosphères, Milieux, Observations Spatiales"',
                'Laboratoire des sciences du climat et de l’environnement',
                "Unité mixte de service de l'OSU-OVSQ",
                'Institut de Radio Astronomie Millimétrique',
            ],
            'OTELo' => [
                'Centre de recherches pétrographiques et géochimiques',
                'GéoRessources',
                'Laboratoire Interdisciplinaire des Environnements Continentaux',
                'Observatoire Terre et Environnement de Lorraine',
            ],
            'EOST' => [
                'Institut de Physique du Globe de Strasbourg',
                'Ecole et Observatoire des sciences de la Terre de Strasbourg',
            ],
            'OPGC' => [
                'Laboratoire de météorologie physique',
                'Laboratoire Magmas et volcans',
                'Observatoire de physique du globe de Clermont-Ferrand',
            ],
            'OSU-Réunion' => [
                "Laboratoire de l'Atmosphère et des Cyclones",
                'Ecologie Marine Tropicale des Océans Pacifique et Indien',
                "Unité mixte de service de l'OSU de la Réunion",
            ],
            'Ecce Terra' => [
                'Laboratoire de Morphodynamique continentale et côtière',
                'Institut des Sciences de la Terre Paris',
                'Biologie des organismes et écosystèmes aquatiques',
                'Institut de minéralogie, de physique des matériaux et de cosmochimie',
                'Laboratoire de Géologie de l’Ecole Normale Supérieure',
                'Laboratoire de météorologie dynamique',
                'Observatoire des sciences de l’Univers Ecce-Terra',
            ],
            'STAMAR' => [
                "Laboratoire d'océanographie de Villefranche",
                'Adaptation et diversité en milieu marin',
                "Laboratoire d'océanographie microbienne",
            ],
            'IUEM' => [
                "LABORATOIRE D'OCEANOGRAPHIE PHYSIQUE ET SPATIALE",
                'Laboratoire Géosciences Océan',
                "Laboratoire des sciences de l'environnement marin",
            ],
            'IPGP' => [
                'Institut de physique du globe de Paris',
                'Unité mixte de service de de l\'Institut de Physique du Globe de Paris',
            ],
            'OSERen' => [
                'Géosciences Rennes',
                "INSTITUT D'ELECTRONIQUE ET DE TELECOMMUNICATION DE RENNES",
                'Institut de Physique de Rennes',
                'Écosystèmes, biodiversité, évolution',
                'Observatoire des Sciences de l’Environnement de Rennes',
            ],
        ];

        $count = $this->em->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM public.entite'
        );
        if ($count > 0) {
            $io->warning('La base de données contient déjà des laboratoires. Souhaitez-vous effacer les laboratoires existants avant d\'importer les nouveaux ? (ne pas les supprimer pourrait causer des problèmes de contrainte unique)');
            $confirm = $io->confirm('Supprimer les laboratoires existants ?', false);
            if (!$confirm) {
                $io->warning('Importation annulée.');
                return Command::FAILURE;
            } else {
                $this->em->getConnection()->executeStatement('DELETE FROM public.entite');
                $io->success('Les laboratoires existants ont été supprimés.');
            }
        }

        $choice = $io->choice(
            'Pour quel OSU déployez-vous cette application ?',
            [
                "OMP",
                "OASU",
                "OREME",
                "OSUPS",
                "OBSPM",
                "OSUG",
                "OCA",
                "OSUL",
                "PYTHEAS",
                "ObAS",
                "IAP",
                "THETA",
                "OSUC",
                "OSUNA",
                "EFLUVE",
                "OVSQ",
                "OTELo",
                "EOST",
                "OPGC",
                "OSU-Réunion",
                "Ecce Terra",
                "STAMAR",
                "IUEM",
                "IPGP",
                "OSERen",
                "Aucun OSU"
            ]
        );

        // Importer SQL categories
        $this->em->getConnection()->executeStatement('DELETE FROM public.categorie');
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/categories.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer le gros SQL laboratories.sql dans la base de données
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/laboratories.sql');
        $this->em->getConnection()->executeStatement($sql);

        if ($choice === 'Aucun OSU') {
            $io->success('Tous les laboratoires ont été importés avec succès.');
            return Command::SUCCESS;
        }

        $laboratoiresAConserver = $laboParOsu[$choice];

        $deletedCount = $this->em->getConnection()->executeStatement(
            'DELETE FROM public.entite
     WHERE (nom IS NULL OR nom NOT IN (?))
       AND code_unite IS NOT NULL',
            [$laboratoiresAConserver],
            [ArrayParameterType::STRING]
        );

        $io->success(sprintf(
            'Les laboratoires de l’OSU %s ont été importés.',
            $choice,
        ));

        $this->em->getConnection()->executeStatement('DELETE FROM public.bap_emploi_type');
        $this->em->getConnection()->executeStatement('DELETE FROM public.emploi_type');
        $this->em->getConnection()->executeStatement('DELETE FROM public.bap');
        $this->em->getConnection()->executeStatement('DELETE FROM public.cnu');
        $this->em->getConnection()->executeStatement('DELETE FROM public.contrat_maintenance');
        $this->em->getConnection()->executeStatement('DELETE FROM public.corps_grade');
        $this->em->getConnection()->executeStatement('DELETE FROM public.corps');
        $this->em->getConnection()->executeStatement('DELETE FROM public.grade');
        $this->em->getConnection()->executeStatement('DELETE FROM public.determination_tarif_methode');
        $this->em->getConnection()->executeStatement('DELETE FROM public.modalite_recrutement');
        $this->em->getConnection()->executeStatement('DELETE FROM public.operation');
        $this->em->getConnection()->executeStatement('DELETE FROM public.section_cnrs');
        $this->em->getConnection()->executeStatement('DELETE FROM public.type_contrat');
        $this->em->getConnection()->executeStatement('DELETE FROM public.ville');

        // Importer SQL BAP
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/bap.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer SQL Emploi type
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/emploi_type.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer SQL BAP Emploi type
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/bap_emploi_type.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer CNU
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/cnu.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer Contrat de maintenance
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/contrat_maintenance.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer Corps
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/corps.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer Grade
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/grade.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer corps_grade
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/corps_grade.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer determination_tarif_methode
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/determination_tarif_methode.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer modalite_recrutement
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/modalite_recrutement.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer operation
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/operation.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer section_cnrs
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/section_cnrs.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer type_contrat
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/type_contrat.sql');
        $this->em->getConnection()->executeStatement($sql);

        // Importer ville
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/ville.sql');
        $this->em->getConnection()->executeStatement($sql);

        return Command::SUCCESS;
    }
}
