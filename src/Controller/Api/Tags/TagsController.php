<?php

namespace App\Controller\Api\Tags;

use App\DTO\Tags\SearchTagsRequest;
use App\DTO\Tags\SearchTagsResponse;
use App\Mapper\TagMapper;
use App\Repository\TagRepository;
use App\Service\PaginationService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'Tags', description: 'Tags management')]
final class TagsController extends AbstractController
{
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly ValidatorInterface $validator,
        private readonly TagMapper $tagMapper,
        private readonly PaginationService $paginationService,
    ) {
    }

    #[Route('/api/tags', name: 'api_get_tags', methods: ['GET'])]
    #[OA\Get(
        description: 'Returns a list of tags with pagination. Can be filtered by search query.',
        summary: 'Get tags list',
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(
                name: 'query',
                description: 'Search query to filter tags',
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
                description: 'Successful response with tags list',
                content: new OA\JsonContent(
                    ref: new Model(type: SearchTagsResponse::class)
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
        #[MapQueryString] SearchTagsRequest $request,
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

        $paginator = $this->tagRepository->findPaginated(
            $request->query,
            $page,
            $limit
        );

        $tags = $this->tagMapper->toArrayCollection($paginator);
        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $response = new SearchTagsResponse(
            data: $tags,
            meta: $meta
        );

        return $this->json($response);
    }
}

