<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20250327083742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove the unused subscription_id column from the sylius_order table because there is a unidirectional many-to-many relationship between mollie_subscription and sylius_order.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order DROP FOREIGN KEY FK_6196A1F99A1887DC');
        $this->addSql('DROP INDEX IDX_6196A1F99A1887DC ON sylius_order');
        $this->addSql('ALTER TABLE sylius_order DROP subscription_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order ADD subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F99A1887DC FOREIGN KEY (subscription_id) REFERENCES mollie_subscription (id) ON UPDATE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6196A1F99A1887DC ON sylius_order (subscription_id)');
    }
}
