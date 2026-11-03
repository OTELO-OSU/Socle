<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261103142500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow application settings to be stored as text.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE parametre ALTER valeur TYPE TEXT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE parametre ALTER valeur TYPE VARCHAR(255)');
    }
}
