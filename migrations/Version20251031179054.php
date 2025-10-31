<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251031179054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes for Expense and MonthlyBudget tables for faster queries';
    }

    public function up(Schema $schema): void
    {
        // Expense table indexes
        $this->addSql('CREATE INDEX idx_expense_user ON expense(user_id)');
        $this->addSql('CREATE INDEX idx_expense_category ON expense(category_id)');
        $this->addSql('CREATE INDEX idx_expense_user_spentAt_amount ON expense(user_id, spent_at, amount)');
        $this->addSql('CREATE INDEX idx_monthly_budget_user ON monthly_budget(user_id)');
        $this->addSql('CREATE INDEX idx_monthly_budget_user_month ON monthly_budget(user_id, month)');
    }

    public function down(Schema $schema): void
    {
        // Drop Expense indexes
        $this->addSql('DROP INDEX idx_expense_user ON expense');
        $this->addSql('DROP INDEX idx_expense_category ON expense');
        $this->addSql('DROP INDEX idx_expense_user_spentAt_amount ON expense');
        $this->addSql('DROP INDEX idx_monthly_budget_user ON monthly_budget');
        $this->addSql('DROP INDEX idx_monthly_budget_user_month ON monthly_budget');
    }
}
