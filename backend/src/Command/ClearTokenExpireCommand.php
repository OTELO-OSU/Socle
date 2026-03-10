<?php

namespace App\Command;

use App\Entity\RefreshToken;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clear-token-expire',
    description: 'Supprime tous les refresh token expiré de la base de données',
)]
class ClearTokenExpireCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new DateTime();

        $deletedCount = $this->em->createQueryBuilder()
            ->delete(RefreshToken::class, 'rt')
            ->where('rt.expiration < :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->execute();

        $io = new SymfonyStyle($input, $output);
        $io->success(sprintf('%d token(s) expiré(s) ont été supprimé(s).', $deletedCount));

        return Command::SUCCESS;
    }
}
