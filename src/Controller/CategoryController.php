<?php

namespace App\Controller;

use App\Entity\Category;
use App\Services\CategoryService;
use App\Services\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/categories')]
class CategoryController extends AbstractController
{
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private CategoryService $categoryService;
    private ValidatorService $validator;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, CategoryService $categoryService, ValidatorService $validator)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->categoryService = $categoryService;
        $this->validator = $validator;
    }

    #[OA\Get(
        path: '/api/categories',
        summary: 'List all categories for the current user',
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 200, description: 'List of categories')
        ]
    )]
    #[Route('', name: 'category_list', methods: ['GET'])]
    #[IsGranted('view')]
    public function list(): JsonResponse
    {
        $user = $this->getUser();
        $categories = $this->em->getRepository(Category::class)->findBy(['user' => $user]);

        $json = $this->serializer->serialize($categories, 'json', ['groups' => ['category']]);
        return new JsonResponse($json, 200, [], true);
    }

    #[OA\Post(
        path: '/api/categories',
        summary: 'Create a new category',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'application/json' => new OA\JsonContent(
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Food')
                    ],
                    type: 'object'
                )
            ]
        ),
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 201, description: 'Category created'),
            new OA\Response(response: 400, description: 'Invalid input')
        ]
    )]
    #[Route('', name: 'category_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            /** @var Category $category */
            $category = $this->serializer->deserialize(
                $request->getContent(),
                Category::class,
                'json'
            );
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid JSON or fields'], 400);
        }

        $category->setUser($this->getUser());

        $errors = $this->validator->validateEntity($category);

        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($category);
        $this->em->flush();

        $json = $this->serializer->serialize($category, 'json', ['groups' => ['category']]);
        return new JsonResponse($json, 201, [], true);
    }

    #[OA\Delete(
        path: '/api/categories/{id}',
        summary: 'Delete a category',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Category ID to delete',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Category not found')
        ]
    )]
    #[Route('/{id}', name: 'category_delete', methods: ['DELETE'])]
    #[IsGranted('delete', 'category')]
    public function delete(Category $category): JsonResponse
    {
        $this->denyAccessUnlessGranted('delete', $category);

        $this->em->remove($category);
        $this->em->flush();

        return $this->json(['message' => 'Category deleted']);
    }
}
