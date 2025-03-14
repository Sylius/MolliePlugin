<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SyliusMolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231214133331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mollie_configuration_amount_limits (id INT AUTO_INCREMENT NOT NULL, min_amount DOUBLE PRECISION DEFAULT NULL, max_amount DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mollie_configuration ADD amount_limits_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_CONFIG_AMOUNT_LIMITS FOREIGN KEY (amount_limits_id) REFERENCES mollie_configuration_amount_limits(id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mollie_configuration DROP FOREIGN KEY FK_CONFIG_AMOUNT_LIMITS');
        $this->addSql('ALTER TABLE mollie_configuration DROP COLUMN amount_limits_id');
        $this->addSql('DROP TABLE mollie_configuration_amount_limits');
    }
}
