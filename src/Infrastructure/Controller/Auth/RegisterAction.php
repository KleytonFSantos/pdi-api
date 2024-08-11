<?php

namespace App\Infrastructure\Controller\Auth;

use App\Domain\Entity\User;
use App\Domain\Interface\RegisterServiceInterface;
use App\Domain\Repository\UserRepository;
use App\Infrastructure\Services\WalletService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RegisterAction extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SerializerInterface $serializer,
        private readonly WalletService $walletService,
        private readonly RegisterServiceInterface $userRegistrationService
    ) {
    }

    #[Route('/register', name: 'app_registration', methods: 'POST')]
    #[OA\Post(
        summary: 'Register an account',
        requestBody: new OA\RequestBody(
            description: 'User registration',
            required: true,
            content: [
                new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'name',
                            summary: 'name',
                            description: 'name of user',
                            value: 'john'
                        ),
                        new OA\Examples(
                            example: 'email',
                            summary: 'email',
                            description: 'email of user',
                            value: 'john@example.com'
                        ),
                        new OA\Examples(
                            example: 'password',
                            summary: 'password',
                            description: 'password of user',
                            value: '12345678'
                        ),
                    ],
                    properties: [
                        new OA\Property(
                            property: 'name',
                            description: 'name of user',
                            type: 'integer',
                            example: 'john'
                        ),
                        new OA\Property(
                            property: 'email',
                            description: 'email of user',
                            type: 'float',
                            example: 'john@example.com'
                        ),
                        new OA\Property(
                            property: 'password',
                            description: 'password of user',
                            type: 'float',
                            example: '12345678'
                        )
                    ]
                )
            ],
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'OK')
                    ],
                    type: 'object',
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Unique Email Constraint',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'This email is already registered.')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Something went wrong.')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    #[OA\Tag(name: 'user')]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            /* @var $user User */
            $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

            $validationErrors = $this->userRegistrationService->validateUser($user);

            if ($validationErrors) {
                return new JsonResponse(
                    [
                        'error' => (string) $validationErrors[0]->getMessage(),
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $hashedPassword = $this->userRegistrationService->hashPassword($user->getPassword());
            $user->setPassword($hashedPassword);

            $wallet = $this->walletService->create($user);
            $user->setWallet($wallet);

            $this->userRepository->save($user, true);

            return new JsonResponse(
                ['user' => $user->getUserIdentifier()],
                Response::HTTP_OK,
                [],
            );
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(
                [
                    'error' => 'This email is already registered.',
                    'type' => 'Unique Email Constraint',
                ],
                Response::HTTP_BAD_REQUEST,
                [],
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [],
            );
        }
    }
}