<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Expense;
use App\Search\EntityPropertyTypeDetector;
use App\Search\SearchRequestInterface;
use AppBundle\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function findByUserWithFilters(int $userId, SearchRequestInterface $request, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.category', 'c')
            ->where('e.user = :user')
            ->setParameter('user', $userId);

        if (!empty($filters['category'])) {
            $qb->andWhere('e.category = :category')
                ->setParameter('category', $filters['category']);
        }

        if (!empty($filters['minAmount'])) {
            $qb->andWhere('e.amount >= :minAmount')
                ->setParameter('minAmount', $filters['minAmount']);
        }

        if (!empty($filters['maxAmount'])) {
            $qb->andWhere('e.amount <= :maxAmount')
                ->setParameter('maxAmount', $filters['maxAmount']);
        }

        if (!empty($filters['fromDate'])) {
            $qb->andWhere('e.spentAt >= :fromDate')
                ->setParameter('fromDate', new \DateTime($filters['fromDate']));
        }

        if (!empty($filters['toDate'])) {
            $qb->andWhere('e.spentAt <= :toDate')
                ->setParameter('toDate', new \DateTime($filters['toDate']));
        }

        $like = $request->getLike();

        if (!empty($like)) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('e.title', ':like'),
                    $qb->expr()->like('c.name', ':like')
                )
            )
                ->setParameter('like', '%' . $like . '%');
        }

        $orderBy = $request->getOrderBy();
        $direction = $request->getDirection();

        if ($orderBy) {
            $expenseProps = EntityPropertyTypeDetector::detect(Expense::class);
            $categoryProps = EntityPropertyTypeDetector::detect(Category::class);

            if (array_key_exists($orderBy, $expenseProps)) {
                $qb->orderBy('e.' . $orderBy, in_array($direction, ['ASC', 'DESC'], true) ? $direction : 'ASC');
            } elseif (array_key_exists($orderBy, $categoryProps)) {
                $qb->orderBy('c.' . $orderBy, in_array($direction, ['ASC', 'DESC'], true) ? $direction : 'ASC');
            }
        }

        $limit = $request->getLimit();
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        $position = $request->getPosition();
        if ($position !== null) {
            $qb->setFirstResult($position);
        }

        return $qb->getQuery()->getResult();
    }
}
