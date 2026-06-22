<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223133012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, url VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, image VARCHAR(255) NOT NULL, duration INT NOT NULL, created_at DATETIME NOT NULL, is_validated TINYINT NOT NULL, validated_at DATETIME DEFAULT NULL, rejected_reason LONGTEXT DEFAULT NULL, validated_by_id INT DEFAULT NULL, INDEX IDX_FEC530A9C69DE5E5 (validated_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9C69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9C69DE5E5');
        $this->addSql('DROP TABLE content');
    }
}
