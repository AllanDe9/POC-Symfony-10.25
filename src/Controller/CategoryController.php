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

final class CategoryController extends AbstractController
{
    #[Route('/api/v1/category/list', name: 'categories', methods: ['GET'])]
    public function getCategories(Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        $categoryList = $categoryRepository->findAllWithPagination($page, $limit);

       return $this->json($categoryList, Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }

    #[Route('/api/v1/category/{id}', name: 'category', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function getCategory(Category $category): JsonResponse
    {
        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('/api/v1/category/add', name: 'category_add', methods: ['POST'])]
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