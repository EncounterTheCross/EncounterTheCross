<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241205031926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event CHANGE active active TINYINT(1) NOT NULL, CHANGE registration_open registration_open TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE event_prayer_team_server DROP FOREIGN KEY FK_1814D2C5A2CDFC9');
        $this->addSql('DROP INDEX IDX_1814D2C5A2CDFC9 ON event_prayer_team_server');
        $this->addSql('ALTER TABLE event_prayer_team_server CHANGE event_particapent_id event_participant_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE event_prayer_team_server ADD CONSTRAINT FK_1814D2C54258866A FOREIGN KEY (event_participant_id) REFERENCES event_participant (id)');
        $this->addSql('CREATE INDEX IDX_1814D2C54258866A ON event_prayer_team_server (event_participant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event CHANGE active active TINYINT(1) DEFAULT 0 NOT NULL, CHANGE registration_open registration_open TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE event_prayer_team_server DROP FOREIGN KEY FK_1814D2C54258866A');
        $this->addSql('DROP INDEX IDX_1814D2C54258866A ON event_prayer_team_server');
        $this->addSql('ALTER TABLE event_prayer_team_server CHANGE event_participant_id event_particapent_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE event_prayer_team_server ADD CONSTRAINT FK_1814D2C5A2CDFC9 FOREIGN KEY (event_particapent_id) REFERENCES event_participant (id)');
        $this->addSql('CREATE INDEX IDX_1814D2C5A2CDFC9 ON event_prayer_team_server (event_particapent_id)');
    }
}
