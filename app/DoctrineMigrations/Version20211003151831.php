<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211003151831 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_prices');
        $this->addSql('ALTER TABLE product ADD price DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_prices (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, min_quantity INT NOT NULL, max_quantity INT NOT NULL, unit_price DOUBLE PRECISION NOT NULL, promotional_price DOUBLE PRECISION DEFAULT NULL, promotional_expiry_date DATE DEFAULT NULL, deleted DATETIME DEFAULT NULL, deleted_by VARCHAR(30) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, created DATETIME NOT NULL, creator VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, modified DATETIME NOT NULL, modified_by VARCHAR(30) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_86B72CFD4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_prices ADD CONSTRAINT FK_86B72CFD4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product DROP price');
    }
}
