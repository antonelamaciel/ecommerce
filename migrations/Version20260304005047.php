<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304005047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_option_value (id INT AUTO_INCREMENT NOT NULL, product_option_id INT NOT NULL, name VARCHAR(255) NOT NULL, is_available TINYINT(1) NOT NULL, INDEX IDX_A938C737C964ABE2 (product_option_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_option_value ADD CONSTRAINT FK_A938C737C964ABE2 FOREIGN KEY (product_option_id) REFERENCES product_option (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_option_value DROP FOREIGN KEY FK_A938C737C964ABE2');
        $this->addSql('DROP TABLE product_option_value');
    }
}
