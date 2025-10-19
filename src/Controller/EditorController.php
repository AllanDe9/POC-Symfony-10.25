<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Entity\Editor;
use App\Repository\VideoGameRepository;
use App\Repository\EditorRepository;
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

final class EditorController extends AbstractController
{
    #[Route('/api/v1/editor/list', name: 'editors', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/editor/list',
        summary: 'Liste tous les éditeurs',
        tags: ['Éditeurs']
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
        description: 'Liste des éditeurs récupérée avec succès'
    )]
    public function getEditors(Request $request, EditorRepository $editorRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $editorsList = $editorRepository->findAllWithPagination($page, $limit);

        return $this->json($editorsList, Response::HTTP_OK, [], ['groups' => ['editor:read']]);
    }

    #[Route('/api/v1/editor/{id}', name: 'editor', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/editor/{id}',
        summary: 'Récupère un éditeur par son ID',
        tags: ['Éditeurs']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'éditeur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Éditeur récupéré avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Éditeur non trouvé'
    )]
    public function getEditor(Editor $editor): JsonResponse
    {
        return $this->json($editor, Response::HTTP_OK, [], ['groups' => 'editor:read']);
    }

    #[Route('/api/v1/editor/add', name: 'editor_add', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/editor/add',
        summary: 'Crée un nouvel éditeur',
        security: [['Bearer' => []]],
        tags: ['Éditeurs']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                new OA\Property(property: 'country', type: 'string', example: 'Japon')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Éditeur créé avec succès'
    )]
    #[OA\Response(
        response: 400,
        description: 'Données invalides'
    )]
    #[OA\Response(
        response: 403,
        description: 'Accès refusé - ROLE_ADMIN requis'
    )]
    public function addEditor(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $editor = $serializer->deserialize($request->getContent(), Editor::class, 'json');
        $entityManager->persist($editor);
        $entityManager->flush();

        $location = $urlGenerator->generate('editor', ['id' => $editor->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $errors = $validator->validate($editor);

        if ($errors->count() > 0) {

            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        return $this->json($editor, Response::HTTP_CREATED, ['Location' => $location], ['groups' => 'getEditor']);
    }

    #[Route('/api/v1/editor/{id}', name: 'editor_update', requirements: ['id' => Requirement::DIGITS], methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/editor/{id}',
        summary: 'Met à jour un éditeur existant',
        security: [['Bearer' => []]],
        tags: ['Éditeurs']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'éditeur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                new OA\Property(property: 'country', type: 'string', example: 'Japon')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Éditeur mis à jour avec succès',
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
        description: 'Éditeur non trouvé'
    )]
    public function updateEditor(Request $request, Editor $currentEditor, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $editor = $serializer->deserialize($request->getContent(), Editor::class, 'json', ['AbstractNormalizer::OBJECT_TO_POPULATE' => $currentEditor]);
        $entityManager->persist($editor);
        $entityManager->flush();

        $location = $urlGenerator->generate('editor', ['id' => $editor->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $errors = $validator->validate($editor);

        if ($errors->count() > 0) {

            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'success'], Response::HTTP_OK, ['Location' => $location]);
    }

    #[Route('/api/v1/editor/{id}', name: 'editor_delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/editor/{id}',
        summary: 'Supprime un éditeur',
        security: [['Bearer' => []]],
        tags: ['Éditeurs']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'éditeur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Éditeur supprimé avec succès',
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
        description: 'Éditeur non trouvé'
    )]
    public function deleteEditor(Editor $editor, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['status' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($editor);
        $entityManager->flush();
        return $this->json(['status' => 'success'], Response::HTTP_OK);
    }
}