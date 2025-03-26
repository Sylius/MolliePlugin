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

final class Version20250318084001 extends AbstractMigration
{
    protected const TABLE_NAME = 'mollie_onboarding_wizard_status';

    public function getDescription(): string
    {
        return 'Add table for storing onboarding wizard status';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->hasTable(self::TABLE_NAME),
            sprintf('Table "%s" already exists, skipping migration.', self::TABLE_NAME),
        );

        $this->addSql('CREATE TABLE mollie_onboarding_wizard_status (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, completed TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_35A4CE97A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mollie_onboarding_wizard_status ADD CONSTRAINT FK_35A4CE97A76ED395 FOREIGN KEY (user_id) REFERENCES sylius_admin_user (id)');
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable(self::TABLE_NAME)) {
            return;
        }

        $this->addSql('ALTER TABLE mollie_onboarding_wizard_status DROP FOREIGN KEY FK_35A4CE97A76ED395');
        $this->addSql('DROP TABLE mollie_onboarding_wizard_status');
    }
}
