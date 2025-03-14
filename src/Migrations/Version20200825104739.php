<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200825104739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mollie_product_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_FCC472585E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_product ADD product_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B7414959723 FOREIGN KEY (product_type_id) REFERENCES mollie_product_type (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_677B9B7414959723 ON sylius_product (product_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B7414959723');
        $this->addSql('DROP TABLE mollie_product_type');
        $this->addSql('DROP INDEX IDX_677B9B7414959723 ON sylius_product');
        $this->addSql('ALTER TABLE sylius_product DROP product_type_id');
    }
}
