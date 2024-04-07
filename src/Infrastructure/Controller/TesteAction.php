<?php

namespace App\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TesteAction extends AbstractController
{

    #[Route('/teste', name: 'app_teste')]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => 'Hello World!'
            ],
            Response::HTTP_OK
        );
    }

}