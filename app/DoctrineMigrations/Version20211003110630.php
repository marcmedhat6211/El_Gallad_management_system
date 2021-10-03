<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211003110630 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_attribute DROP FOREIGN KEY FK_94DA597612469DE2');
        $this->addSql('DROP INDEX IDX_94DA597612469DE2 ON product_attribute');
        $this->addSql('ALTER TABLE product_attribute CHANGE category_id product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_attribute ADD CONSTRAINT FK_94DA59764584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_94DA59764584665A ON product_attribute (product_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_attribute DROP FOREIGN KEY FK_94DA59764584665A');
        $this->addSql('DROP INDEX IDX_94DA59764584665A ON product_attribute');
        $this->addSql('ALTER TABLE product_attribute CHANGE product_id category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_attribute ADD CONSTRAINT FK_94DA597612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_94DA597612469DE2 ON product_attribute (category_id)');
    }
}
