<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204223058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        try {
            $this->addSql(<<<'SQL'
                ALTER TABLE event_participant ADD raw_spam_details LONGTEXT DEFAULT NULL
            SQL);
        } 
        catch (\Exception $e) {
            // Log the exception or handle it as needed
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        
        $this->addSql(<<<'SQL'
            ALTER TABLE event_participant DROP raw_spam_details
        SQL);
    }
}
