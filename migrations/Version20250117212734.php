<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250117212734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates LaunchPointContacts from existing Leader launch_point_id data';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE launch_point_contacts (id INT AUTO_INCREMENT NOT NULL, launch_point_id INT UNSIGNED NOT NULL, leader_id INT UNSIGNED NOT NULL, helper TINYINT(1) NOT NULL, INDEX IDX_2745925AA495DAEF (launch_point_id), UNIQUE INDEX UNIQ_2745925A73154ED4 (leader_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE launch_point_contacts ADD CONSTRAINT FK_2745925AA495DAEF FOREIGN KEY (launch_point_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE launch_point_contacts ADD CONSTRAINT FK_2745925A73154ED4 FOREIGN KEY (leader_id) REFERENCES leader (id)');
        $this->addSql('ALTER TABLE prayer_team CHANGE requires_intersession requires_intersession TINYINT(1) NOT NULL');

        // First, get all leaders that have a launch_point_id
        $leaders = $this->connection->fetchAllAssociative(
            'SELECT id, launch_point_id FROM leader WHERE launch_point_id IS NOT NULL'
        );

        // Insert a LaunchPointContact for each leader
        foreach ($leaders as $leader) {
            $this->addSql(
                'INSERT INTO launch_point_contacts (launch_point_id, leader_id, helper) 
                 VALUES (:launch_point_id, :leader_id, :helper)',
                [
                    'launch_point_id' => $leader['launch_point_id'],
                    'leader_id' => $leader['id'],
                    'helper' => 0, // Assuming all existing leaders are main leaders, not helpers
                ]
            );
        }

        // Optionally, after confirming the data is migrated correctly,
        // you might want to remove the launch_point_id column from the leader table
        $this->addSql('ALTER TABLE leader DROP FOREIGN KEY FK_F5E3EAD7A495DAEF');
        $this->addSql('ALTER TABLE leader DROP COLUMN launch_point_id');
    }

    public function down(Schema $schema): void
    {
        // If we need to rollback, we should restore the data back to the leader table
        $this->addSql('ALTER TABLE leader ADD launch_point_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE leader ADD CONSTRAINT FK_F5E3EAD7A495DAEF FOREIGN KEY (launch_point_id) REFERENCES location (id)');

        $contacts = $this->connection->fetchAllAssociative(
            'SELECT launch_point_id, leader_id FROM launch_point_contacts'
        );

        foreach ($contacts as $contact) {
            $this->addSql(
                'UPDATE leader SET launch_point_id = :launch_point_id 
                 WHERE id = :leader_id',
                [
                    'launch_point_id' => $contact['launch_point_id'],
                    'leader_id' => $contact['leader_id'],
                ]
            );
        }

        // Then remove all launch point contacts
        $this->addSql('DELETE FROM launch_point_contacts');

        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE launch_point_contacts DROP FOREIGN KEY FK_2745925AA495DAEF');
        $this->addSql('ALTER TABLE launch_point_contacts DROP FOREIGN KEY FK_2745925A73154ED4');
        $this->addSql('DROP TABLE launch_point_contacts');
        $this->addSql('ALTER TABLE prayer_team CHANGE requires_intersession requires_intersession TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
