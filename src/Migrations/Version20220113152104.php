<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220113152104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mollie_subscription_schedule (id INT AUTO_INCREMENT NOT NULL, mollie_subscription_id INT DEFAULT NULL, scheduled_date DATETIME NOT NULL, fulfilled_date DATETIME DEFAULT NULL, INDEX IDX_79B927C0D38231D4 (mollie_subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mollie_subscription_schedule ADD CONSTRAINT FK_79B927C0D38231D4 FOREIGN KEY (mollie_subscription_id) REFERENCES mollie_subscription (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE mollie_subscription_schedule');
    }
}
