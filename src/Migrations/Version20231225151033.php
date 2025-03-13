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
final class Version20231225151033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renaming bitbag mollie tables';
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function up(Schema $schema): void
    {
        // created in local migration
        $this->renameTableIfExists($schema, 'bitbag_onboarding_wizard_status', 'mollie_onboarding_wizard_status');
        if ($schema->hasTable('mollie_configuration')) {
            return;
        }
        $this->renameTableIfExists($schema, 'bitbag_mollie_configuration', 'mollie_configuration');
        $this->renameTableIfExists($schema, 'bitbag_mollie_configuration_surcharge_fee', 'mollie_configuration_surcharge_fee');
        $this->renameTableIfExists($schema, 'bitbag_mollie_configuration_translation', 'mollie_configuration_translation');
        $this->renameTableIfExists($schema, 'bitbag_mollie_customer', 'mollie_customer');
        $this->renameTableIfExists($schema, 'bitbag_mollie_email_template', 'mollie_email_template');
        $this->renameTableIfExists($schema, 'bitbag_mollie_email_template_translation', 'mollie_email_template_translation');
        $this->renameTableIfExists($schema, 'bitbag_mollie_logger', 'mollie_logger');
        $this->renameTableIfExists($schema, 'bitbag_mollie_method_image', 'mollie_method_image');
        $this->renameTableIfExists($schema, 'bitbag_mollie_product_type', 'mollie_product_type');
        $this->renameTableIfExists($schema, 'bitbag_mollie_subscription', 'mollie_subscription');
        $this->renameTableIfExists($schema, 'bitbag_mollie_subscription_orders', 'mollie_subscription_orders');
        $this->renameTableIfExists($schema, 'bitbag_mollie_subscription_payments', 'mollie_subscription_payments');
        $this->renameTableIfExists($schema, 'bitbag_mollie_subscription_configuration', 'mollie_subscription_configuration');
        $this->renameTableIfExists($schema, 'bitbag_mollie_subscription_schedule', 'mollie_subscription_schedule');
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->renameTableIfExists($schema, 'mollie_onboarding_wizard_status', 'bitbag_onboarding_wizard_status');
        $this->renameTableIfExists($schema, 'mollie_configuration', 'bitbag_mollie_configuration');
        $this->renameTableIfExists($schema, 'mollie_configuration_surcharge_fee', 'bitbag_mollie_configuration_surcharge_fee');
        $this->renameTableIfExists($schema, 'mollie_configuration_translation', 'bitbag_mollie_configuration_translation');
        $this->renameTableIfExists($schema, 'mollie_customer', 'bitbag_mollie_customer');
        $this->renameTableIfExists($schema, 'mollie_email_template', 'bitbag_mollie_email_template');
        $this->renameTableIfExists($schema, 'mollie_email_template_translation', 'bitbag_mollie_email_template_translation');
        $this->renameTableIfExists($schema, 'mollie_logger', 'bitbag_mollie_logger');
        $this->renameTableIfExists($schema, 'mollie_method_image', 'bitbag_mollie_method_image');
        $this->renameTableIfExists($schema, 'mollie_product_type', 'bitbag_mollie_product_type');
        $this->renameTableIfExists($schema, 'mollie_subscription', 'bitbag_mollie_subscription');
        $this->renameTableIfExists($schema, 'mollie_subscription_orders', 'bitbag_mollie_subscription_orders');
        $this->renameTableIfExists($schema, 'mollie_subscription_payments', 'bitbag_mollie_subscription_payments');
        $this->renameTableIfExists($schema, 'mollie_subscription_configuration', 'bitbag_mollie_subscription_configuration');
        $this->renameTableIfExists($schema, 'mollie_subscription_schedule', 'bitbag_mollie_subscription_schedule');
    }

    /**
     * @param Schema $schema
     * @param string $oldTableName
     * @param string $newTableName
     *
     * @return void
     */
    private function renameTableIfExists(Schema $schema, string $oldTableName, string $newTableName): void
    {
        if ($schema->hasTable($newTableName)) {
            return;
        }

        if ($schema->hasTable($oldTableName)) {
            $this->addSql(sprintf('RENAME TABLE %s TO %s', $oldTableName, $newTableName));
        }
    }
}
