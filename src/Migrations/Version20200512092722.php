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

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20200512092722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Regenerated all Sylius Mollie migrations to version 2.0.';
    }

    public function postUp(Schema $schema): void
    {
        $this->cleanMigrationsTable();
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('mollie_configuration')) {
            return;
        }

        $this->addSql('CREATE TABLE mollie_configuration (id INT AUTO_INCREMENT NOT NULL, payment_surcharge_fee INT DEFAULT NULL, amount_limits_id INT DEFAULT NULL, method_image_id INT DEFAULT NULL, default_category_id INT DEFAULT NULL, gateway_id INT DEFAULT NULL, method_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, image LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', minimum_amount LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', maximum_amount LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', payment_type VARCHAR(255) NOT NULL, country LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', can_refunded TINYINT(1) NOT NULL, issuers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', country_restriction VARCHAR(255) DEFAULT NULL, country_level_excluded LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', country_level_allowed LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', order_expiration INT DEFAULT NULL, apple_pay_direct TINYINT(1) NOT NULL, position INT NOT NULL, qr_code_enabled TINYINT(1) DEFAULT NULL, payment_description VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_EDD60BC5EB71DAB7 (payment_surcharge_fee), UNIQUE INDEX UNIQ_EDD60BC52306388E (amount_limits_id), UNIQUE INDEX UNIQ_EDD60BC5DBC26BFF (method_image_id), UNIQUE INDEX UNIQ_EDD60BC5C6B58E54 (default_category_id), INDEX IDX_EDD60BC5577F8E00 (gateway_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_configuration_amount_limits (id INT AUTO_INCREMENT NOT NULL, min_amount DOUBLE PRECISION DEFAULT NULL, max_amount DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_configuration_surcharge_fee (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) DEFAULT NULL, fixed_amount DOUBLE PRECISION DEFAULT NULL, percentage DOUBLE PRECISION DEFAULT NULL, surcharge_limit DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_configuration_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_988446672C2AC5D3 (translatable_id), UNIQUE INDEX mollie_configuration_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_customer (id INT AUTO_INCREMENT NOT NULL, profile_id VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_credit_card_saved VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_5F1CF5B5CCFA12B8 (profile_id), UNIQUE INDEX UNIQ_5F1CF5B5E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_email_template (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, style TEXT DEFAULT NULL, UNIQUE INDEX UNIQ_6FF7452C8CDE5729 (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_email_template_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, content TEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_21BA0DD02C2AC5D3 (translatable_id), UNIQUE INDEX mollie_email_template_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_logger (id INT AUTO_INCREMENT NOT NULL, level INT NOT NULL, error_code INT NOT NULL, message VARCHAR(1000) NOT NULL, date_time DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_method_image (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_onboarding_wizard_status (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, completed TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_35A4CE97A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_product_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_87AE2C2F5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_subscription (id INT AUTO_INCREMENT NOT NULL, subscription_configuration_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, order_item_id INT DEFAULT NULL, state VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, started_at DATETIME DEFAULT NULL, recent_failed_payments_count INT DEFAULT 0 NOT NULL, processing_state VARCHAR(255) DEFAULT \'none\' NOT NULL, payment_state VARCHAR(255) DEFAULT \'pending\' NOT NULL, UNIQUE INDEX UNIQ_255E3D74B3185C (subscription_configuration_id), INDEX IDX_255E3D749395C3F3 (customer_id), INDEX IDX_255E3D74E415FB15 (order_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_subscription_orders (subscription_id INT NOT NULL, order_id INT NOT NULL, INDEX IDX_AC10D4809A1887DC (subscription_id), INDEX IDX_AC10D4808D9F6D38 (order_id), PRIMARY KEY(subscription_id, order_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_subscription_payments (subscription_id INT NOT NULL, payment_id INT NOT NULL, INDEX IDX_DC1E0C489A1887DC (subscription_id), INDEX IDX_DC1E0C484C3A3BB (payment_id), PRIMARY KEY(subscription_id, payment_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_subscription_configuration (id INT AUTO_INCREMENT NOT NULL, host_name VARCHAR(255) NOT NULL, port INT DEFAULT NULL, subscription_id VARCHAR(255) DEFAULT NULL, mandate_id VARCHAR(255) DEFAULT NULL, mollie_customer_id VARCHAR(255) DEFAULT NULL, number_of_repetitions INT NOT NULL, payment_details_configuration LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', `interval` VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_subscription_schedule (id INT AUTO_INCREMENT NOT NULL, mollie_subscription_id INT DEFAULT NULL, scheduled_date DATETIME NOT NULL, fulfilled_date DATETIME DEFAULT NULL, schedule_index INT NOT NULL, INDEX IDX_E3F48681D38231D4 (mollie_subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC5EB71DAB7 FOREIGN KEY (payment_surcharge_fee) REFERENCES mollie_configuration_surcharge_fee (id)');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC52306388E FOREIGN KEY (amount_limits_id) REFERENCES mollie_configuration_amount_limits (id)');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC5DBC26BFF FOREIGN KEY (method_image_id) REFERENCES mollie_method_image (id)');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC5C6B58E54 FOREIGN KEY (default_category_id) REFERENCES mollie_product_type (id)');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC5577F8E00 FOREIGN KEY (gateway_id) REFERENCES sylius_gateway_config (id)');
        $this->addSql('ALTER TABLE mollie_configuration_translation ADD CONSTRAINT FK_988446672C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES mollie_configuration (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mollie_email_template_translation ADD CONSTRAINT FK_21BA0DD02C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES mollie_email_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mollie_onboarding_wizard_status ADD CONSTRAINT FK_35A4CE97A76ED395 FOREIGN KEY (user_id) REFERENCES sylius_admin_user (id)');
        $this->addSql('ALTER TABLE mollie_subscription ADD CONSTRAINT FK_255E3D74B3185C FOREIGN KEY (subscription_configuration_id) REFERENCES mollie_subscription_configuration (id)');
        $this->addSql('ALTER TABLE mollie_subscription ADD CONSTRAINT FK_255E3D749395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id)');
        $this->addSql('ALTER TABLE mollie_subscription ADD CONSTRAINT FK_255E3D74E415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id)');
        $this->addSql('ALTER TABLE mollie_subscription_orders ADD CONSTRAINT FK_AC10D4809A1887DC FOREIGN KEY (subscription_id) REFERENCES mollie_subscription (id)');
        $this->addSql('ALTER TABLE mollie_subscription_orders ADD CONSTRAINT FK_AC10D4808D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
        $this->addSql('ALTER TABLE mollie_subscription_payments ADD CONSTRAINT FK_DC1E0C489A1887DC FOREIGN KEY (subscription_id) REFERENCES mollie_subscription (id)');
        $this->addSql('ALTER TABLE mollie_subscription_payments ADD CONSTRAINT FK_DC1E0C484C3A3BB FOREIGN KEY (payment_id) REFERENCES sylius_payment (id)');
        $this->addSql('ALTER TABLE mollie_subscription_schedule ADD CONSTRAINT FK_E3F48681D38231D4 FOREIGN KEY (mollie_subscription_id) REFERENCES mollie_subscription (id)');
        $this->addSql('ALTER TABLE sylius_order ADD abandoned_email TINYINT(1) NOT NULL, ADD recurring_sequence_index INT DEFAULT NULL, ADD qr_code TEXT DEFAULT NULL, ADD mollie_payment_id TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD product_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B7414959723 FOREIGN KEY (product_type_id) REFERENCES mollie_product_type (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_677B9B7414959723 ON sylius_product (product_type_id)');
        $this->addSql('ALTER TABLE sylius_product_variant ADD recurring TINYINT(1) DEFAULT 0 NOT NULL, ADD recurring_times INT DEFAULT NULL, ADD recurring_interval VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('mollie_configuration')) {
            return;
        }

        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B7414959723');
        $this->addSql('ALTER TABLE mollie_configuration DROP FOREIGN KEY FK_EDD60BC5EB71DAB7');
        $this->addSql('ALTER TABLE mollie_configuration DROP FOREIGN KEY FK_EDD60BC52306388E');
        $this->addSql('ALTER TABLE mollie_configuration DROP FOREIGN KEY FK_EDD60BC5DBC26BFF');
        $this->addSql('ALTER TABLE mollie_configuration DROP FOREIGN KEY FK_EDD60BC5C6B58E54');
        $this->addSql('ALTER TABLE mollie_configuration DROP FOREIGN KEY FK_EDD60BC5577F8E00');
        $this->addSql('ALTER TABLE mollie_configuration_translation DROP FOREIGN KEY FK_988446672C2AC5D3');
        $this->addSql('ALTER TABLE mollie_email_template_translation DROP FOREIGN KEY FK_21BA0DD02C2AC5D3');
        $this->addSql('ALTER TABLE mollie_onboarding_wizard_status DROP FOREIGN KEY FK_35A4CE97A76ED395');
        $this->addSql('ALTER TABLE mollie_subscription DROP FOREIGN KEY FK_255E3D74B3185C');
        $this->addSql('ALTER TABLE mollie_subscription DROP FOREIGN KEY FK_255E3D749395C3F3');
        $this->addSql('ALTER TABLE mollie_subscription DROP FOREIGN KEY FK_255E3D74E415FB15');
        $this->addSql('ALTER TABLE mollie_subscription_orders DROP FOREIGN KEY FK_AC10D4809A1887DC');
        $this->addSql('ALTER TABLE mollie_subscription_orders DROP FOREIGN KEY FK_AC10D4808D9F6D38');
        $this->addSql('ALTER TABLE mollie_subscription_payments DROP FOREIGN KEY FK_DC1E0C489A1887DC');
        $this->addSql('ALTER TABLE mollie_subscription_payments DROP FOREIGN KEY FK_DC1E0C484C3A3BB');
        $this->addSql('ALTER TABLE mollie_subscription_schedule DROP FOREIGN KEY FK_E3F48681D38231D4');
        $this->addSql('DROP TABLE mollie_configuration');
        $this->addSql('DROP TABLE mollie_configuration_amount_limits');
        $this->addSql('DROP TABLE mollie_configuration_surcharge_fee');
        $this->addSql('DROP TABLE mollie_configuration_translation');
        $this->addSql('DROP TABLE mollie_customer');
        $this->addSql('DROP TABLE mollie_email_template');
        $this->addSql('DROP TABLE mollie_email_template_translation');
        $this->addSql('DROP TABLE mollie_logger');
        $this->addSql('DROP TABLE mollie_method_image');
        $this->addSql('DROP TABLE mollie_onboarding_wizard_status');
        $this->addSql('DROP TABLE mollie_product_type');
        $this->addSql('DROP TABLE mollie_subscription');
        $this->addSql('DROP TABLE mollie_subscription_orders');
        $this->addSql('DROP TABLE mollie_subscription_payments');
        $this->addSql('DROP TABLE mollie_subscription_configuration');
        $this->addSql('DROP TABLE mollie_subscription_schedule');
        $this->addSql('ALTER TABLE sylius_order DROP abandoned_email, DROP recurring_sequence_index, DROP qr_code, DROP mollie_payment_id');
        $this->addSql('DROP INDEX IDX_677B9B7414959723 ON sylius_product');
        $this->addSql('ALTER TABLE sylius_product DROP product_type_id');
        $this->addSql('ALTER TABLE sylius_product_variant DROP recurring, DROP recurring_times, DROP recurring_interval');
    }

    private function cleanMigrationsTable(): void
    {
        $this->connection->executeStatement('DELETE FROM sylius_migrations WHERE version LIKE :version AND version NOT IN (:current)', [
            'version' => 'SyliusMolliePlugin\\\\Migrations\\\\Version%',
            'current' => [
                'Sylius\\MolliePlugin\\Migrations\\Version20200512092722',
                self::class,
            ],
        ], [
            'version' => Types::STRING,
            'current' => ArrayParameterType::STRING,
        ]);
        $this->connection->executeStatement('DELETE FROM sylius_migrations WHERE version LIKE :version AND version NOT IN (:current)', [
            'version' => 'BitBag\\\\SyliusMolliePlugin\\\\Migrations\\\\Version%',
            'current' => [
                'Sylius\\MolliePlugin\\Migrations\\Version20200512092722',
                self::class,
            ],
        ], [
            'version' => Types::STRING,
            'current' => ArrayParameterType::STRING,
        ]);
    }
}
