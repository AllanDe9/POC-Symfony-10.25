<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Repository\VideoGameRepository;
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

#[OA\Tag(name: 'Video Games')]
final class VideoGameController extends AbstractController
{
    #[Route('/api/v1/video-game/list', name: 'video_games', methods: ['GET'])]
    #[OA\Get(
        summary: 'Liste des jeux vidéo',
        description: 'Récupère la liste paginée des jeux vidéo',
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
    public function getVideoGames(Request $request, VideoGameRepository $videoGameRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        $videoGamesList = $videoGameRepository->findAllWithPagination($page, $limit);

       return $this->json($videoGamesList, Response::HTTP_OK, [], ['groups' => ['videoGame:read']]);
    }

    #[Route('/api/v1/video-game/{id}', name: 'video_game', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[OA\Get(
        summary: 'Détails d\'un jeu vidéo',
        description: 'Récupère les détails d\'un jeu vidéo par son ID',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID du jeu vidéo', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Jeu vidéo trouvé'),
            new OA\Response(response: 404, description: 'Jeu vidéo non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié')
        ]
    )]
    public function getVideoGame(VideoGame $videoGame): JsonResponse
    {
        return $this->json($videoGame, Response::HTTP_OK, [], ['groups' => 'videoGame:read']);
    }

    #[Route('/api/v1/video-game/add', name: 'video_game_add', methods: ['POST'])]
    #[OA\Post(
        summary: 'Créer un jeu vidéo',
        description: 'Crée un nouveau jeu vidéo (nécessite ROLE_ADMIN)',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données du jeu vidéo',
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'The Legend of Zelda'),
                    new OA\Property(property: 'description', type: 'string', example: 'Un jeu d\'aventure épique'),
                    new OA\Property(property: 'releaseDate', type: 'string', format: 'date', example: '1986-02-21')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Jeu vidéo créé'),
            new OA\Response(response: 400, description: 'Données invalides'),
            new OA\Response(response: 403, description: 'Accès interdit'),
            new OA\Response(response: 401, description: 'Non authentifié')
        ]
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
        summary: 'Modifier un jeu vidéo',
        description: 'Met à jour un jeu vidéo existant (nécessite ROLE_ADMIN)',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID du jeu vidéo', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Jeu vidéo mis à jour'),
            new OA\Response(response: 400, description: 'Données invalides'),
            new OA\Response(response: 403, description: 'Accès interdit'),
            new OA\Response(response: 404, description: 'Jeu vidéo non trouvé')
        ]
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
        summary: 'Supprimer un jeu vidéo',
        description: 'Supprime un jeu vidéo (nécessite ROLE_ADMIN)',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID du jeu vidéo', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Jeu vidéo supprimé'),
            new OA\Response(response: 403, description: 'Accès interdit'),
            new OA\Response(response: 404, description: 'Jeu vidéo non trouvé')
        ]
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