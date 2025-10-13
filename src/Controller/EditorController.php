<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Entity\Editor;
use App\Repository\VideoGameRepository;
use App\Repository\EditorRepository;
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

#[OA\Tag(name: 'Editors')]
final class EditorController extends AbstractController
{
    #[Route('/api/v1/editor/list', name: 'editors', methods: ['GET'])]
    #[OA\Get(
        summary: 'Liste des éditeurs',
        description: 'Récupère la liste paginée des éditeurs',
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
    public function getEditors(Request $request, EditorRepository $editorRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $editorsList = $editorRepository->findAllWithPagination($page, $limit);

        return $this->json($editorsList, Response::HTTP_OK, [], ['groups' => ['editor:read']]);
    }

    #[Route('/api/v1/editor/{id}', name: 'editor', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[OA\Get(
        summary: 'Détails d\'un éditeur',
        description: 'Récupère les détails d\'un éditeur par son ID',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'éditeur', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Éditeur trouvé'),
            new OA\Response(response: 404, description: 'Éditeur non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié')
        ]
    )]
    public function getEditor(Editor $editor): JsonResponse
    {
        return $this->json($editor, Response::HTTP_OK, [], ['groups' => 'editor:read']);
    }

    #[Route('/api/v1/editor/add', name: 'editor_add', methods: ['POST'])]
    #[OA\Post(
        summary: 'Créer un éditeur',
        description: 'Crée un nouvel éditeur (nécessite ROLE_ADMIN)',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                    new OA\Property(property: 'country', type: 'string', example: 'Japan')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Éditeur créé'),
            new OA\Response(response: 400, description: 'Données invalides'),
            new OA\Response(response: 403, description: 'Accès interdit')
        ]
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
        summary: 'Modifier un éditeur',
        description: 'Met à jour un éditeur existant (nécessite ROLE_ADMIN)',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'éditeur', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'country', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Éditeur mis à jour'),
            new OA\Response(response: 403, description: 'Accès interdit'),
            new OA\Response(response: 404, description: 'Éditeur non trouvé')
        ]
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
        summary: 'Supprimer un éditeur',
        description: 'Supprime un éditeur (nécessite ROLE_ADMIN)',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'éditeur', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Éditeur supprimé'),
            new OA\Response(response: 403, description: 'Accès interdit'),
            new OA\Response(response: 404, description: 'Éditeur non trouvé')
        ]
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