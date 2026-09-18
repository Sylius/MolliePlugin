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

final class Version20260630000001 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'Add migrated_at column to mollie_subscription (PostgreSQL)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mollie_subscription ADD migrated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mollie_subscription DROP migrated_at');
    }
}
