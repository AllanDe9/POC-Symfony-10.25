<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Authentication')]
final class AuthController extends AbstractController
{
    #[Route('/api/login_check', name: 'api_login_check_doc', methods: ['POST'])]
    #[OA\Post(
        summary: 'Authentification JWT',
        description: 'Permet de s\'authentifier et d\'obtenir un token JWT',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token JWT généré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Identifiants invalides')
        ]
    )]
    public function login(): JsonResponse
    {
        // Cette méthode ne sera jamais appelée car interceptée par le firewall Lexik JWT
        return new JsonResponse(['message' => 'This endpoint is handled by LexikJWTAuthenticationBundle']);
    }
}
