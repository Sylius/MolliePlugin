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

final class Version20200825151019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mollie_configuration ADD default_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mollie_configuration ADD CONSTRAINT FK_23CC85048298F FOREIGN KEY (default_category_id) REFERENCES mollie_product_type (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_23CC85048298F ON mollie_configuration (default_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mollie_configuration DROP FOREIGN KEY FK_23CC85048298F');
        $this->addSql('DROP INDEX UNIQ_23CC85048298F ON mollie_configuration');
        $this->addSql('ALTER TABLE mollie_configuration DROP default_category_id');
    }
}
