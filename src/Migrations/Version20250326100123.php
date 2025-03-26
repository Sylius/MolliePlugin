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

final class Version20250326100123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mollie_configuration DROP INDEX FK_CONFIG_AMOUNT_LIMITS, ADD UNIQUE INDEX UNIQ_EDD60BC52306388E (amount_limits_id)');
        $this->addSql('ALTER TABLE mollie_configuration RENAME INDEX uniq_23cc8504eb71dab7 TO UNIQ_EDD60BC5EB71DAB7');
        $this->addSql('ALTER TABLE mollie_configuration RENAME INDEX uniq_23cc8504dbc26bff TO UNIQ_EDD60BC5DBC26BFF');
        $this->addSql('ALTER TABLE mollie_configuration RENAME INDEX uniq_23cc85048298f TO UNIQ_EDD60BC5C6B58E54');
        $this->addSql('ALTER TABLE mollie_configuration RENAME INDEX idx_23cc8504577f8e00 TO IDX_EDD60BC5577F8E00');
        $this->addSql('ALTER TABLE mollie_configuration_translation RENAME INDEX idx_369b057a2c2ac5d3 TO IDX_988446672C2AC5D3');
        $this->addSql('ALTER TABLE mollie_customer RENAME INDEX uniq_162c0bf1ccfa12b8 TO UNIQ_5F1CF5B5CCFA12B8');
        $this->addSql('ALTER TABLE mollie_customer RENAME INDEX uniq_162c0bf1e7927c74 TO UNIQ_5F1CF5B5E7927C74');
        $this->addSql('ALTER TABLE mollie_email_template RENAME INDEX uniq_835aad848cde5729 TO UNIQ_6FF7452C8CDE5729');
        $this->addSql('ALTER TABLE mollie_email_template_translation RENAME INDEX idx_42127e4a2c2ac5d3 TO IDX_21BA0DD02C2AC5D3');
        $this->addSql('ALTER TABLE mollie_product_type RENAME INDEX uniq_fcc472585e237e06 TO UNIQ_87AE2C2F5E237E06');
        $this->addSql('ALTER TABLE mollie_subscription RENAME INDEX uniq_5e346303b3185c TO UNIQ_255E3D74B3185C');
        $this->addSql('ALTER TABLE mollie_subscription RENAME INDEX idx_5e3463039395c3f3 TO IDX_255E3D749395C3F3');
        $this->addSql('ALTER TABLE mollie_subscription RENAME INDEX idx_5e346303e415fb15 TO IDX_255E3D74E415FB15');
        $this->addSql('ALTER TABLE mollie_subscription_orders RENAME INDEX idx_dce71bd39a1887dc TO IDX_AC10D4809A1887DC');
        $this->addSql('ALTER TABLE mollie_subscription_orders RENAME INDEX idx_dce71bd38d9f6d38 TO IDX_AC10D4808D9F6D38');
        $this->addSql('ALTER TABLE mollie_subscription_payments RENAME INDEX idx_4653ad099a1887dc TO IDX_DC1E0C489A1887DC');
        $this->addSql('ALTER TABLE mollie_subscription_payments RENAME INDEX idx_4653ad094c3a3bb TO IDX_DC1E0C484C3A3BB');
        $this->addSql('ALTER TABLE mollie_subscription_schedule RENAME INDEX idx_79b927c0d38231d4 TO IDX_E3F48681D38231D4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mollie_configuration DROP INDEX UNIQ_EDD60BC52306388E, ADD INDEX FK_CONFIG_AMOUNT_LIMITS (amount_limits_id)');
        $this->addSql('ALTER TABLE mollie_configuration RENAME INDEX idx_edd60bc5577f8e00 TO IDX_23CC8504577F8E00');
        $this->addSql('ALTER TABLE mollie_configuration RENAME INDEX uniq_edd60bc5c6b58e54 TO UNIQ_23CC85048298F');
        $this->addSql('ALTER TABLE mollie_configuration RENAME INDEX uniq_edd60bc5dbc26bff TO UNIQ_23CC8504DBC26BFF');
        $this->addSql('ALTER TABLE mollie_configuration RENAME INDEX uniq_edd60bc5eb71dab7 TO UNIQ_23CC8504EB71DAB7');
        $this->addSql('ALTER TABLE mollie_configuration_translation RENAME INDEX idx_988446672c2ac5d3 TO IDX_369B057A2C2AC5D3');
        $this->addSql('ALTER TABLE mollie_customer RENAME INDEX uniq_5f1cf5b5ccfa12b8 TO UNIQ_162C0BF1CCFA12B8');
        $this->addSql('ALTER TABLE mollie_customer RENAME INDEX uniq_5f1cf5b5e7927c74 TO UNIQ_162C0BF1E7927C74');
        $this->addSql('ALTER TABLE mollie_email_template RENAME INDEX uniq_6ff7452c8cde5729 TO UNIQ_835AAD848CDE5729');
        $this->addSql('ALTER TABLE mollie_email_template_translation RENAME INDEX idx_21ba0dd02c2ac5d3 TO IDX_42127E4A2C2AC5D3');
        $this->addSql('ALTER TABLE mollie_product_type RENAME INDEX uniq_87ae2c2f5e237e06 TO UNIQ_FCC472585E237E06');
        $this->addSql('ALTER TABLE mollie_subscription RENAME INDEX idx_255e3d749395c3f3 TO IDX_5E3463039395C3F3');
        $this->addSql('ALTER TABLE mollie_subscription RENAME INDEX idx_255e3d74e415fb15 TO IDX_5E346303E415FB15');
        $this->addSql('ALTER TABLE mollie_subscription RENAME INDEX uniq_255e3d74b3185c TO UNIQ_5E346303B3185C');
        $this->addSql('ALTER TABLE mollie_subscription_orders RENAME INDEX idx_ac10d4808d9f6d38 TO IDX_DCE71BD38D9F6D38');
        $this->addSql('ALTER TABLE mollie_subscription_orders RENAME INDEX idx_ac10d4809a1887dc TO IDX_DCE71BD39A1887DC');
        $this->addSql('ALTER TABLE mollie_subscription_payments RENAME INDEX idx_dc1e0c484c3a3bb TO IDX_4653AD094C3A3BB');
        $this->addSql('ALTER TABLE mollie_subscription_payments RENAME INDEX idx_dc1e0c489a1887dc TO IDX_4653AD099A1887DC');
        $this->addSql('ALTER TABLE mollie_subscription_schedule RENAME INDEX idx_e3f48681d38231d4 TO IDX_79B927C0D38231D4');
    }
}
