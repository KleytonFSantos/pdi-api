<?php

namespace App\Infrastructure\Controller\Transaction;

use App\Domain\DTO\TransactionDTO;
use App\Domain\Exception\PayeeIsCommunException;
use App\Domain\Exception\PayerHasNotBalanceLimit;
use App\Domain\Exception\PayerIsNotCommunException;
use App\Infrastructure\Services\TransactionService;
use Doctrine\Persistence\ManagerRegistry;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TransactionPostAction extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly TransactionService $transactionService,
        private readonly ManagerRegistry $managerRegistry
    ) {
    }

    #[Route('/transaction', name: 'api_transaction', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new transaction',
        requestBody: new OA\RequestBody(
            description: 'Transaction data',
            required: true,
            content: [
                new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: '{"value":"300", "payee: "2"}',
                            summary: 'transaction data',
                            description: 'transaction data',
                        ),
                    ],
                    properties: [
                        new OA\Property(property: 'value', description: 'Transaction amount', type: 'float', example: 100.00),
                        new OA\Property(property: 'payee', description: 'Transaction payee', type: 'integer', example: 2),
                    ],
                ),
            ],
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Transaction created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Transaction created successfully'),
                    ],
                    type: 'object',
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request due to invalid input',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    #[OA\Tag(name: 'transaction')]
    public function __invoke(Request $request, UserInterface $user): JsonResponse
    {
        $this->managerRegistry->getManager()->beginTransaction();

        try {
            $transactionDTO = $this->serializer->deserialize($request->getContent(), TransactionDTO::class, 'json');
            $this->transactionService->create($transactionDTO, $user);
            $this->managerRegistry->getManager()->commit();

            return $this->json([], Response::HTTP_CREATED);
        } catch (
            ClientExceptionInterface|
            RedirectionExceptionInterface|
            ServerExceptionInterface|
            TransportExceptionInterface $exception
        ) {
            $this->managerRegistry->getManager()->rollback();

            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (
            PayerIsNotCommunException|
            PayeeIsCommunException|
            PayerHasNotBalanceLimit $exception
        ) {
            $this->managerRegistry->getManager()->rollback();

            return $this->json(['error' => $exception->getMessage()], $exception->getCode());
        } catch (\Throwable $exception) {
            $this->managerRegistry->getManager()->rollback();

            return $this->json(['error' => 'Ocorreu um erro inesperado'], Response::HTTP_BAD_REQUEST);
        }
    }
}
