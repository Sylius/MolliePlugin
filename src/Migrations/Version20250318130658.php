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

final class Version20250318130658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename column order_expiration to order_expiration_days in mollie_configuration table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mollie_configuration CHANGE order_expiration order_expiration_days INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mollie_configuration CHANGE order_expiration_days order_expiration INT DEFAULT NULL');
    }
}
