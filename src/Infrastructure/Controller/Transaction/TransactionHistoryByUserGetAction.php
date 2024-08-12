<?php

namespace App\Infrastructure\Controller\Transaction;

use App\Infrastructure\Services\TransactionService;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionHistoryByUserGetAction extends AbstractController
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    #[Route('/{user}/transaction', name: 'api_transaction_by_user', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get the current balance of a user',
        security: [['Authorization' => []]],
        tags: ['transaction'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the transaction history by the user',
                content: new OA\JsonContent(
                    type: 'object',
                    example: '[
                        {
                            "value": 100,
                            "payer": "kleyton",
                            "payee": "john",
                            "created_at": {
                                "date": "2024-08-11 19:55:51.000000",
                                "timezone_type": 3,
                                "timezone": "UTC"
                            }
                        }
                    ]'
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
            new OA\Response(
                response: 404,
                description: 'Not Found',
                content: new OA\JsonContent(
                    type: 'object',
                    example: '{
                      "error": "No transaction history found"
                    }'
                )
            ),
        ],
    )]
    #[Security(name: 'Authorization')]
    #[OA\Tag('transaction')]
    public function __invoke(int $user): JsonResponse
    {
        $transactionHistoryData = $this->transactionService->getTransactionHistoryByUser($user);

        if (empty($transactionHistoryData)) {
            return new JsonResponse(
                ['error' => 'No transaction history found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse(
            $transactionHistoryData,
            Response::HTTP_OK
        );
    }
}
