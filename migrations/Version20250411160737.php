<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250411160737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_activity (request_time DATETIME NOT NULL, response_time DATETIME DEFAULT NULL, duration INT DEFAULT NULL, ip_address VARCHAR(15) DEFAULT NULL, request_uri VARCHAR(255) DEFAULT NULL, request_method VARCHAR(10) NOT NULL, route VARCHAR(255) DEFAULT NULL, status_code INT DEFAULT NULL, content_type VARCHAR(255) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, browser VARCHAR(50) DEFAULT NULL, browser_version VARCHAR(20) DEFAULT NULL, operating_system VARCHAR(50) DEFAULT NULL, device_type VARCHAR(20) DEFAULT NULL, session_id VARCHAR(255) DEFAULT NULL, user_id INT DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, referrer VARCHAR(255) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, id INT UNSIGNED AUTO_INCREMENT NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE user_activity
        SQL);
    }
}
