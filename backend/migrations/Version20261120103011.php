<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120103011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE plateforme_pseudo_id_mapping_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE plateforme_pseudo_id_mapping (id INT NOT NULL, plateforme_id INT NOT NULL, pseudo_id_mapped VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PLATEFORME_ID ON plateforme_pseudo_id_mapping (plateforme_id)');
        $this->addSql('ALTER TABLE plateforme_pseudo_id_mapping ALTER id SET DEFAULT nextval(\'plateforme_pseudo_id_mapping_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE plateforme_pseudo_id_mapping_id_seq CASCADE');
        $this->addSql('DROP TABLE plateforme_pseudo_id_mapping');
    }
}
