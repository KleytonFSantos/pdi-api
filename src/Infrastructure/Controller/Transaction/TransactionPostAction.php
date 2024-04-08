<?php

namespace App\Infrastructure\Controller\Transaction;

use App\Domain\DTO\TransactionDTO;
use App\Domain\Exception\PayeeIsCommunException;
use App\Domain\Exception\PayerHasNotBalanceLimit;
use App\Domain\Exception\PayerIsNotCommunException;
use App\Infrastructure\Services\TransactionService;
use Doctrine\Persistence\ManagerRegistry;
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
use Throwable;

class TransactionPostAction extends AbstractController
{

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly TransactionService $transactionService,
        private readonly ManagerRegistry $managerRegistry
    ) {
    }

    #[Route('/transaction', name: 'api_transaction', methods: ['POST'])]
    public function __invoke(Request $request, UserInterface $user): JsonResponse
    {
        $this->managerRegistry->getManager()->beginTransaction();

        try {
            $transactionDTO = $this->serializer->deserialize($request->getContent(), TransactionDTO::class, 'json');
            $this->transactionService->create($transactionDTO, $user);
            $this->managerRegistry->getManager()->commit();

            return $this->json([], Response::HTTP_CREATED);
        } catch (
            ClientExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface |
            TransportExceptionInterface $exception
        ) {
            $this->managerRegistry->getManager()->rollback();
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (
            PayerIsNotCommunException |
            PayeeIsCommunException |
            PayerHasNotBalanceLimit $exception
        ) {
            $this->managerRegistry->getManager()->rollback();
            return $this->json(['error' => $exception->getMessage()], $exception->getCode());
        } catch (Throwable $exception) {
            $this->managerRegistry->getManager()->rollback();
            return $this->json(['error' => 'Ocorreu um erro inesperado'], Response::HTTP_BAD_REQUEST);
        }
    }
}