<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Expense;
use App\Entity\MonthlyBudget;
use App\Entity\User;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword("password");
        $manager->persist($user);

        $category = new Category();
        $category->setName('Groceries')->setUser($user);
        $manager->persist($category);

        $budget = new MonthlyBudget();
        $budget->setUser($user)
            ->setMonth(Carbon::now())
            ->setAmount(1000);
        $manager->persist($budget);

        $expensesData = [
            ['title' => 'Supermarket shopping', 'amount' => 50.25],
            ['title' => 'Electricity bill', 'amount' => 75.00],
            ['title' => 'Cinema', 'amount' => 20.00],
        ];

        foreach ($expensesData as $data) {
            $expense = new Expense();
            $expense->setTitle($data['title'])
                ->setAmount($data['amount'])
                ->setSpentAt(Carbon::now())
                ->setCategory($category)
                ->setUser($user);
            $manager->persist($expense);
        }

        $manager->flush();
    }
}

