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

final class Version20250401083148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Moved the onboarding status to the admin user entity and removed the onboarding status entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mollie_onboarding_wizard_status DROP FOREIGN KEY FK_35A4CE97A76ED395');
        $this->addSql('DROP TABLE mollie_onboarding_wizard_status');
        $this->addSql('ALTER TABLE sylius_admin_user ADD mollie_onboarding_completed TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mollie_onboarding_wizard_status (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, completed TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_35A4CE97A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE mollie_onboarding_wizard_status ADD CONSTRAINT FK_35A4CE97A76ED395 FOREIGN KEY (user_id) REFERENCES sylius_admin_user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE sylius_admin_user DROP mollie_onboarding_completed');
    }
}
