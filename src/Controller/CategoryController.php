<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'Categories')]
final class CategoryController extends AbstractController
{
    #[Route('/api/v1/category/list', name: 'categories', methods: ['GET'])]
    #[OA\Get(
        summary: 'Liste des catégories',
        description: 'Récupère la liste paginée des catégories',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Numéro de page', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Nombre d\'éléments par page', schema: new OA\Schema(type: 'integer', default: 3))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste récupérée avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié')
        ]
    )]
    public function getCategories(Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        $categoryList = $categoryRepository->findAllWithPagination($page, $limit);

       return $this->json($categoryList, Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }

    #[Route('/api/v1/category/{id}', name: 'category', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[OA\Get(
        summary: 'Détails d\'une catégorie',
        description: 'Récupère les détails d\'une catégorie par son ID',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la catégorie', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Catégorie trouvée'),
            new OA\Response(response: 404, description: 'Catégorie non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié')
        ]
    )]
    public function getCategory(Category $category): JsonResponse
    {
        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('/api/v1/category/add', name: 'category_add', methods: ['POST'])]
    #[OA\Post(
        summary: 'Créer une catégorie',
        description: 'Crée une nouvelle catégorie (nécessite ROLE_ADMIN)',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Action')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Catégorie créée'),
            new OA\Response(response: 400, description: 'Données invalides'),
            new OA\Response(response: 403, description: 'Accès interdit')
        ]
    )]
    public function addVideoGame(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');
        $entityManager->persist($category);
        $entityManager->flush();

        $location = $urlGenerator->generate('category', ['id' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $errors = $validator->validate($category);

        if ($errors->count() > 0) {

            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        return $this->json($category, Response::HTTP_CREATED, ['Location' => $location], ['groups' => 'category:read']);
    }

    #[Route('/api/v1/category/{id}', name: 'category_update', requirements: ['id' => Requirement::DIGITS], methods: ['PUT'])]
    #[OA\Put(
        summary: 'Modifier une catégorie',
        description: 'Met à jour une catégorie existante (nécessite ROLE_ADMIN)',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la catégorie', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Catégorie mise à jour'),
            new OA\Response(response: 403, description: 'Accès interdit'),
            new OA\Response(response: 404, description: 'Catégorie non trouvée')
        ]
    )]
    public function updateCategory(Request $request, Category $currentCategory, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $category = $serializer->deserialize($request->getContent(), Category::class, 'json', ['AbstractNormalizer::OBJECT_TO_POPULATE' => $currentCategory]);
        $entityManager->persist($category);
        $entityManager->flush();

        $location = $urlGenerator->generate('category', ['id' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $errors = $validator->validate($category);

        if ($errors->count() > 0) {

            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'success'], Response::HTTP_OK, ['Location' => $location]);
    }

    #[Route('/api/v1/category/{id}', name: 'category_delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Supprimer une catégorie',
        description: 'Supprime une catégorie (nécessite ROLE_ADMIN)',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la catégorie', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Catégorie supprimée'),
            new OA\Response(response: 403, description: 'Accès interdit'),
            new OA\Response(response: 404, description: 'Catégorie non trouvée')
        ]
    )]
    public function deleteCategory(Category $category, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($category);
        $entityManager->flush();
        return $this->json(['status' => 'success'], Response::HTTP_OK);
    }
}