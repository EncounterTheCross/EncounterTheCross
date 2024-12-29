<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240812224142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_prayer_team_server (id INT UNSIGNED AUTO_INCREMENT NOT NULL, event_id INT UNSIGNED NOT NULL, prayer_team_id INT UNSIGNED NOT NULL, event_particapent_id INT UNSIGNED NOT NULL, row_pointer BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1814D2C55A89FD83 (row_pointer), INDEX IDX_1814D2C571F7E88B (event_id), INDEX IDX_1814D2C56798D81 (prayer_team_id), INDEX IDX_1814D2C5A2CDFC9 (event_particapent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prayer_team (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, row_pointer BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_5F9D47775A89FD83 (row_pointer), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_prayer_team_server ADD CONSTRAINT FK_1814D2C571F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_prayer_team_server ADD CONSTRAINT FK_1814D2C56798D81 FOREIGN KEY (prayer_team_id) REFERENCES prayer_team (id)');
        $this->addSql('ALTER TABLE event_prayer_team_server ADD CONSTRAINT FK_1814D2C5A2CDFC9 FOREIGN KEY (event_particapent_id) REFERENCES event_participant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_prayer_team_server DROP FOREIGN KEY FK_1814D2C571F7E88B');
        $this->addSql('ALTER TABLE event_prayer_team_server DROP FOREIGN KEY FK_1814D2C56798D81');
        $this->addSql('ALTER TABLE event_prayer_team_server DROP FOREIGN KEY FK_1814D2C5A2CDFC9');
        $this->addSql('DROP TABLE event_prayer_team_server');
        $this->addSql('DROP TABLE prayer_team');
    }
}
