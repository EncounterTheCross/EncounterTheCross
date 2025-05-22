<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522002624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE area DROP disabled, DROP sort_key, DROP timezone, DROP area_admin_email, DROP resolution, DROP default_duration, DROP default_duration_all_day, DROP morningstarts, DROP morningstarts_minutes, DROP eveningends, DROP eveningends_minutes, DROP private_enabled, DROP private_default, DROP private_mandatory, DROP private_override, DROP min_create_ahead_enabled, DROP min_create_ahead_secs, DROP max_create_ahead_enabled, DROP max_create_ahead_secs, DROP min_delete_ahead_enabled, DROP min_delete_ahead_secs, DROP max_delete_ahead_enabled, DROP max_delete_ahead_secs, DROP max_per_day_enabled, DROP max_per_day, DROP max_per_week_enabled, DROP max_per_week, DROP max_per_month_enabled, DROP max_per_month, DROP max_per_year_enabled, DROP max_per_year, DROP max_per_future_enabled, DROP max_per_future, DROP max_secs_per_day_enabled, DROP max_secs_per_day, DROP max_secs_per_week_enabled, DROP max_secs_per_week, DROP max_secs_per_month_enabled, DROP max_secs_per_month, DROP max_secs_per_year_enabled, DROP max_secs_per_year, DROP max_secs_per_future_enabled, DROP max_secs_per_future, DROP max_duration_enabled, DROP max_duration_secs, DROP max_duration_periods, DROP custom_html, DROP approval_enabled, DROP reminders_enabled, DROP enable_periods, DROP periods, DROP confirmation_enabled, DROP confirmed_default, DROP times_along_top, DROP default_type
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE area ADD disabled TINYINT(1) NOT NULL, ADD sort_key VARCHAR(30) NOT NULL, ADD timezone VARCHAR(50) DEFAULT NULL, ADD area_admin_email TEXT DEFAULT NULL, ADD resolution INT DEFAULT NULL, ADD default_duration INT DEFAULT NULL, ADD default_duration_all_day TINYINT(1) NOT NULL, ADD morningstarts INT DEFAULT NULL, ADD morningstarts_minutes INT DEFAULT NULL, ADD eveningends INT DEFAULT NULL, ADD eveningends_minutes INT DEFAULT NULL, ADD private_enabled TINYINT(1) DEFAULT NULL, ADD private_default TINYINT(1) DEFAULT NULL, ADD private_mandatory TINYINT(1) DEFAULT NULL, ADD private_override VARCHAR(32) DEFAULT NULL, ADD min_create_ahead_enabled TINYINT(1) DEFAULT NULL, ADD min_create_ahead_secs INT DEFAULT NULL, ADD max_create_ahead_enabled TINYINT(1) DEFAULT NULL, ADD max_create_ahead_secs INT DEFAULT NULL, ADD min_delete_ahead_enabled TINYINT(1) DEFAULT NULL, ADD min_delete_ahead_secs INT DEFAULT NULL, ADD max_delete_ahead_enabled TINYINT(1) DEFAULT NULL, ADD max_delete_ahead_secs INT DEFAULT NULL, ADD max_per_day_enabled TINYINT(1) NOT NULL, ADD max_per_day INT NOT NULL, ADD max_per_week_enabled TINYINT(1) NOT NULL, ADD max_per_week INT NOT NULL, ADD max_per_month_enabled TINYINT(1) NOT NULL, ADD max_per_month INT NOT NULL, ADD max_per_year_enabled TINYINT(1) NOT NULL, ADD max_per_year INT NOT NULL, ADD max_per_future_enabled TINYINT(1) NOT NULL, ADD max_per_future INT NOT NULL, ADD max_secs_per_day_enabled TINYINT(1) NOT NULL, ADD max_secs_per_day INT NOT NULL, ADD max_secs_per_week_enabled TINYINT(1) NOT NULL, ADD max_secs_per_week INT NOT NULL, ADD max_secs_per_month_enabled TINYINT(1) NOT NULL, ADD max_secs_per_month INT NOT NULL, ADD max_secs_per_year_enabled TINYINT(1) NOT NULL, ADD max_secs_per_year INT NOT NULL, ADD max_secs_per_future_enabled TINYINT(1) NOT NULL, ADD max_secs_per_future INT NOT NULL, ADD max_duration_enabled TINYINT(1) NOT NULL, ADD max_duration_secs INT NOT NULL, ADD max_duration_periods INT NOT NULL, ADD custom_html TEXT DEFAULT NULL, ADD approval_enabled TINYINT(1) DEFAULT NULL, ADD reminders_enabled TINYINT(1) DEFAULT NULL, ADD enable_periods TINYINT(1) DEFAULT NULL, ADD periods TEXT DEFAULT NULL, ADD confirmation_enabled TINYINT(1) DEFAULT NULL, ADD confirmed_default TINYINT(1) DEFAULT NULL, ADD times_along_top TINYINT(1) NOT NULL, ADD default_type VARCHAR(1) NOT NULL
        SQL);
    }
}
