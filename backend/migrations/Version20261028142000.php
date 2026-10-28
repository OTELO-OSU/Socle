<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028142000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalise les rôles stockés sous forme d\'objet JSON en tableau JSON';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE utilisateur
            SET roles = (SELECT COALESCE(json_agg(DISTINCT value), '[]'::json) FROM json_each_text(roles::json))
            WHERE json_typeof(roles::json) = 'object'
            SQL);
    }

    public function down(Schema $schema): void
    {
        // Pas de retour arrière : la normalisation est sans perte
    }
}
