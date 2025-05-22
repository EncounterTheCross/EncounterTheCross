<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521231649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE area (disabled TINYINT(1) NOT NULL, area_name VARCHAR(30) DEFAULT NULL, sort_key VARCHAR(30) NOT NULL, timezone VARCHAR(50) DEFAULT NULL, area_admin_email TEXT DEFAULT NULL, resolution INT DEFAULT NULL, default_duration INT DEFAULT NULL, default_duration_all_day TINYINT(1) NOT NULL, morningstarts INT DEFAULT NULL, morningstarts_minutes INT DEFAULT NULL, eveningends INT DEFAULT NULL, eveningends_minutes INT DEFAULT NULL, private_enabled TINYINT(1) DEFAULT NULL, private_default TINYINT(1) DEFAULT NULL, private_mandatory TINYINT(1) DEFAULT NULL, private_override VARCHAR(32) DEFAULT NULL, min_create_ahead_enabled TINYINT(1) DEFAULT NULL, min_create_ahead_secs INT DEFAULT NULL, max_create_ahead_enabled TINYINT(1) DEFAULT NULL, max_create_ahead_secs INT DEFAULT NULL, min_delete_ahead_enabled TINYINT(1) DEFAULT NULL, min_delete_ahead_secs INT DEFAULT NULL, max_delete_ahead_enabled TINYINT(1) DEFAULT NULL, max_delete_ahead_secs INT DEFAULT NULL, max_per_day_enabled TINYINT(1) NOT NULL, max_per_day INT NOT NULL, max_per_week_enabled TINYINT(1) NOT NULL, max_per_week INT NOT NULL, max_per_month_enabled TINYINT(1) NOT NULL, max_per_month INT NOT NULL, max_per_year_enabled TINYINT(1) NOT NULL, max_per_year INT NOT NULL, max_per_future_enabled TINYINT(1) NOT NULL, max_per_future INT NOT NULL, max_secs_per_day_enabled TINYINT(1) NOT NULL, max_secs_per_day INT NOT NULL, max_secs_per_week_enabled TINYINT(1) NOT NULL, max_secs_per_week INT NOT NULL, max_secs_per_month_enabled TINYINT(1) NOT NULL, max_secs_per_month INT NOT NULL, max_secs_per_year_enabled TINYINT(1) NOT NULL, max_secs_per_year INT NOT NULL, max_secs_per_future_enabled TINYINT(1) NOT NULL, max_secs_per_future INT NOT NULL, max_duration_enabled TINYINT(1) NOT NULL, max_duration_secs INT NOT NULL, max_duration_periods INT NOT NULL, custom_html TEXT DEFAULT NULL, approval_enabled TINYINT(1) DEFAULT NULL, reminders_enabled TINYINT(1) DEFAULT NULL, enable_periods TINYINT(1) DEFAULT NULL, periods TEXT DEFAULT NULL, confirmation_enabled TINYINT(1) DEFAULT NULL, confirmed_default TINYINT(1) DEFAULT NULL, times_along_top TINYINT(1) NOT NULL, default_type VARCHAR(1) NOT NULL, id INT UNSIGNED AUTO_INCREMENT NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, venue_id INT UNSIGNED NOT NULL, INDEX IDX_D7943D6840A73EBA (venue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event_room_booking (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, event_id INT UNSIGNED NOT NULL, room_config_id INT UNSIGNED NOT NULL, launch_point_id INT UNSIGNED NOT NULL, INDEX IDX_E13D5BCD71F7E88B (event_id), INDEX IDX_E13D5BCDB872E6EA (room_config_id), INDEX IDX_E13D5BCDA495DAEF (launch_point_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event_room_booking_room_configuration (event_room_booking_id INT NOT NULL, room_configuration_id INT UNSIGNED NOT NULL, INDEX IDX_5C750D9FE11084C (event_room_booking_id), INDEX IDX_5C750D9F1389C97 (room_configuration_id), PRIMARY KEY(event_room_booking_id, room_configuration_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE room (disabled TINYINT(1) NOT NULL, room_name VARCHAR(25) NOT NULL, sort_key VARCHAR(25) NOT NULL, description VARCHAR(60) DEFAULT NULL, capacity INT NOT NULL, room_admin_email TEXT DEFAULT NULL, invalid_types VARCHAR(255) DEFAULT NULL, custom_html TEXT DEFAULT NULL, id INT UNSIGNED AUTO_INCREMENT NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, area_id INT UNSIGNED NOT NULL, INDEX IDX_729F519BBD0F409C (area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE room_configuration (name VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, capacity INT NOT NULL, is_default TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, valid_from_month INT DEFAULT NULL, valid_until_month INT DEFAULT NULL, id INT UNSIGNED AUTO_INCREMENT NOT NULL, room_id INT UNSIGNED NOT NULL, INDEX IDX_CC52153154177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE area ADD CONSTRAINT FK_D7943D6840A73EBA FOREIGN KEY (venue_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking ADD CONSTRAINT FK_E13D5BCD71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking ADD CONSTRAINT FK_E13D5BCDB872E6EA FOREIGN KEY (room_config_id) REFERENCES room_configuration (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking ADD CONSTRAINT FK_E13D5BCDA495DAEF FOREIGN KEY (launch_point_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking_room_configuration ADD CONSTRAINT FK_5C750D9FE11084C FOREIGN KEY (event_room_booking_id) REFERENCES event_room_booking (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking_room_configuration ADD CONSTRAINT FK_5C750D9F1389C97 FOREIGN KEY (room_configuration_id) REFERENCES room_configuration (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room ADD CONSTRAINT FK_729F519BBD0F409C FOREIGN KEY (area_id) REFERENCES area (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room_configuration ADD CONSTRAINT FK_CC52153154177093 FOREIGN KEY (room_id) REFERENCES room (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request CHANGE requested_at requested_at DATETIME NOT NULL, CHANGE expires_at expires_at DATETIME NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE area DROP FOREIGN KEY FK_D7943D6840A73EBA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking DROP FOREIGN KEY FK_E13D5BCD71F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking DROP FOREIGN KEY FK_E13D5BCDB872E6EA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking DROP FOREIGN KEY FK_E13D5BCDA495DAEF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking_room_configuration DROP FOREIGN KEY FK_5C750D9FE11084C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_room_booking_room_configuration DROP FOREIGN KEY FK_5C750D9F1389C97
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room DROP FOREIGN KEY FK_729F519BBD0F409C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room_configuration DROP FOREIGN KEY FK_CC52153154177093
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE area
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event_room_booking
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event_room_booking_room_configuration
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE room
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE room_configuration
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request CHANGE requested_at requested_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE expires_at expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }
}
