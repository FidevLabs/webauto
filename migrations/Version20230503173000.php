<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steps_request ADD payment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE steps_request ADD CONSTRAINT FK_1B1F1D3A4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('CREATE INDEX IDX_1B1F1D3A4C3A3BB ON steps_request (payment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steps_request DROP FOREIGN KEY FK_1B1F1D3A4C3A3BB');
        $this->addSql('DROP INDEX IDX_1B1F1D3A4C3A3BB ON steps_request');
        $this->addSql('ALTER TABLE steps_request DROP payment_id');
    }
}
