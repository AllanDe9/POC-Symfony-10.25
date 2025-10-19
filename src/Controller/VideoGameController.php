<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Repository\VideoGameRepository;
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

final class VideoGameController extends AbstractController
{
    #[Route('/api/v1/video-game/list', name: 'video_games', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/video-game/list',
        summary: 'Liste tous les jeux vidéo',
        tags: ['Jeux Vidéo']
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
        description: 'Liste des jeux vidéo récupérée avec succès'
    )]
    public function getVideoGames(Request $request, VideoGameRepository $videoGameRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        $videoGamesList = $videoGameRepository->findAllWithPagination($page, $limit);

       return $this->json($videoGamesList, Response::HTTP_OK, [], ['groups' => ['videoGame:read']]);
    }

    #[Route('/api/v1/video-game/{id}', name: 'video_game', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/video-game/{id}',
        summary: 'Récupère un jeu vidéo par son ID',
        tags: ['Jeux Vidéo']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID du jeu vidéo',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Jeu vidéo récupéré avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Jeu vidéo non trouvé'
    )]
    public function getVideoGame(VideoGame $videoGame): JsonResponse
    {
        return $this->json($videoGame, Response::HTTP_OK, [], ['groups' => 'videoGame:read']);
    }

    #[Route('/api/v1/video-game/add', name: 'video_game_add', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/video-game/add',
        summary: 'Crée un nouveau jeu vidéo',
        security: [['Bearer' => []]],
        tags: ['Jeux Vidéo']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['title', 'releaseDate'],
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'The Legend of Zelda'),
                new OA\Property(property: 'releaseDate', type: 'string', format: 'date', example: '2025-01-15'),
                new OA\Property(property: 'description', type: 'string', example: 'Un jeu d\'aventure épique')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Jeu vidéo créé avec succès'
    )]
    #[OA\Response(
        response: 400,
        description: 'Données invalides'
    )]
    #[OA\Response(
        response: 403,
        description: 'Accès refusé - ROLE_ADMIN requis'
    )]
    public function addVideoGame(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $videoGame = $serializer->deserialize($request->getContent(), VideoGame::class, 'json');
        $entityManager->persist($videoGame);
        $entityManager->flush();

        $location = $urlGenerator->generate('video_game', ['id' => $videoGame->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $errors = $validator->validate($videoGame);

        if ($errors->count() > 0) {

            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        return $this->json($videoGame, Response::HTTP_CREATED, ['Location' => $location], ['groups' => 'getVideoGame']);
    }

    #[Route('/api/v1/video-game/{id}', name: 'video_game_update', requirements: ['id' => Requirement::DIGITS], methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/video-game/{id}',
        summary: 'Met à jour un jeu vidéo existant',
        security: [['Bearer' => []]],
        tags: ['Jeux Vidéo']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID du jeu vidéo',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'The Legend of Zelda'),
                new OA\Property(property: 'releaseDate', type: 'string', format: 'date', example: '2025-01-15'),
                new OA\Property(property: 'description', type: 'string', example: 'Un jeu d\'aventure épique')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Jeu vidéo mis à jour avec succès',
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
        description: 'Jeu vidéo non trouvé'
    )]
    public function updateVideoGame(Request $request, VideoGame $currentVideoGame, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $videoGame = $serializer->deserialize($request->getContent(), VideoGame::class, 'json', ['AbstractNormalizer::OBJECT_TO_POPULATE' => $currentVideoGame]);
        $entityManager->persist($videoGame);
        $entityManager->flush();

        $location = $urlGenerator->generate('video_game', ['id' => $videoGame->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $errors = $validator->validate($videoGame);

        if ($errors->count() > 0) {

            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'success'], Response::HTTP_OK, ['Location' => $location]);
    }

    #[Route('/api/v1/video-game/{id}', name: 'video_game_delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/video-game/{id}',
        summary: 'Supprime un jeu vidéo',
        security: [['Bearer' => []]],
        tags: ['Jeux Vidéo']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID du jeu vidéo',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Jeu vidéo supprimé avec succès',
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
        description: 'Jeu vidéo non trouvé'
    )]
    public function deleteVideoGame(VideoGame $videoGame, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($videoGame);
        $entityManager->flush();
        return $this->json(['status' => 'success'], Response::HTTP_OK);
    }
}