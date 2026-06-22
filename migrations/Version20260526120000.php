<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les colonnes "views" (compteur de vues) et "rating" (note affichée)
 * à la table content.
 */
final class Version20260526120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add views and rating columns to content table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE content ADD views INT DEFAULT 0 NOT NULL, ADD rating DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE content DROP views, DROP rating');
    }
}
