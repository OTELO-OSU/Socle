<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261123144500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add instanceHost field to PlateformePseudoIdMapping to distinguish remote platforms';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plateforme_pseudo_id_mapping ADD instance_host VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PLATEFORME_ID_HOST ON plateforme_pseudo_id_mapping(plateforme_id, instance_host)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_PLATEFORME_ID_HOST');
        $this->addSql('ALTER TABLE plateforme_pseudo_id_mapping DROP instance_host');
    }
}
