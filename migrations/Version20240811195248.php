<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240811195248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT fk_723705d1a76ed395');
        $this->addSql('DROP INDEX idx_723705d1a76ed395');
        $this->addSql('ALTER TABLE transaction ADD payer_id INT NOT NULL');
        $this->addSql('ALTER TABLE transaction RENAME COLUMN user_id TO payee_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transaction ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE transaction DROP payee_id');
        $this->addSql('ALTER TABLE transaction DROP payer_id');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT fk_723705d1a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_723705d1a76ed395 ON transaction (user_id)');
    }
}
