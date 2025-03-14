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

final class Version20201021101147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mollie_email_template (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, style TEXT DEFAULT NULL, UNIQUE INDEX UNIQ_835AAD848CDE5729 (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_email_template_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, content TEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_42127E4A2C2AC5D3 (translatable_id), UNIQUE INDEX mollie_email_template_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mollie_email_template_translation ADD CONSTRAINT FK_42127E4A2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES mollie_email_template (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mollie_email_template_translation DROP FOREIGN KEY FK_42127E4A2C2AC5D3');
        $this->addSql('DROP TABLE mollie_email_template');
        $this->addSql('DROP TABLE mollie_email_template_translation');
    }
}
