<?php

namespace App\Entity;

use App\Repository\ExpenseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
#[ORM\Table(name: "expense")]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['analytics'])]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    #[Groups(['analytics', 'expense'])]
    private string $title;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Groups(['analytics', 'expense'])]
    private float $amount;

    #[ORM\Column(type: "datetime")]
    #[Groups(['analytics', 'expense'])]
    private \DateTimeInterface $spentAt;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: "expenses")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['analytics', 'expense', 'category'])]
    private Category $category;


    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
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

    public function getSpentAt(): \DateTimeInterface
    {
        return $this->spentAt;
    }

    public function setSpentAt(\DateTimeInterface $spentAt): self
    {
        $this->spentAt = $spentAt;
        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;
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
}
