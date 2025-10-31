<?php

namespace App\Controller;

use App\Entity\MonthlyBudget;
use App\Entity\User;
use App\Repository\MonthlyBudgetRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/monthly-budgets', name: 'monthly_budget_')]
class MonthlyBudgetController extends AbstractController
{
    private EntityManagerInterface $em;
    private MonthlyBudgetRepository $repo;

    public function __construct(EntityManagerInterface $em, MonthlyBudgetRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
    }

    #[OA\Get(
        path: '/api/monthly-budgets',
        summary: 'List all monthly budgets',
        tags: ['monthlyBudget'],
        responses: [
            new OA\Response(response: 200, description: 'List of monthly budgets')
        ]
    )]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $data = $this->repo->findAll();
        return $this->json($data, 200, [], ['groups' => 'monthly_budget:read']);
    }

    #[OA\Post(
        path: '/api/monthly-budgets',
        summary: 'Create a new monthly budget',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'application/json' => new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'month', type: 'string', example: '2025-11'),
                        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 1500.50)
                    ],
                    required: ['month', 'amount']
                )
            ]
        ),
        tags: ['monthlyBudget'],
        responses: [
            new OA\Response(response: 201, description: 'Budget created successfully'),
            new OA\Response(response: 400, description: 'Missing data or invalid input'),
            new OA\Response(response: 404, description: 'User not found')
        ]
    )]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!isset($payload['month'], $payload['amount'])) {
            return $this->json(['error' => 'Missing data'], 400);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        try {
            $month = preg_match('/^\d{4}-\d{2}$/', $payload['month'])
                ? \DateTime::createFromFormat('Y-m', $payload['month'])
                : new \DateTime($payload['month']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid month format'], 400);
        }

        $firstOfMonth = new \DateTimeImmutable($month->format('Y-m-01'));

        $existingBudget = $this->em->getRepository(MonthlyBudget::class)
            ->findOneBy(['user' => $user, 'month' => $firstOfMonth]);

        if ($existingBudget) {
            return $this->json($existingBudget, 201, [], ['groups' => 'monthly_budget:read']);
        }

        $budget = new MonthlyBudget();
        $budget->setUser($user)
            ->setMonth($firstOfMonth)
            ->setAmount((float)$payload['amount']);

        $this->em->persist($budget);
        $this->em->flush();

        return $this->json($budget, 201, [], ['groups' => 'monthly_budget:read']);
    }

    #[OA\Get(
        path: '/api/monthly-budgets/{id}',
        summary: 'Get a single monthly budget',
        tags: ['monthlyBudget'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID of the monthly budget')
        ],
        responses: [
            new OA\Response(response: 200, description: 'Budget found'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Budget not found')
        ]
    )]
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('view', subject: 'budget')]
    public function show(MonthlyBudget $budget): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $budget);
        return $this->json($budget, 200, [], ['groups' => 'monthly_budget:read']);
    }

    #[OA\Put(
        path: '/api/monthly-budgets/{id}',
        summary: 'Update monthly budget amount',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'application/json' => new OA\JsonContent(
                    required: ['amount'],
                    properties: [
                        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 2000.50)
                    ],
                    type: 'object'
                )
            ]
        ),
        tags: ['monthlyBudget'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID of the monthly budget')
        ],
        responses: [
            new OA\Response(response: 200, description: 'Budget updated successfully'),
            new OA\Response(response: 400, description: 'Invalid input or duplicate budget'),
            new OA\Response(response: 404, description: 'Budget not found')
        ]
    )]
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, MonthlyBudget $budget): JsonResponse
    {
        if (!$this->isGranted('edit', $budget)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $payload = json_decode($request->getContent(), true);

        if (isset($payload['year'], $payload['month'])) {
            $budget->setMonth(sprintf('%04d-%02d', $payload['year'], $payload['month']));
        }

        if (isset($payload['amount'])) {
            $budget->setAmount((float)$payload['amount']);
        }

        if (isset($payload['userId'])) {
            $user = $this->em->getRepository(User::class)->find($payload['userId']);
            if (!$user) {
                return $this->json(['error' => 'User not found'], 404);
            }
            $budget->setUser($user);
        }

        $this->em->flush();

        return $this->json($budget, 200, [], ['groups' => 'monthly_budget:read']);
    }

    #[OA\Delete(
        path: '/api/monthly-budgets/{id}',
        summary: 'Delete a monthly budget',
        tags: ['monthlyBudget'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID of the monthly budget')
        ],
        responses: [
            new OA\Response(response: 200, description: 'Deleted successfully'),
            new OA\Response(response: 404, description: 'Budget not found')
        ]
    )]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $budget = $this->repo->find($id);
        if (!$budget) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $this->em->remove($budget);
        $this->em->flush();

        return $this->json(['success' => true]);
    }
}
