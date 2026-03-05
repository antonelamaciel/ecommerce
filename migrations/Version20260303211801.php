<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303211801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bundle (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bundle_product (bundle_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_25B2BDD4F1FAD9D3 (bundle_id), INDEX IDX_25B2BDD44584665A (product_id), PRIMARY KEY(bundle_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bundle_product ADD CONSTRAINT FK_25B2BDD4F1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES bundle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bundle_product ADD CONSTRAINT FK_25B2BDD44584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bundle_product DROP FOREIGN KEY FK_25B2BDD4F1FAD9D3');
        $this->addSql('ALTER TABLE bundle_product DROP FOREIGN KEY FK_25B2BDD44584665A');
        $this->addSql('DROP TABLE bundle');
        $this->addSql('DROP TABLE bundle_product');
    }
}
