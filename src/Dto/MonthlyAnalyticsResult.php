<?php

namespace App\Dto;

use App\Entity\Expense;
use Symfony\Component\Serializer\Annotation\Groups;

class MonthlyAnalyticsResult
{
    #[Groups(['analytics'])]
    private string $month;

    #[Groups(['analytics'])]
    private float $budget;

    #[Groups(['analytics'])]
    private float $spent;

    #[Groups(['analytics'])]
    private ?float $percentUsed;

    /** @var Expense[] */
    #[Groups(['analytics'])]
    private array $expenses;

    /**
     * @param Expense[] $expenses
     */
    public function __construct(string $month, float $budget, float $spent, ?float $percentUsed, array $expenses)
    {
        $this->month = $month;
        $this->budget = $budget;
        $this->spent = $spent;
        $this->percentUsed = $percentUsed;
        $this->expenses = $expenses;
    }

    public function getMonth(): string
    {
        return $this->month;
    }

    public function getBudget(): float
    {
        return $this->budget;
    }

    public function getSpent(): float
    {
        return $this->spent;
    }

    public function getPercentUsed(): ?float
    {
        return $this->percentUsed;
    }

    public function getExpenses(): array
    {
        return $this->expenses;
    }

    public function setExpenses(array $expenses): void
    {
        $this->expenses = $expenses;
    }
}