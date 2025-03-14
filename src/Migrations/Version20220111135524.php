<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111135524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_variant ADD recurring TINYINT(1) DEFAULT \'0\' NOT NULL, ADD recurring_times INT(11) DEFAULT NULL, ADD recurring_interval VARCHAR(255) DEFAULT NULL, ADD complete_recurring_price TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_variant DROP recurring, DROP recurring_interval, DROP recurring_times, DROP complete_recurring_price');
    }
}
