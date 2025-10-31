<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030179054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monthly_budget ADD CONSTRAINT user_month_unique UNIQUE (user_id, month)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monthly_budget DROP CONSTRAINT user_month_unique');
    }
}
