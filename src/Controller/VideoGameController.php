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

final class VideoGameController extends AbstractController
{
    #[Route('/api/v1/video-game/list', name: 'video_games', methods: ['GET'])]
    public function getVideoGames(Request $request, VideoGameRepository $videoGameRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        $videoGamesList = $videoGameRepository->findAllWithPagination($page, $limit);

       return $this->json($videoGamesList, Response::HTTP_OK, [], ['groups' => ['videoGame:read']]);
    }

    #[Route('/api/v1/video-game/{id}', name: 'video_game', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function getVideoGame(VideoGame $videoGame): JsonResponse
    {
        return $this->json($videoGame, Response::HTTP_OK, [], ['groups' => 'videoGame:read']);
    }

    #[Route('/api/v1/video-game/add', name: 'video_game_add', methods: ['POST'])]
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