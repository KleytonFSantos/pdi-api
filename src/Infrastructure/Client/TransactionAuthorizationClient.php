<?php

namespace App\Infrastructure\Client;

use App\Domain\Enum\TransactionAuthorization;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TransactionAuthorizationClient
{
    const AUTHORIZATION_URL = 'https://run.mocky.io/v3/a44f11a6-1788-4160-bc48-610e66f8386b';

    public function __construct(private HttpClientInterface $client,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function checkAuthorizationStatus(): bool
    {
        $response = $this->client->request(
            'GET',
            self::AUTHORIZATION_URL
        );

        return json_decode(
                $response->getContent(),
                true
            )['message']
            === TransactionAuthorization::Autorizado->value;    }
}