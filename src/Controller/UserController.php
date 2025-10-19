<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use OpenApi\Attributes as OA;

final class UserController extends AbstractController
{
    #[Route('/api/v1/user/list', name: 'users', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/user/list',
        summary: 'Liste tous les utilisateurs',
        security: [['Bearer' => []]],
        tags: ['Utilisateurs']
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
        description: 'Liste des utilisateurs récupérée avec succès'
    )]
    #[OA\Response(
        response: 403,
        description: 'Accès refusé - ROLE_ADMIN requis'
    )]
    public function getUsers(Request $request, UserRepository $userRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $usersList = $userRepository->findAllWithPagination($page, $limit);

        return $this->json($usersList, Response::HTTP_OK);
    }

    #[Route('/api/v1/user/{id}', name: 'user', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/user/{id}',
        summary: 'Récupère un utilisateur par son ID',
        security: [['Bearer' => []]],
        tags: ['Utilisateurs']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Utilisateur récupéré avec succès'
    )]
    #[OA\Response(
        response: 403,
        description: 'Accès refusé - ROLE_ADMIN requis'
    )]
    #[OA\Response(
        response: 404,
        description: 'Utilisateur non trouvé'
    )]
    public function getUserById(User $user): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($user, Response::HTTP_OK);
    }

    #[Route('/api/v1/user/add', name: 'user_add', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/user/add',
        summary: 'Crée un nouvel utilisateur',
        security: [['Bearer' => []]],
        tags: ['Utilisateurs']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'newuser@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password123'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                new OA\Property(property: 'subscriptionToNewsletter', type: 'boolean', example: false)
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Utilisateur créé avec succès'
    )]
    #[OA\Response(
        response: 400,
        description: 'Données invalides'
    )]
    #[OA\Response(
        response: 403,
        description: 'Accès refusé - ROLE_ADMIN requis'
    )]
    public function addUser(
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $entityManager, 
        UrlGeneratorInterface $urlGenerator, 
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $plainPassword = $data['password'] ?? null;

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        // Hash the password
        if ($plainPassword) {
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $location = $urlGenerator->generate('user', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($user, Response::HTTP_CREATED, ['Location' => $location]);
    }

    #[Route('/api/v1/user/{id}', name: 'user_update', requirements: ['id' => Requirement::DIGITS], methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/user/{id}',
        summary: 'Met à jour un utilisateur existant',
        security: [['Bearer' => []]],
        tags: ['Utilisateurs']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'updated@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'newpassword123'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_ADMIN']),
                new OA\Property(property: 'subscriptionToNewsletter', type: 'boolean', example: true)
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Utilisateur mis à jour avec succès',
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
        description: 'Utilisateur non trouvé'
    )]
    public function updateUser(
        Request $request, 
        User $currentUser, 
        SerializerInterface $serializer, 
        EntityManagerInterface $entityManager, 
        UrlGeneratorInterface $urlGenerator, 
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $plainPassword = $data['password'] ?? null;

        $user = $serializer->deserialize(
            $request->getContent(), 
            User::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
        );

        // Hash the password if provided
        if ($plainPassword) {
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $location = $urlGenerator->generate('user', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json(['status' => 'success'], Response::HTTP_OK, ['Location' => $location]);
    }

    #[Route('/api/v1/user/{id}', name: 'user_delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/user/{id}',
        summary: 'Supprime un utilisateur',
        security: [['Bearer' => []]],
        tags: ['Utilisateurs']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Utilisateur supprimé avec succès',
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
        description: 'Utilisateur non trouvé'
    )]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($user);
        $entityManager->flush();
        
        return $this->json(['status' => 'success'], Response::HTTP_OK);
    }
}
