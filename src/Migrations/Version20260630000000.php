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
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20260630000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add migrated_at column to mollie_subscription';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mollie_subscription ADD migrated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mollie_subscription DROP migrated_at');
    }
}
