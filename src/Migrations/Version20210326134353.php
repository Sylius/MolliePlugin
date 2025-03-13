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

final class Version20210326134353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $adjustmentTableAltered = $schema->getTable('sylius_adjustment')->hasColumn('shipment_id');
        $channelTableAltered = $schema->getTable('sylius_channel')->hasColumn('contact_phone_number');
        $attributeTableAltered = $schema->getTable('sylius_product_attribute')->hasColumn('translatable');
        $shipmentTableAltered = $schema->getTable('sylius_shipment')->hasColumn('adjustments_total');
        if ($adjustmentTableAltered && $channelTableAltered && $attributeTableAltered && $shipmentTableAltered) {
            return;
        }

        $this->addSql('ALTER TABLE sylius_adjustment ADD shipment_id INT DEFAULT NULL, ADD details JSON NOT NULL');
        $this->addSql('ALTER TABLE sylius_adjustment ADD CONSTRAINT FK_ACA6E0F27BE036FC FOREIGN KEY (shipment_id) REFERENCES sylius_shipment (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_ACA6E0F27BE036FC ON sylius_adjustment (shipment_id)');
        $this->addSql('ALTER TABLE sylius_channel ADD contact_phone_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_attribute ADD translatable TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE sylius_product_attribute_value CHANGE locale_code locale_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shipment ADD adjustments_total INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_adjustment DROP FOREIGN KEY FK_ACA6E0F27BE036FC');
        $this->addSql('DROP INDEX IDX_ACA6E0F27BE036FC ON sylius_adjustment');
        $this->addSql('ALTER TABLE sylius_adjustment DROP shipment_id, DROP details');
        $this->addSql('ALTER TABLE sylius_channel DROP contact_phone_number');
        $this->addSql('ALTER TABLE sylius_product_attribute DROP translatable');
        $this->addSql('ALTER TABLE sylius_product_attribute_value CHANGE locale_code locale_code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE sylius_shipment DROP adjustments_total');
    }
}
