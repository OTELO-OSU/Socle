<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261030085000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute l’identifiant ROR aux entités';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entite ADD ror_id VARCHAR(9) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1A291827B47C99C0 ON entite (ror_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_1A291827B47C99C0');
        $this->addSql('ALTER TABLE entite DROP ror_id');
    }
}
