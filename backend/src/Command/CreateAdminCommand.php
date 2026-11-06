<?php

namespace App\Command;

use App\Entity\RefreshToken;
use App\Entity\Utilisateur;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur',
)]
class CreateAdminCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Demander un mot de passe pour l'utilisateur administrateur
        $io = new SymfonyStyle($input, $output);
        $email = $io->ask('Entrez l\'email de l\'utilisateur administrateur');

        if (!$email) {
            $io->error('L\'email est requis.');
            return Command::FAILURE;
        }

        $password = $io->askHidden('Entrez le mot de passe pour l\'utilisateur administrateur');
        if (!$password) {
            $io->error('Le mot de passe est requis.');
            return Command::FAILURE;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 13]);

        $user = new Utilisateur();
        $user->setEmailValide(true);
        $user->setAdmis(true);
        $user->setNom('Temporaire');
        $user->setPrenom('Administrateur');
        $user->setEmail($email);
        $user->setMdp($hashedPassword);
        $user->setRoles(['ROLE_ADMIN']);

        $this->em->persist($user);
        $this->em->flush();

        $io = new SymfonyStyle($input, $output);
        $io->success('L\'utilisateur administrateur a été créé avec succès.');

        return Command::SUCCESS;
    }
}
