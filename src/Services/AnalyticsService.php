<?php

namespace App\Services;

use App\Dto\MonthlyAnalyticsResult;
use App\Entity\Expense;
use App\Entity\User;
use App\Repository\ExpenseRepository;
use App\Repository\MonthlyBudgetRepository;

class AnalyticsService
{
    public function __construct(
        private readonly ExpenseRepository $expenseRepository,
        private readonly MonthlyBudgetRepository $monthlyBudgetRepository,
    ) {}

    public function getMonthlyAnalytics(User $user, $searchRequest, $filters): array
    {
        $expenses = $this->expenseRepository->findByUserWithFilters(
            $user->getId(),
            $searchRequest,
            $filters
        );

        $budgets = $this->monthlyBudgetRepository->findByUser($user);

        $budgetByMonth = [];
        foreach ($budgets as $budget) {
            $monthKey = $budget->getFormattedMonth();
            $budgetByMonth[$monthKey] = $budget->getAmount();
        }

        $expensesByMonth = [];
        foreach ($expenses as $expense) {
            /** @var Expense $expense */
            $monthKey = $expense->getSpentAt()->format('Y-m');
            $expensesByMonth[$monthKey][] = $expense;
        }

        $result = [];
        foreach ($expensesByMonth as $month => $monthExpenses) {
            $totalSpent = array_reduce($monthExpenses, fn($carry, $e) => $carry + $e->getAmount(), 0);
            $budget = $budgetByMonth[$month] ?? 0;
            $percentUsed = $budget > 0 ? ($totalSpent / $budget) * 100 : null;

            $result[] = new MonthlyAnalyticsResult(
                $month,
                $budget,
                $totalSpent,
                $percentUsed,
                $monthExpenses
            );
        }

        return $result;
    }
}
