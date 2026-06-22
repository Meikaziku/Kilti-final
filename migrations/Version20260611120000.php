<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Données de démonstration : 25 avis sur le reportage « Cosmos : aux frontières du visible ».
 *
 * Comme la table rating a une contrainte d'unicité (user_id, content_id),
 * un utilisateur ne peut laisser qu'un seul avis par reportage. On crée donc
 * 25 utilisateurs de démonstration, puis un avis chacun.
 *
 * Prérequis :
 *   - la colonne rating.comment doit exister ;
 *   - le reportage « Cosmos : aux frontières du visible » doit exister (migration des reportages de démo).
 *
 * Comptes créés : mot de passe « demo1234 » pour tous.
 */
final class Version20260611120000 extends AbstractMigration
{
    private const DEMO_PASSWORD = '$2b$12$rPu3xnUIPLyU9sTtXiACpeeEnYl/JARmXFbJQw.dx/CBFXeJNbfvS';

    public function getDescription(): string
    {
        return 'Ajoute 25 avis de démonstration sur « Cosmos : aux frontières du visible ».';
    }

    public function up(Schema $schema): void
    {
        // 1) La colonne comment doit exister
        $hasComment = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rating' AND COLUMN_NAME = 'comment'"
        );
        $this->abortIf(
            $hasComment === 0,
            'La colonne rating.comment est absente. Lance d\'abord la migration qui ajoute le champ comment.'
        );

        // 2) Le reportage cible doit exister
        $contentId = $this->connection->fetchOne("SELECT id FROM content WHERE title = 'Cosmos : aux frontières du visible' LIMIT 1");
        $this->abortIf(
            $contentId === false,
            'Le reportage « Cosmos : aux frontières du visible » est introuvable. Lance d\'abord la migration des reportages de démonstration.'
        );
        $contentId = (int) $contentId;

        // 3) Utilisateurs de démonstration [email, pseudo]
        $users = [
            ['reviewer1@demo.kilti', 'lucas_m'],
            ['reviewer2@demo.kilti', 'emma.r'],
            ['reviewer3@demo.kilti', 'hugo_t'],
            ['reviewer4@demo.kilti', 'lea_p'],
            ['reviewer5@demo.kilti', 'nathan'],
            ['reviewer6@demo.kilti', 'chloe_b'],
            ['reviewer7@demo.kilti', 'enzo'],
            ['reviewer8@demo.kilti', 'manon_d'],
            ['reviewer9@demo.kilti', 'louis_v'],
            ['reviewer10@demo.kilti', 'camille'],
            ['reviewer11@demo.kilti', 'jules_k'],
            ['reviewer12@demo.kilti', 'sofia'],
            ['reviewer13@demo.kilti', 'gabriel'],
            ['reviewer14@demo.kilti', 'ines_w'],
            ['reviewer15@demo.kilti', 'raph'],
            ['reviewer16@demo.kilti', 'jade_l'],
            ['reviewer17@demo.kilti', 'tom_b'],
            ['reviewer18@demo.kilti', 'alice'],
            ['reviewer19@demo.kilti', 'noah_g'],
            ['reviewer20@demo.kilti', 'zoe'],
            ['reviewer21@demo.kilti', 'adam_s'],
            ['reviewer22@demo.kilti', 'lina'],
            ['reviewer23@demo.kilti', 'theo_r'],
            ['reviewer24@demo.kilti', 'sarah_m'],
            ['reviewer25@demo.kilti', 'maxence'],
        ];

        $pwd = $this->connection->quote(self::DEMO_PASSWORD);
        $rows = [];
        foreach ($users as [$email, $username]) {
            $rows[] = sprintf(
                "(%s, '[\"ROLE_USER\"]', %s, %s, 1)",
                $this->connection->quote($email),
                $pwd,
                $this->connection->quote($username)
            );
        }
        $this->addSql("INSERT INTO users (email, roles, password, username, is_verified) VALUES " . implode(", ", $rows));

