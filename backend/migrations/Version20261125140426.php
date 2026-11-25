<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261125140426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emploi_type_bap DROP CONSTRAINT fk_b6104d1dea947d2d');
        $this->addSql('ALTER TABLE emploi_type_bap DROP CONSTRAINT fk_b6104d1da293edb1');
        $this->addSql('DROP TABLE emploi_type_bap');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE emploi_type_bap (emploi_type_id INT NOT NULL, bap_id INT NOT NULL, PRIMARY KEY (emploi_type_id, bap_id))');
        $this->addSql('CREATE INDEX idx_b6104d1dea947d2d ON emploi_type_bap (emploi_type_id)');
        $this->addSql('CREATE INDEX idx_b6104d1da293edb1 ON emploi_type_bap (bap_id)');
        $this->addSql('ALTER TABLE emploi_type_bap ADD CONSTRAINT fk_b6104d1dea947d2d FOREIGN KEY (emploi_type_id) REFERENCES emploi_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE emploi_type_bap ADD CONSTRAINT fk_b6104d1da293edb1 FOREIGN KEY (bap_id) REFERENCES bap (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
