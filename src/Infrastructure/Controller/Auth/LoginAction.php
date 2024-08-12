<?php

namespace App\Infrastructure\Controller\Auth;

use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

class LoginAction
{
    #[Route('/login_check', name: 'app_login', methods: ['POST'])]
    #[OA\Post(
        summary: 'Login check to return the auth bearer token',
        requestBody: new OA\RequestBody(
            description: 'Login check to return the auth bearer token',
            required: true,
            content: [
                new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: '{"username": "username@example.com", "password": "password"}',
                            summary: 'request example',
                            description: 'transaction ammount',
                        ),
                    ],
                    properties: [
                        new OA\Property(
                            property: 'username',
                            description: 'user email is request in username',
                            type: 'string',
                            example: 'johndoe@example.com',
                        ),
                        new OA\Property(
                            property: 'password',
                            description: 'user password',
                            type: 'string',
                            example: '12345678'
                        ),
                    ]
                ),
            ]
        ),
        tags: ['user'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the auth token',
                content: new OA\JsonContent(
                    type: 'object',
                    example: '{"token": "<token>"}'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    type: 'object',
                    example: '{
                      "code": 401,
                      "message": "Invalid credentials."
                    }'
                )
            ),
        ],
    )]
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }
}
