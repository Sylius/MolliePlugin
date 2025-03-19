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

class Version20240716134351 extends AbstractMigration
{
    private const TABLE_NAME = 'mollie_configuration';

    public function getDescription(): string
    {
        return 'Change qr_code_enabled column to allow NULL values and set default to NULL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ' . self::TABLE_NAME . ' MODIFY qr_code_enabled TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ' . self::TABLE_NAME . ' MODIFY qr_code_enabled TINYINT(1) NOT NULL');
    }
}
