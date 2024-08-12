<?php

namespace App\Infrastructure\Controller\Balance;

use App\Domain\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BalanceByUserGetAction extends AbstractController
{
    #[Route('/{user}/balance', name: 'api_user_balance', methods: 'GET')]
    #[OA\Get(
        summary: 'Get the current balance of a user',
        security: [['Authorization' => []]],
        tags: ['transaction'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the actual balance of a user',
                content: new OA\JsonContent(
                    type: 'object',
                    example: '{"balance": 0}'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    type: 'object',
                    example: '{"error": "Unauthorized"}'
                )
            ),
        ],
    )]
    #[Security(name: 'Authorization')]
    public function __invoke(User $user): JsonResponse
    {
        return $this->json([
            'balance' => $user->getWallet()->getBalance(),
        ], Response::HTTP_OK);
    }
}
