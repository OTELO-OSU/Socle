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
                'UMR3589',
                'UMR5110',
                'UMR5126',
                'UMR5277',
                'UMR5318',
                'UMR5560',
                'UMR5563',
                'UMR5566',
                'UAR831',
            ],
            'OASU' => [
                'UMR5804',
                'UMR5805',
                'UMR7266',
                'UAR2567',
            ],
            'OREME' => [
                'UMR5243',
                'UMR5299',
                'UMR5569',
                'UAR3282',
            ],
            'OSUPS' => [
                'UMR7158',
                'UMR8148',
                'UMR8617',
            ],
            'OBSPM' => [
                'UAR2050',
                'UAR704',
                'UMR8254',
                'UMR8262',
                'UMR8109',
                'UMR8255',
            ],
            'OSUG' => [
                'UMR5001',
                'UMR5204',
                'UMR5274',
                'UMR5275',
                'UAR832',
            ],
            'OCA' => [
                'UMR7250',
                'UMR7293',
                'UMR7329',
                'UAR2202',
            ],
            'OSUL' => [
                'UMR5574',
                'UMR5276',
                'UAR3721',
            ],
            'PYTHEAS' => [
                'UAR3470',
                'UMR7294',
                'UMR7326',
                'UMR7330',
            ],
            'ObAS' => [
                'UMR7550',
            ],
            'IAP' => [
                'UMR7095',
            ],
            'THETA' => [
                'UMR6213',
                'UMR6249',
                'UAR3245',
            ],
            'OSUC' => [
                'UAR3116',
                'UMR7327',
                'UMR7328',
                'UPR4301',
            ],
            'OSUNA' => [
                'UAR3281',
                'UMR6112',
            ],
            'EFLUVE' => [
                'UMR7583',
                'UAR3563',
            ],
            'OVSQ' => [
                'UMR8190',
                'UMR8212',
                'UAR3342',
                'UPS2074',
            ],
            'OTELo' => [
                'UMR7358',
                'UMR7359',
                'UMR7360',
                'UAR3562',
            ],
            'EOST' => [
                'UMR7516',
                'UAR830',
            ],
            'OPGC' => [
                'UMR6016',
                'UMR6524',
                'UAR833',
            ],
            'OSU-Réunion' => [
                'UMR8105',
                'UMR9220',
                'UAR3365',
            ],
            'Ecce Terra' => [
                'UMR6143',
                'UMR7193',
                'UMR8067',
                'UMR7590',
                'UMR8538',
                'UMR8539',
                'UAR3455',
            ],
            'STAMAR' => [
                'UMR7093',
                'UMR7144',
                'UMR7621',
            ],
            'IUEM' => [
                'UMR6523',
                'UMR6538',
                'UMR6539',
            ],
            'IPGP' => [
                'UMR7154',
                'UAR3454',
            ],
            'OSERen' => [
                'UMR6118',
                'UMR6164',
                'UMR6251',
                'UMR6553',
                'UAR3343',
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

        // Importer le gros SQL entite.sql dans la base de données
        $sql = file_get_contents($this->kernel->getProjectDir() . '/sql/entite.sql');
        $this->em->getConnection()->executeStatement($sql);

        if ($choice === 'Aucun OSU') {
            $io->success('Tous les laboratoires ont été importés avec succès.');
            return Command::SUCCESS;
        }

        $laboratoiresAConserver = $laboParOsu[$choice];

        $deletedCount = $this->em->getConnection()->executeStatement(
            'DELETE FROM public.entite
     WHERE (REPLACE(code_unite, \' \', \'\') NOT IN (?))
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
