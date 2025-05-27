<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527020311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE event_booked_rooms ADD event_id INT UNSIGNED NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_booked_rooms ADD CONSTRAINT FK_CFF08DF171F7E88B FOREIGN KEY (event_id) REFERENCES event (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CFF08DF171F7E88B ON event_booked_rooms (event_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE event_booked_rooms DROP FOREIGN KEY FK_CFF08DF171F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CFF08DF171F7E88B ON event_booked_rooms
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_booked_rooms DROP event_id
        SQL);
    }
}
