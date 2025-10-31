<?php

namespace App\Security\Voter;

use App\Entity\Expense;
use App\Entity\MonthlyBudget;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UniversalVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && (
                $subject instanceof MonthlyBudget ||
                $subject instanceof Expense ||
                $subject instanceof Category
            );
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match (true) {
            $subject instanceof MonthlyBudget => $this->voteMonthlyBudget($attribute, $subject, $user),
            $subject instanceof Expense => $this->voteExpenseEntity($attribute, $subject, $user),
            $subject instanceof Category => $this->voteCategory($attribute, $subject, $user),
            default => false,
        };
    }

    private function voteMonthlyBudget(string $attribute, MonthlyBudget $budget, User $user): bool
    {
        return $budget->getUser()->getId() === $user->getId();
    }

    private function voteExpenseEntity(string $attribute, Expense $expense, User $user): bool
    {
        return match ($attribute) {
            self::VIEW => true,
            self::EDIT, self::DELETE => $expense->getUser()->getId() === $user->getId(),
            default => false,
        };
    }

    private function voteCategory(string $attribute, Category $category, User $user): bool
    {
        return match ($attribute) {
            self::VIEW => true,
            self::EDIT, self::DELETE => $category->getUser()->getId() === $user->getId(),
            default => false,
        };
    }
}
