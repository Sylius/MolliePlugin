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
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250509141720 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'Initial migration for PostgreSQL';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('mollie_configuration')) {
            return;
        }

        $this->addSql('CREATE SEQUENCE mollie_configuration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_configuration_amount_limits_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_configuration_surcharge_fee_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_configuration_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_email_template_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_email_template_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_logger_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_method_image_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_product_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_subscription_configuration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mollie_subscription_schedule_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE mollie_configuration (id INT NOT NULL, payment_surcharge_fee INT DEFAULT NULL, amount_limits_id INT DEFAULT NULL, method_image_id INT DEFAULT NULL, default_category_id INT DEFAULT NULL, gateway_id INT DEFAULT NULL, method_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, image TEXT NOT NULL, minimum_amount TEXT NOT NULL, maximum_amount TEXT NOT NULL, payment_type VARCHAR(255) NOT NULL, country TEXT NOT NULL, can_refunded BOOLEAN NOT NULL, issuers TEXT DEFAULT NULL, country_restriction VARCHAR(255) DEFAULT NULL, country_level_excluded TEXT DEFAULT NULL, country_level_allowed TEXT DEFAULT NULL, order_expiration_days INT DEFAULT NULL, apple_pay_direct BOOLEAN NOT NULL, position INT NOT NULL, qr_code_enabled BOOLEAN DEFAULT NULL, payment_description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EDD60BC5EB71DAB7 ON mollie_configuration (payment_surcharge_fee)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EDD60BC52306388E ON mollie_configuration (amount_limits_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EDD60BC5DBC26BFF ON mollie_configuration (method_image_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EDD60BC5C6B58E54 ON mollie_configuration (default_category_id)');
        $this->addSql('CREATE INDEX IDX_EDD60BC5577F8E00 ON mollie_configuration (gateway_id)');
        $this->addSql('COMMENT ON COLUMN mollie_configuration.image IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN mollie_configuration.minimum_amount IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN mollie_configuration.maximum_amount IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN mollie_configuration.country IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN mollie_configuration.issuers IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN mollie_configuration.country_level_excluded IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN mollie_configuration.country_level_allowed IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE mollie_configuration_amount_limits (id INT NOT NULL, min_amount DOUBLE PRECISION DEFAULT NULL, max_amount DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mollie_configuration_surcharge_fee (id INT NOT NULL, type VARCHAR(255) DEFAULT NULL, fixed_amount DOUBLE PRECISION DEFAULT NULL, percentage DOUBLE PRECISION DEFAULT NULL, surcharge_limit DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mollie_configuration_translation (id INT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_988446672C2AC5D3 ON mollie_configuration_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX mollie_configuration_translation_uniq_trans ON mollie_configuration_translation (translatable_id, locale)');
        $this->addSql('CREATE TABLE mollie_customer (id INT NOT NULL, profile_id VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_credit_card_saved VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F1CF5B5CCFA12B8 ON mollie_customer (profile_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F1CF5B5E7927C74 ON mollie_customer (email)');
        $this->addSql('CREATE TABLE mollie_email_template (id INT NOT NULL, type VARCHAR(255) NOT NULL, style TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6FF7452C8CDE5729 ON mollie_email_template (type)');
        $this->addSql('CREATE TABLE mollie_email_template_translation (id INT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, content TEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_21BA0DD02C2AC5D3 ON mollie_email_template_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX mollie_email_template_translation_uniq_trans ON mollie_email_template_translation (translatable_id, locale)');
        $this->addSql('CREATE TABLE mollie_logger (id INT NOT NULL, level INT NOT NULL, error_code INT NOT NULL, message VARCHAR(1000) NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mollie_method_image (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mollie_product_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_87AE2C2F5E237E06 ON mollie_product_type (name)');
        $this->addSql('CREATE TABLE mollie_subscription (id INT NOT NULL, subscription_configuration_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, order_item_id INT DEFAULT NULL, state VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, recent_failed_payments_count INT DEFAULT 0 NOT NULL, processing_state VARCHAR(255) DEFAULT \'none\' NOT NULL, payment_state VARCHAR(255) DEFAULT \'pending\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_255E3D74B3185C ON mollie_subscription (subscription_configuration_id)');
        $this->addSql('CREATE INDEX IDX_255E3D749395C3F3 ON mollie_subscription (customer_id)');
        $this->addSql('CREATE INDEX IDX_255E3D74E415FB15 ON mollie_subscription (order_item_id)');
        $this->addSql('CREATE TABLE mollie_subscription_orders (subscription_id INT NOT NULL, order_id INT NOT NULL, PRIMARY KEY(subscription_id, order_id))');
        $this->addSql('CREATE INDEX IDX_AC10D4809A1887DC ON mollie_subscription_orders (subscription_id)');
        $this->addSql('CREATE INDEX IDX_AC10D4808D9F6D38 ON mollie_subscription_orders (order_id)');
        $this->addSql('CREATE TABLE mollie_subscription_payments (subscription_id INT NOT NULL, payment_id INT NOT NULL, PRIMARY KEY(subscription_id, payment_id))');
        $this->addSql('CREATE INDEX IDX_DC1E0C489A1887DC ON mollie_subscription_payments (subscription_id)');
        $this->addSql('CREATE INDEX IDX_DC1E0C484C3A3BB ON mollie_subscription_payments (payment_id)');
        $this->addSql('CREATE TABLE mollie_subscription_configuration (id INT NOT NULL, host_name VARCHAR(255) NOT NULL, port INT DEFAULT NULL, subscription_id VARCHAR(255) DEFAULT NULL, mandate_id VARCHAR(255) DEFAULT NULL, mollie_customer_id VARCHAR(255) DEFAULT NULL, number_of_repetitions INT NOT NULL, payment_details_configuration TEXT NOT NULL, "interval" VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN mollie_subscription_configuration.payment_details_configuration IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE mollie_subscription_schedule (id INT NOT NULL, mollie_subscription_id INT DEFAULT NULL, scheduled_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, fulfilled_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, schedule_index INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E3F48681D38231D4 ON mollie_subscription_schedule (mollie_subscription_id)');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC5EB71DAB7 FOREIGN KEY (payment_surcharge_fee) REFERENCES mollie_configuration_surcharge_fee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC52306388E FOREIGN KEY (amount_limits_id) REFERENCES mollie_configuration_amount_limits (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC5DBC26BFF FOREIGN KEY (method_image_id) REFERENCES mollie_method_image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC5C6B58E54 FOREIGN KEY (default_category_id) REFERENCES mollie_product_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_EDD60BC5577F8E00 FOREIGN KEY (gateway_id) REFERENCES sylius_gateway_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_configuration_translation ADD CONSTRAINT FK_988446672C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES mollie_configuration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_email_template_translation ADD CONSTRAINT FK_21BA0DD02C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES mollie_email_template (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_subscription ADD CONSTRAINT FK_255E3D74B3185C FOREIGN KEY (subscription_configuration_id) REFERENCES mollie_subscription_configuration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_subscription ADD CONSTRAINT FK_255E3D749395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_subscription ADD CONSTRAINT FK_255E3D74E415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_subscription_orders ADD CONSTRAINT FK_AC10D4809A1887DC FOREIGN KEY (subscription_id) REFERENCES mollie_subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_subscription_orders ADD CONSTRAINT FK_AC10D4808D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_subscription_payments ADD CONSTRAINT FK_DC1E0C489A1887DC FOREIGN KEY (subscription_id) REFERENCES mollie_subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_subscription_payments ADD CONSTRAINT FK_DC1E0C484C3A3BB FOREIGN KEY (payment_id) REFERENCES sylius_payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mollie_subscription_schedule ADD CONSTRAINT FK_E3F48681D38231D4 FOREIGN KEY (mollie_subscription_id) REFERENCES mollie_subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_admin_user ADD mollie_onboarding_completed BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE sylius_order ADD abandoned_email BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE sylius_order ADD recurring_sequence_index INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_order ADD qr_code TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_order ADD mollie_payment_id TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD product_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B7414959723 FOREIGN KEY (product_type_id) REFERENCES mollie_product_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_677B9B7414959723 ON sylius_product (product_type_id)');
        $this->addSql('ALTER TABLE sylius_product_variant ADD recurring BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE sylius_product_variant ADD recurring_times INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_variant ADD recurring_interval VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('mollie_configuration')) {
            return;
        }

        $this->addSql('ALTER TABLE sylius_product DROP CONSTRAINT FK_677B9B7414959723');
        $this->addSql('DROP SEQUENCE mollie_configuration_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_configuration_amount_limits_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_configuration_surcharge_fee_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_configuration_translation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_customer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_email_template_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_email_template_translation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_logger_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_method_image_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_product_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_subscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_subscription_configuration_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mollie_subscription_schedule_id_seq CASCADE');
        $this->addSql('ALTER TABLE mollie_configuration DROP CONSTRAINT FK_EDD60BC5EB71DAB7');
        $this->addSql('ALTER TABLE mollie_configuration DROP CONSTRAINT FK_EDD60BC52306388E');
        $this->addSql('ALTER TABLE mollie_configuration DROP CONSTRAINT FK_EDD60BC5DBC26BFF');
        $this->addSql('ALTER TABLE mollie_configuration DROP CONSTRAINT FK_EDD60BC5C6B58E54');
        $this->addSql('ALTER TABLE mollie_configuration DROP CONSTRAINT FK_EDD60BC5577F8E00');
        $this->addSql('ALTER TABLE mollie_configuration_translation DROP CONSTRAINT FK_988446672C2AC5D3');
        $this->addSql('ALTER TABLE mollie_email_template_translation DROP CONSTRAINT FK_21BA0DD02C2AC5D3');
        $this->addSql('ALTER TABLE mollie_subscription DROP CONSTRAINT FK_255E3D74B3185C');
        $this->addSql('ALTER TABLE mollie_subscription DROP CONSTRAINT FK_255E3D749395C3F3');
        $this->addSql('ALTER TABLE mollie_subscription DROP CONSTRAINT FK_255E3D74E415FB15');
        $this->addSql('ALTER TABLE mollie_subscription_orders DROP CONSTRAINT FK_AC10D4809A1887DC');
        $this->addSql('ALTER TABLE mollie_subscription_orders DROP CONSTRAINT FK_AC10D4808D9F6D38');
        $this->addSql('ALTER TABLE mollie_subscription_payments DROP CONSTRAINT FK_DC1E0C489A1887DC');
        $this->addSql('ALTER TABLE mollie_subscription_payments DROP CONSTRAINT FK_DC1E0C484C3A3BB');
        $this->addSql('ALTER TABLE mollie_subscription_schedule DROP CONSTRAINT FK_E3F48681D38231D4');
        $this->addSql('DROP TABLE mollie_configuration');
        $this->addSql('DROP TABLE mollie_configuration_amount_limits');
        $this->addSql('DROP TABLE mollie_configuration_surcharge_fee');
        $this->addSql('DROP TABLE mollie_configuration_translation');
        $this->addSql('DROP TABLE mollie_customer');
        $this->addSql('DROP TABLE mollie_email_template');
        $this->addSql('DROP TABLE mollie_email_template_translation');
        $this->addSql('DROP TABLE mollie_logger');
        $this->addSql('DROP TABLE mollie_method_image');
        $this->addSql('DROP TABLE mollie_product_type');
        $this->addSql('DROP TABLE mollie_subscription');
        $this->addSql('DROP TABLE mollie_subscription_orders');
        $this->addSql('DROP TABLE mollie_subscription_payments');
        $this->addSql('DROP TABLE mollie_subscription_configuration');
        $this->addSql('DROP TABLE mollie_subscription_schedule');
        $this->addSql('ALTER TABLE sylius_admin_user DROP mollie_onboarding_completed');
        $this->addSql('ALTER TABLE sylius_product_variant DROP recurring');
        $this->addSql('ALTER TABLE sylius_product_variant DROP recurring_times');
        $this->addSql('ALTER TABLE sylius_product_variant DROP recurring_interval');
        $this->addSql('DROP INDEX IDX_677B9B7414959723');
        $this->addSql('ALTER TABLE sylius_product DROP product_type_id');
        $this->addSql('ALTER TABLE sylius_order DROP abandoned_email');
        $this->addSql('ALTER TABLE sylius_order DROP recurring_sequence_index');
        $this->addSql('ALTER TABLE sylius_order DROP qr_code');
        $this->addSql('ALTER TABLE sylius_order DROP mollie_payment_id');
    }
}
