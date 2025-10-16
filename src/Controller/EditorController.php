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

final class EditorController extends AbstractController
{
    #[Route('/api/v1/editor/list', name: 'editors', methods: ['GET'])]
    public function getEditors(Request $request, EditorRepository $editorRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $editorsList = $editorRepository->findAllWithPagination($page, $limit);

        return $this->json($editorsList, Response::HTTP_OK, [], ['groups' => ['editor:read']]);
    }

    #[Route('/api/v1/editor/{id}', name: 'editor', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function getEditor(Editor $editor): JsonResponse
    {
        return $this->json($editor, Response::HTTP_OK, [], ['groups' => 'editor:read']);
    }

    #[Route('/api/v1/editor/add', name: 'editor_add', methods: ['POST'])]
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