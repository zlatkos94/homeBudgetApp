<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\MonthlyBudget;
use App\Entity\User;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MonthlyBudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonthlyBudget::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findBudgetsWithExpenses(User $user, string $fromMonth, string $toMonth): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $from = (new \DateTime($fromMonth . '-01'))->format('Y-m-d');
        $to = (new \DateTime($toMonth . '-01'))->modify('last day of this month')->format('Y-m-d');

        $sql = '
        SELECT 
            b.id AS budget_id,
            b.month,
            b.amount AS budget,
            COALESCE(SUM(e.amount), 0) AS spent
        FROM monthly_budget b
        LEFT JOIN expense e 
            ON e.user_id = b.user_id
            AND e.spent_at >= DATE_FORMAT(b.month, "%Y-%m-01")
            AND e.spent_at <= LAST_DAY(b.month)
        WHERE b.user_id = :userId
          AND b.month BETWEEN :from AND :to
        GROUP BY b.id, b.month, b.amount
        ORDER BY b.month ASC
    ';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'userId' => $user->getId(),
            'from' => $from,
            'to' => $to,
        ]);

        return $result->fetchAllAssociative();
    }
}
