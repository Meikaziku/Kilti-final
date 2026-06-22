<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223140056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE content ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9C69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A912469DE2 FOREIGN KEY (category_id) REFERENCES content_category (id)');
        $this->addSql('CREATE INDEX IDX_FEC530A912469DE2 ON content (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE content_category');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9A76ED395');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9C69DE5E5');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A912469DE2');
        $this->addSql('DROP INDEX IDX_FEC530A912469DE2 ON content');
        $this->addSql('ALTER TABLE content DROP category_id');
    }
}
