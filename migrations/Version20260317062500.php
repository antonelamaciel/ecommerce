<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Allow deleting a Product even if it has associated OrderDetails rows.
 * Sets product_object_id to NULL on delete instead of raising an FK error.
 */
final class Version20260317062500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change order_details.product_object_id FK to ON DELETE SET NULL so products can be deleted.';
    }

    public function up(Schema $schema): void
    {
        // Drop the existing FK constraint (no ON DELETE rule → defaults to RESTRICT)
        $this->addSql('ALTER TABLE order_details DROP FOREIGN KEY FK_845CA2C14D8DE0A6');

        // Re-add the column as nullable (it may already be nullable, but ensure it)
        $this->addSql('ALTER TABLE order_details CHANGE product_object_id product_object_id INT DEFAULT NULL');

        // Re-create the FK with ON DELETE SET NULL
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C14D8DE0A6 FOREIGN KEY (product_object_id) REFERENCES product (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // Restore original FK without ON DELETE rule
        $this->addSql('ALTER TABLE order_details DROP FOREIGN KEY FK_845CA2C14D8DE0A6');
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C14D8DE0A6 FOREIGN KEY (product_object_id) REFERENCES product (id)');
    }
}
