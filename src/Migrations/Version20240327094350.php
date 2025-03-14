<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240327094350 extends AbstractMigration
{
    //table name
    private const TABLE_NAME = 'sylius_order';

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ' . self::TABLE_NAME . ' ADD qr_code TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ' . self::TABLE_NAME . ' DROP COLUMN qr_code');
    }
}
