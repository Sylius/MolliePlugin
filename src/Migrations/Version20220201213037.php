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

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220201213037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mollie_subscription_configuration (id INT AUTO_INCREMENT NOT NULL, host_name VARCHAR(255) NOT NULL, port INT DEFAULT NULL, subscription_id VARCHAR(255) DEFAULT NULL, mandate_id VARCHAR(255) DEFAULT NULL, mollie_customer_id VARCHAR(255) DEFAULT NULL, number_of_repetitions INT NOT NULL, payment_details_configuration LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE mollie_subscription_first');
        $this->addSql('ALTER TABLE mollie_subscription ADD subscription_configuration_id INT DEFAULT NULL, ADD recent_failed_payments_count INT DEFAULT 0 NOT NULL, ADD payment_state VARCHAR(255) DEFAULT \'pending\' NOT NULL, DROP `interval`, DROP subscription_id, DROP mollie_customer_id, DROP number_of_repetitions, DROP mandate_id, DROP payment_details_configuration');
        $this->addSql('ALTER TABLE mollie_subscription ADD CONSTRAINT FK_5E346303B3185C FOREIGN KEY (subscription_configuration_id) REFERENCES mollie_subscription_configuration (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5E346303B3185C ON mollie_subscription (subscription_configuration_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mollie_subscription DROP FOREIGN KEY FK_5E346303B3185C');
        $this->addSql('CREATE TABLE mollie_subscription_first (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, subscription_id VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, customer_id VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, state VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, UNIQUE INDEX UNIQ_97993F5D9395C3F3 (customer_id), UNIQUE INDEX UNIQ_97993F5D9A1887DC (subscription_id), UNIQUE INDEX UNIQ_97993F5D8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE mollie_subscription_first ADD CONSTRAINT FK_97993F5D8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE mollie_subscription_configuration');
        $this->addSql('DROP INDEX UNIQ_5E346303B3185C ON mollie_subscription');
        $this->addSql('ALTER TABLE mollie_subscription ADD `interval` VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, ADD subscription_id VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD mollie_customer_id VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD number_of_repetitions INT NOT NULL, ADD mandate_id VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD payment_details_configuration LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', DROP subscription_configuration_id, DROP recent_failed_payments_count, DROP payment_state');
    }
}
