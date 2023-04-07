<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230406120851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD agency_id INT NOT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81CDEADB2A FOREIGN KEY (agency_id) REFERENCES agency (id)');
        $this->addSql('CREATE INDEX IDX_D4E6F81CDEADB2A ON address (agency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81CDEADB2A');
        $this->addSql('DROP INDEX IDX_D4E6F81CDEADB2A ON address');
        $this->addSql('ALTER TABLE address DROP agency_id');
    }
}
