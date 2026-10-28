<?php

namespace App\Controller;

use App\Application\User\RegisterUser\RegisterUserHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        RegisterUserHandler $handler,
    ): JsonResponse {
        try {
            $data = json_decode(
                $request->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        if (!is_array($data) || array_is_list($data)) {
            throw new BadRequestHttpException('JSON body must be an object');
        }

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (
            !is_string($email) ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !is_string($password) ||
            strlen($password) < 12
        ) {
            throw new UnprocessableEntityHttpException(
                'Valid email and password of at least 12 characters are required'
            );
        }

        $user = $handler($email, $password);

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
        ], Response::HTTP_CREATED);
    }
}
