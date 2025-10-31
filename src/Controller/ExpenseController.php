<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\Category;
use App\Entity\MonthlyBudget;
use App\Repository\ExpenseRepository;
use App\Repository\MonthlyBudgetRepository;
use App\Search\SearchRequest;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[Route('/api/expenses')]
class ExpenseController extends AbstractController
{
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private AnalyticsService $analyticsService;
    private MonthlyBudgetRepository $monthlyBudgetRepository;


    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, AnalyticsService $analyticsService, MonthlyBudgetRepository $monthlyBudgetRepository)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->analyticsService = $analyticsService;
        $this->monthlyBudgetRepository = $monthlyBudgetRepository;
    }

    #[OA\Get(
        path: '/api/expenses',
        summary: 'List all expenses for the current user with optional filters, search and sorting',
        tags: ['Expense'],
        parameters: [
            new OA\Parameter(name: 'category', description: 'Category ID', in: 'query', required: false),
            new OA\Parameter(name: 'minAmount', description: 'Minimum amount', in: 'query', required: false),
            new OA\Parameter(name: 'maxAmount', description: 'Maximum amount', in: 'query', required: false),
            new OA\Parameter(name: 'fromDate', description: 'From date YYYY-MM-DD', in: 'query', required: false),
            new OA\Parameter(name: 'toDate', description: 'To date YYYY-MM-DD', in: 'query', required: false),
            new OA\Parameter(name: 'sort', description: 'Field to sort by, e.g. title or name', in: 'query', required: false),
            new OA\Parameter(name: 'order', in: 'query', required: false, description: 'Sort direction ASC or DESC'),
            new OA\Parameter(name: 'like', description: 'Search term for expense title or category name', in: 'query', required: false),
            new OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Maximum number of results'),
            new OA\Parameter(name: 'position', in: 'query', required: false, description: 'Offset for pagination')
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of expenses')
        ]
    )]
    #[Route('', name: 'expense_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $filters = $request->query->all();
        $searchRequest = new SearchRequest($request);

        $result = $this->analyticsService->getMonthlyAnalytics($user, $searchRequest, $filters);
        $json = $this->serializer->serialize($result, 'json', ['groups' => ['expense', 'category', 'analytics']]);
        return new JsonResponse($json, 200, [], true);
    }

    #[OA\Post(
        path: '/api/expenses',
        summary: 'Create a new expense',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'application/json' => new OA\JsonContent(
                    required: ['title', 'amount', 'spentAt', 'categoryId'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Groceries'),
                        new OA\Property(property: 'amount', type: 'number', example: 100.50),
                        new OA\Property(property: 'spentAt', type: 'string', example: '2025-10-30T12:00:00'),
                        new OA\Property(property: 'categoryId', type: 'integer', example: 1)
                    ],
                    type: 'object'
                )
            ]
        ),
        tags: ['Expense'],
        responses: [
            new OA\Response(response: 201, description: 'Expense created'),
            new OA\Response(response: 400, description: 'Invalid data')
        ]
    )]
    #[Route('', name: 'expense_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['amount'], $data['spentAt'], $data['categoryId'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $category = $this->em->getRepository(Category::class)->find($data['categoryId']);
        if (!$category || $category->getUser() !== $user) {
            return $this->json(['error' => 'Invalid category'], 400);
        }

        $expense = new Expense();
        $expense->setTitle($data['title'])
            ->setAmount((float) $data['amount'])
            ->setSpentAt(new \DateTime($data['spentAt']))
            ->setCategory($category)
            ->setUser($user);

        $this->em->persist($expense);

        $monthStart = Carbon::instance($expense->getSpentAt())->startOfMonth();

        $budget = $this->monthlyBudgetRepository->findOneBy([
            'user' => $user,
            'month' => $monthStart,
        ]);

        if (!$budget) {
            $budget = new MonthlyBudget();
            $budget->setUser($user)
                ->setMonth($monthStart)
                ->setAmount(0);

            $this->em->persist($budget);
        }

        $this->em->flush();

        $json = $this->serializer->serialize($expense, 'json', ['groups' => ['expense', 'category']]);
        return new JsonResponse($json, 201, [], true);
    }

    #[OA\Put(
        path: '/api/expenses/{id}',
        summary: 'Update an existing expense',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'application/json' => new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Groceries'),
                        new OA\Property(property: 'amount', type: 'number', example: 100.50),
                        new OA\Property(property: 'spentAt', type: 'string', example: '2025-10-30T12:00:00'),
                        new OA\Property(property: 'categoryId', type: 'integer', example: 1)
                    ],
                    type: 'object'
                )
            ]
        ),
        tags: ['Expense'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Expense ID')
        ],
        responses: [
            new OA\Response(response: 200, description: 'Expense updated'),
            new OA\Response(response: 400, description: 'Invalid data'),
            new OA\Response(response: 403, description: 'Forbidden')
        ]
    )]
    #[Route('/{id}', name: 'expense_update', methods: ['PUT'])]
    public function update(Expense $expense, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if ($expense->getUser() !== $user) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $expense->setTitle($data['title']);
        }

        if (isset($data['amount'])) {
            $expense->setAmount((float) $data['amount']);
        }

        if (isset($data['spentAt'])) {
            $expense->setSpentAt(new \DateTime($data['spentAt']));
        }

        if (isset($data['categoryId'])) {
            $category = $this->em->getRepository(Category::class)->find($data['categoryId']);
            if (!$category || $category->getUser() !== $user) {
                return $this->json(['error' => 'Invalid category'], 400);
            }
            $expense->setCategory($category);
        }

        $this->em->flush();

        $json = $this->serializer->serialize($expense, 'json', ['groups' => ['expense', 'category']]);
        return new JsonResponse($json, 200, [], true);
    }

    #[OA\Delete(
        path: '/api/expenses/{id}',
        summary: 'Delete an expense',
        tags: ['Expense'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Expense ID')
        ],
        responses: [
            new OA\Response(response: 200, description: 'Expense deleted'),
            new OA\Response(response: 403, description: 'Forbidden')
        ]
    )]
    #[Route('/{id}', name: 'expense_delete', methods: ['DELETE'])]
    public function delete(Expense $expense): JsonResponse
    {
        $user = $this->getUser();
        if ($expense->getUser() !== $user) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $this->em->remove($expense);
        $this->em->flush();

        return $this->json(['message' => 'Expense deleted']);
    }
}
