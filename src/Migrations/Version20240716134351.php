<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\MolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20240716134351 extends AbstractMigration
{
    private const TABLE_NAME = 'mollie_configuration';

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Change qr_code_enabled column to allow NULL values and set default to NULL';
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ' . self::TABLE_NAME . ' MODIFY qr_code_enabled TINYINT(1) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ' . self::TABLE_NAME . ' MODIFY qr_code_enabled TINYINT(1) NOT NULL');
    }
}
