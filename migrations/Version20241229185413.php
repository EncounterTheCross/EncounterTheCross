<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241229185413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD check_in_token VARCHAR(255)');

        // make sure all have check in token values
        $this->addSql('UPDATE event  SET check_in_token = LOWER(HEX(RANDOM_BYTES(32))) WHERE check_in_token IS NULL;');
        $this->addSql('ALTER TABLE event CHANGE check_in_token check_in_token VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP check_in_token');
    }
}
