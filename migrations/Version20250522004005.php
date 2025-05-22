<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522004005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE room DROP disabled, DROP sort_key, DROP description, DROP capacity, DROP room_admin_email, DROP invalid_types, DROP custom_html
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE room ADD disabled TINYINT(1) NOT NULL, ADD sort_key VARCHAR(25) NOT NULL, ADD description VARCHAR(60) DEFAULT NULL, ADD capacity INT NOT NULL, ADD room_admin_email TEXT DEFAULT NULL, ADD invalid_types VARCHAR(255) DEFAULT NULL, ADD custom_html TEXT DEFAULT NULL
        SQL);
    }
}
