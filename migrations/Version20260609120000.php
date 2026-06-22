<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Données de démonstration : catégories de reportages + quelques reportages déjà validés.
 *
 * Remarque : un reportage doit avoir un auteur (user_id non nul). Cette migration
 * récupère donc le premier utilisateur existant. Il faut donc avoir créé au moins
 * un compte avant de la lancer (par ex. : php bin/console app:create-moderator ...).
 */
final class Version20260609120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insère des catégories et des reportages de démonstration.';
    }

    public function up(Schema $schema): void
    {
        // On a besoin d'un utilisateur existant pour être l'auteur des reportages.
        $userId = $this->connection->fetchOne('SELECT id FROM users ORDER BY id ASC LIMIT 1');
        $this->abortIf(
            $userId === false,
            'Aucun utilisateur en base. Crée d’abord un compte (php bin/console app:create-moderator email mot_de_passe --admin) avant de lancer cette migration.'
        );
        $userId = (int) $userId;

        // --- Catégories ---
        $this->addSql("INSERT INTO content_category (name) VALUES
            ('Nature'),
            ('Histoire'),
            ('Sciences'),
            ('Société'),
            ('Voyage'),
            ('Culture')");

        // --- Reportages (déjà validés pour apparaître dans l'Explorer) ---
        // category_id est retrouvé par le nom de la catégorie ; user_id est l'auteur récupéré ci-dessus.
        $reports = [
            ['Les gardiens de la forêt', 'Une plongée au cœur des dernières forêts primaires et à la rencontre de celles et ceux qui se battent pour les protéger.', 'report-1.jpg', 52, 'Nature', 1240],
            ['Mémoires d\'une ville', 'À travers les archives et les témoignages de ses habitants, le portrait intime d\'une ville et de ceux qui l\'ont façonnée.', 'report-2.jpg', 48, 'Histoire', 870],
            ['L\'océan en sursis', 'Des récifs coralliens aux grands fonds, un état des lieux de la santé des océans et des solutions pour les préserver.', 'report-3.jpg', 67, 'Nature', 2030],
            ['Voix de la rue', 'Pendant un an, la caméra suit le quotidien de personnes sans domicile et interroge notre regard sur la grande précarité.', 'report-4.jpg', 41, 'Société', 1520],
            ['Cosmos : aux frontières du visible', 'Des premiers télescopes aux observatoires modernes, comment l\'humanité repousse sans cesse les limites de ce qu\'elle peut observer.', 'report-5.jpg', 58, 'Sciences', 3110],
            ['Sur la route de la soie', 'Un voyage de plusieurs milliers de kilomètres sur les traces des anciennes routes commerciales reliant l\'Orient à l\'Occident.', 'report-6.jpg', 73, 'Voyage', 1680],
            ['Artisans d\'un savoir-faire', 'Rencontre avec des artisans qui perpétuent des gestes transmis depuis des générations, entre tradition et modernité.', 'report-7.jpg', 39, 'Culture', 640],
            ['Génération climat', 'Partout dans le monde, des jeunes se mobilisent pour le climat. Portrait d\'une génération qui refuse de subir l\'avenir.', 'report-8.jpg', 55, 'Société', 2450],
        ];

        foreach ($reports as [$title, $description, $image, $duration, $categoryName, $views]) {
            $titleSql = $this->connection->quote($title);
            $descSql = $this->connection->quote($description);
            $imageSql = $this->connection->quote($image);
            $catSql = $this->connection->quote($categoryName);
            $urlSql = $this->connection->quote('https://example.com/reportages/' . $image);

            $this->addSql(
                "INSERT INTO content
                    (title, description, url, image, duration, views, rating, ratings_count, status, is_validated, created_at, validated_at, validated_by_id, category_id, user_id)
                 SELECT
                    $titleSql, $descSql, $urlSql, $imageSql, $duration, $views, NULL, 0, 'approved', 1, NOW(), NOW(), $userId,
                    cc.id, $userId
                 FROM content_category cc
                 WHERE cc.name = $catSql
                 LIMIT 1"
            );
        }
    }

    public function down(Schema $schema): void
    {
        // On supprime les reportages de démo (par leur nom de fichier image),
        // puis les catégories de démo.
        $this->addSql("DELETE FROM content WHERE image IN (
            'report-1.jpg','report-2.jpg','report-3.jpg','report-4.jpg',
            'report-5.jpg','report-6.jpg','report-7.jpg','report-8.jpg'
        )");

        $this->addSql("DELETE FROM content_category WHERE name IN (
            'Nature','Histoire','Sciences','Société','Voyage','Culture'
        )");
    }
}
