<?php

namespace App\Controller;

use App\Repository\MonthlyBudgetRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/analytics', name: 'analytic')]
class AnalyticsController extends AbstractController
{
    private EntityManagerInterface $em;
    private MonthlyBudgetRepository $budgetRepository;

    public function __construct(MonthlyBudgetRepository $budgetRepository)
    {
        $this->budgetRepository = $budgetRepository;
    }

    #[OA\Get(
        path: '/api/analytics',
        summary: 'Compare two months',
        tags: ['analytic'],
        parameters: [
            new Parameter(
                name: 'from',
                description: 'Start month (format: YYYY-MM)',
                in: 'query',
                required: true,
                schema: new Schema(type: 'string', example: '2025-10')
            ),
            new Parameter(
                name: 'to',
                description: 'End month (format: YYYY-MM)',
                in: 'query',
                required: true,
                schema: new Schema(type: 'string', example: '2025-11')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Monthly comparison analytics')
        ]
    )]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $from = $request->query->get('from');
        $to = $request->query->get('to');

        if (!$from || !$to) {
            return $this->json(['error' => 'Both "from" and "to" query parameters are required'], 400);
        }

        $data = $this->budgetRepository->findBudgetsWithExpenses($user, $from, $to);

        return $this->json($data, 200);
    }
}
