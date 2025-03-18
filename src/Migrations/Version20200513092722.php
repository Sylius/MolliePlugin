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

namespace Sylius\MolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200513092722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mollie_configuration (id INT AUTO_INCREMENT NOT NULL, payment_surcharge_fee INT DEFAULT NULL, method_image_id INT DEFAULT NULL, gateway_id INT DEFAULT NULL, method_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, image LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', minimum_amount LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', maximum_amount LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', payment_type VARCHAR(255) NOT NULL, country LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', can_refunded TINYINT(1) NOT NULL, issuers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', country_level LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', order_expiration INT DEFAULT NULL, UNIQUE INDEX UNIQ_23CC85045E237E06 (name), UNIQUE INDEX UNIQ_23CC8504EB71DAB7 (payment_surcharge_fee), UNIQUE INDEX UNIQ_23CC8504DBC26BFF (method_image_id), INDEX IDX_23CC8504577F8E00 (gateway_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_subscription_first (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, subscription_id VARCHAR(255) DEFAULT NULL, customer_id VARCHAR(255) DEFAULT NULL, state VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_97993F5D9A1887DC (subscription_id), UNIQUE INDEX UNIQ_97993F5D9395C3F3 (customer_id), UNIQUE INDEX UNIQ_97993F5D8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_method_image (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_logger (id INT AUTO_INCREMENT NOT NULL, level INT NOT NULL, error_code INT NOT NULL, message VARCHAR(255) NOT NULL, date_time DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_configuration_surcharge_fee (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) DEFAULT NULL, fixed_amount DOUBLE PRECISION DEFAULT NULL, percentage DOUBLE PRECISION DEFAULT NULL, surcharge_limit DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_23CC8504EB71DAB7 FOREIGN KEY (payment_surcharge_fee) REFERENCES mollie_configuration_surcharge_fee (id)');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_23CC8504DBC26BFF FOREIGN KEY (method_image_id) REFERENCES mollie_method_image (id)');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_23CC8504577F8E00 FOREIGN KEY (gateway_id) REFERENCES sylius_gateway_config (id)');
        $this->addSql('ALTER TABLE mollie_subscription_first ADD CONSTRAINT FK_97993F5D8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mollie_configuration DROP FOREIGN KEY FK_23CC8504DBC26BFF');
        $this->addSql('ALTER TABLE mollie_configuration DROP FOREIGN KEY FK_23CC8504EB71DAB7');
        $this->addSql('DROP TABLE mollie_configuration');
        $this->addSql('DROP TABLE mollie_subscription_first');
        $this->addSql('DROP TABLE mollie_method_image');
        $this->addSql('DROP TABLE mollie_logger');
        $this->addSql('DROP TABLE mollie_configuration_surcharge_fee');
    }
}
