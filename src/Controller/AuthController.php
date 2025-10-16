<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/api/login_check', name: 'api_login_check_doc', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Cette méthode ne sera jamais appelée car interceptée par le firewall Lexik JWT
        return new JsonResponse(['message' => 'This endpoint is handled by LexikJWTAuthenticationBundle']);
    }
}