        // 4) Avis [email auteur, note, commentaire, il y a X heures]
        $reviews = [
            ['reviewer1@demo.kilti', 5, 'Documentaire passionnant, j\'ai appris énormément de choses sur l\'observation de l\'univers.', 2],
            ['reviewer2@demo.kilti', 4, 'Les images sont magnifiques et les explications restent accessibles. Bravo.', 27],
            ['reviewer3@demo.kilti', 5, 'Un peu technique par moments, mais l\'ensemble est vraiment captivant.', 35],
            ['reviewer4@demo.kilti', 5, 'J\'ai adoré la partie sur les télescopes modernes, c\'est impressionnant.', 40],
            ['reviewer5@demo.kilti', 4, 'Très bon rythme, on ne s\'ennuie pas une seconde.', 68],
            ['reviewer6@demo.kilti', 5, 'Parfait pour s\'initier à l\'astronomie sans être largué.', 81],
            ['reviewer7@demo.kilti', 4, 'La musique colle parfaitement aux images, immersion totale.', 93],
            ['reviewer8@demo.kilti', 3, 'Quelques longueurs au milieu mais la fin rattrape tout.', 105],
            ['reviewer9@demo.kilti', 5, 'Le genre de reportage qui donne envie de regarder le ciel autrement.', 114],
            ['reviewer10@demo.kilti', 4, 'Bien documenté, j\'aurais juste aimé un peu plus d\'interviews de chercheurs.', 142],
            ['reviewer11@demo.kilti', 5, 'Superbe vulgarisation, je l\'ai montré à mes enfants qui ont adoré.', 150],
            ['reviewer12@demo.kilti', 5, 'Un must pour les curieux. Je recommande à fond.', 176],
            ['reviewer13@demo.kilti', 4, 'Le sujet est dense mais bien expliqué, chapeau au réalisateur.', 204],
            ['reviewer14@demo.kilti', 5, 'Visuellement c\'est une claque, le fond est tout aussi solide.', 226],
            ['reviewer15@demo.kilti', 3, 'Intéressant mais j\'attendais plus sur les dernières découvertes.', 233],
            ['reviewer16@demo.kilti', 4, 'Pédagogique sans être ennuyeux, exactement ce que je cherchais.', 256],
            ['reviewer17@demo.kilti', 5, 'Une belle réussite, à voir absolument sur grand écran.', 274],
            ['reviewer18@demo.kilti', 5, 'Ça m\'a réconcilié avec les documentaires scientifiques.', 280],
            ['reviewer19@demo.kilti', 3, 'Bon dans l\'ensemble, le début est un peu lent à démarrer.', 285],
            ['reviewer20@demo.kilti', 5, 'Fascinant du début à la fin, je le reverrai sans hésiter.', 292],
            ['reviewer21@demo.kilti', 4, 'Les schémas aident vraiment à comprendre, gros plus.', 303],
            ['reviewer22@demo.kilti', 5, 'Un voyage incroyable, on en ressort avec plein de questions.', 315],
            ['reviewer23@demo.kilti', 4, 'Très complet, parfait pour un public débutant comme avancé.', 336],
            ['reviewer24@demo.kilti', 4, 'Le narrateur est excellent, sa voix porte tout le doc.', 360],
            ['reviewer25@demo.kilti', 5, 'Coup de cœur, je ne m\'attendais pas à être autant captivé.', 365],
        ];

        foreach ($reviews as [$email, $value, $comment, $hoursAgo]) {
            $this->addSql(sprintf(
                "INSERT INTO rating (value, comment, created_at, updated_at, user_id, content_id)
                 SELECT %d, %s, NOW() - INTERVAL %d HOUR, NOW() - INTERVAL %d HOUR,
                    (SELECT id FROM users WHERE email = %s), %d",
                $value,
                $this->connection->quote($comment),
                $hoursAgo,
                $hoursAgo,
                $this->connection->quote($email),
                $contentId
            ));
        }

        // 5) On recalcule la moyenne et le nombre de notes du reportage
        $this->addSql(sprintf(
            "UPDATE content SET
                rating = (SELECT AVG(value) FROM rating WHERE content_id = %d),
                ratings_count = (SELECT COUNT(*) FROM rating WHERE content_id = %d)
             WHERE id = %d",
            $contentId, $contentId, $contentId
        ));
    }

    public function down(Schema $schema): void
    {
        $emails = [];
        for ($i = 1; $i <= 25; $i++) {
            $emails[] = "'reviewer{$i}@demo.kilti'";
        }
        $list = implode(',', $emails);

        $this->addSql("DELETE FROM rating WHERE user_id IN (SELECT id FROM users WHERE email IN ($list))");
        $this->addSql("DELETE FROM users WHERE email IN ($list)");

        $contentId = $this->connection->fetchOne("SELECT id FROM content WHERE title = 'Cosmos : aux frontières du visible' LIMIT 1");
        if ($contentId !== false) {
            $contentId = (int) $contentId;
            $this->addSql(sprintf(
                "UPDATE content SET
                    rating = (SELECT AVG(value) FROM rating WHERE content_id = %d),
                    ratings_count = (SELECT COUNT(*) FROM rating WHERE content_id = %d)
                 WHERE id = %d",
                $contentId, $contentId, $contentId
            ));
        }
    }
}
