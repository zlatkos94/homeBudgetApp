<?php

namespace App\Entity;

use App\Repository\MonthlyBudgetRepository;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: "monthly_budget")]
#[ORM\UniqueConstraint(name: "user_month_unique", columns: ["user_id", "month"])]
#[ORM\Entity(repositoryClass: MonthlyBudgetRepository::class)]
class MonthlyBudget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['monthly_budget:read'])]
    private int $id;

    #[ORM\Column(type: "date")]
    #[Groups(['monthly_budget:read', 'monthly_budget:write'])]
    private \DateTimeInterface $month;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Groups(['monthly_budget:read', 'monthly_budget:write'])]
    private float $amount;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMonth(): string
    {
        return $this->month->format('Y-m-d');
    }

    public function setMonth(\DateTimeInterface $month): self
    {
        $this->month = Carbon::instance($month)->startOfMonth();
        return $this;
    }


    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getFormattedMonth(): string
    {
        return $this->month->format('Y-m');
    }
}
