<?php

namespace App\Tests\Service;

use App\Repository\ExpenseRepository;
use App\Repository\MonthlyBudgetRepository;
use App\Services\AnalyticsService;
use PHPUnit\Framework\TestCase;
use App\Entity\User;
use App\Entity\Expense;
use App\Entity\MonthlyBudget;
use App\DTO\MonthlyAnalyticsResult;
use App\Search\SearchRequestInterface;
use DateTime;

class AnalyticsServiceTest extends TestCase
{
    public function testGetMonthlyAnalytics(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $searchRequest = $this->createMock(SearchRequestInterface::class);
        $filters = [];

        $expense1 = new Expense();
        $expense1->setAmount(50.0);
        $expense1->setSpentAt(new DateTime('2025-10-01'));

        $expense2 = new Expense();
        $expense2->setAmount(25.0);
        $expense2->setSpentAt(new DateTime('2025-10-15'));

        $expenseRepository = $this->createMock(ExpenseRepository::class);
        $expenseRepository->expects($this->once())
            ->method('findByUserWithFilters')
            ->with($user->getId(), $searchRequest, $filters)
            ->willReturn([$expense1, $expense2]);

        $budget = $this->createMock(MonthlyBudget::class);
        $budget->method('getAmount')->willReturn(200.0);
        $budget->method('getFormattedMonth')->willReturn('2025-10');

        $monthlyBudgetRepository = $this->createMock(MonthlyBudgetRepository::class);
        $monthlyBudgetRepository->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn([$budget]);

        $service = new AnalyticsService($expenseRepository, $monthlyBudgetRepository);

        $result = $service->getMonthlyAnalytics($user, $searchRequest, $filters);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(MonthlyAnalyticsResult::class, $result[0]);
        $this->assertEquals('2025-10', $result[0]->getMonth());
        $this->assertEquals(200.0, $result[0]->getBudget());
        $this->assertEquals(75.0, $result[0]->getSpent());
        $this->assertEquals(37.5, $result[0]->getPercentUsed());
        $this->assertCount(2, $result[0]->getExpenses());

    }
}
