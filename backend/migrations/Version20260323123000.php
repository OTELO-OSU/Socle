<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260323123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert token expiration columns from DATE to DATETIME for accurate expiry checks';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE refresh_token ALTER COLUMN expiration TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE utilisateur_inscription_oauth ALTER COLUMN expiration TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE utilisateur_verification ALTER COLUMN expiration TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE refresh_token ALTER COLUMN expiration TYPE DATE');
        $this->addSql('ALTER TABLE utilisateur_inscription_oauth ALTER COLUMN expiration TYPE DATE');
        $this->addSql('ALTER TABLE utilisateur_verification ALTER COLUMN expiration TYPE DATE');
    }
}
