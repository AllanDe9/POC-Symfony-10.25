<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

final class CategoryController extends AbstractController
{
    #[Route('/api/v1/category/list', name: 'categories', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/category/list',
        summary: 'Liste toutes les catégories',
        tags: ['Catégories']
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Numéro de la page',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Nombre d\'éléments par page',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 3)
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des catégories récupérée avec succès',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Action')
                ]
            )
        )
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
        path: '/api/v1/category/{id}',
        summary: 'Récupère une catégorie par son ID',
        tags: ['Catégories']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de la catégorie',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Catégorie récupérée avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Action')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Catégorie non trouvée'
    )]
    public function getCategory(Category $category): JsonResponse
    {
        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('/api/v1/category/add', name: 'category_add', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/category/add',
        summary: 'Crée une nouvelle catégorie',
        security: [['Bearer' => []]],
        tags: ['Catégories']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'RPG', minLength: 2, maxLength: 100)
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Catégorie créée avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'RPG')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Données invalides'
    )]
    #[OA\Response(
        response: 403,
        description: 'Accès refusé - ROLE_ADMIN requis'
    )]
    public function addCategory(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
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
        path: '/api/v1/category/{id}',
        summary: 'Met à jour une catégorie existante',
        security: [['Bearer' => []]],
        tags: ['Catégories']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de la catégorie',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'RPG', minLength: 2, maxLength: 100)
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Catégorie mise à jour avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Données invalides'
    )]
    #[OA\Response(
        response: 403,
        description: 'Accès refusé - ROLE_ADMIN requis'
    )]
    #[OA\Response(
        response: 404,
        description: 'Catégorie non trouvée'
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
        path: '/api/v1/category/{id}',
        summary: 'Supprime une catégorie',
        security: [['Bearer' => []]],
        tags: ['Catégories']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de la catégorie',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Catégorie supprimée avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success')
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Accès refusé - ROLE_ADMIN requis'
    )]
    #[OA\Response(
        response: 404,
        description: 'Catégorie non trouvée'
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