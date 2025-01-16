<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250115024655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_prayer_team_server ADD intersession_assignment_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE event_prayer_team_server ADD CONSTRAINT FK_1814D2C587F3A840 FOREIGN KEY (intersession_assignment_id) REFERENCES prayer_team (id)');
        $this->addSql('CREATE INDEX IDX_1814D2C587F3A840 ON event_prayer_team_server (intersession_assignment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_prayer_team_server DROP FOREIGN KEY FK_1814D2C587F3A840');
        $this->addSql('DROP INDEX IDX_1814D2C587F3A840 ON event_prayer_team_server');
        $this->addSql('ALTER TABLE event_prayer_team_server DROP intersession_assignment_id');
    }
}
