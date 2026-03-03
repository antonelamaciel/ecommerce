<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303030438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personalize ADD email VARCHAR(255) DEFAULT NULL, ADD whatsapp VARCHAR(50) DEFAULT NULL, ADD instagram VARCHAR(255) DEFAULT NULL, ADD twitter VARCHAR(255) DEFAULT NULL, ADD linkedin VARCHAR(255) DEFAULT NULL, ADD tiktok VARCHAR(255) DEFAULT NULL, ADD youtube VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personalize DROP email, DROP whatsapp, DROP instagram, DROP twitter, DROP linkedin, DROP tiktok, DROP youtube');
    }
}
