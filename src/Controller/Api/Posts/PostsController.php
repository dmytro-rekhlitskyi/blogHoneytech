<?php

namespace App\Controller\Api\Posts;

use App\DTO\Posts\SearchPostsRequest;
use App\DTO\Posts\SearchPostsResponse;
use App\Mapper\PostMapper;
use App\Repository\PostRepository;
use App\Service\PaginationService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'Posts', description: 'Posts management')]
final class PostsController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly ValidatorInterface $validator,
        private readonly PostMapper $postMapper,
        private readonly PaginationService $paginationService,
    ) {
    }

    #[Route('/api/posts', name: 'api_index_posts', methods: ['GET'])]
    #[OA\Get(
        path: '/api/posts',
        description: 'Returns a list of posts with pagination. Can be filtered by tag.',
        summary: 'Get posts list',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'tagSlug',
                description: 'Tag slug to filter posts',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', maxLength: 255)
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, default: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, default: 20)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with posts list',
                content: new OA\JsonContent(
                    ref: new Model(type: SearchPostsResponse::class)
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'property', type: 'string'),
                                    new OA\Property(property: 'message', type: 'string'),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(
        #[MapQueryString] SearchPostsRequest $request,
    ): JsonResponse {
        $violations = $this->validator->validate($request);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'property' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
            return $this->json(['errors' => $errors], 400);
        }

        $page = $request->page ?? 1;
        $limit = $request->limit ?? 20;

        $paginator = $this->postRepository->findPaginated(
            $request->tagSlug,
            $page,
            $limit
        );

        $posts = $this->postMapper->toArrayCollection($paginator);
        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $response = new SearchPostsResponse(
            data: $posts,
            meta: $meta
        );

        return $this->json($response);
    }
}
