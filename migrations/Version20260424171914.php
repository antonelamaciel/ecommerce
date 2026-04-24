<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Adds created_at to product table.
 */
final class Version20260424171914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds created_at to product table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP created_at');
    }
}
