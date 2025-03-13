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

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211202141347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mollie_subscription (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, state VARCHAR(255) NOT NULL, `interval` INT NOT NULL, created_at DATETIME NOT NULL, started_at DATETIME DEFAULT NULL, INDEX IDX_5E346303A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_subscription_payments (subscription_id INT NOT NULL, payment_id INT NOT NULL, INDEX IDX_4653AD099A1887DC (subscription_id), INDEX IDX_4653AD094C3A3BB (payment_id), PRIMARY KEY(subscription_id, payment_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mollie_subscription_product (id INT AUTO_INCREMENT NOT NULL, subscription_id INT DEFAULT NULL, product_id INT DEFAULT NULL, product_amount INT NOT NULL, INDEX IDX_8B1B805A9A1887DC (subscription_id), INDEX IDX_8B1B805A4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mollie_subscription ADD CONSTRAINT FK_5E346303A76ED395 FOREIGN KEY (user_id) REFERENCES sylius_shop_user (id)');
        $this->addSql('ALTER TABLE mollie_subscription_payments ADD CONSTRAINT FK_4653AD099A1887DC FOREIGN KEY (subscription_id) REFERENCES mollie_subscription (id)');
        $this->addSql('ALTER TABLE mollie_subscription_payments ADD CONSTRAINT FK_4653AD094C3A3BB FOREIGN KEY (payment_id) REFERENCES sylius_payment (id)');
        $this->addSql('ALTER TABLE mollie_subscription_product ADD CONSTRAINT FK_8B1B805A9A1887DC FOREIGN KEY (subscription_id) REFERENCES mollie_subscription (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mollie_subscription_product ADD CONSTRAINT FK_8B1B805A4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mollie_subscription_payments DROP FOREIGN KEY FK_4653AD099A1887DC');
        $this->addSql('ALTER TABLE mollie_subscription_product DROP FOREIGN KEY FK_8B1B805A9A1887DC');
        $this->addSql('DROP TABLE mollie_subscription');
        $this->addSql('DROP TABLE mollie_subscription_payments');
        $this->addSql('DROP TABLE mollie_subscription_product');
    }
}
