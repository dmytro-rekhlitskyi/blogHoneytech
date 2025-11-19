<?php

namespace App\DTO\Posts;

use App\DTO\PaginationMeta;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'SearchPostsResponse',
    description: 'Response with posts list and pagination metadata'
)]
final readonly class SearchPostsResponse
{
    public function __construct(
        #[OA\Property(
            description: 'Array of posts',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Post title'),
                    new OA\Property(property: 'slug', type: 'string', example: 'post-title'),
                    new OA\Property(property: 'body', type: 'string', example: 'Post content'),
                    new OA\Property(
                        property: 'tags',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'PHP'),
                                new OA\Property(property: 'slug', type: 'string', example: 'php'),
                            ],
                            type: 'object'
                        )
                    ),
                ],
                type: 'object'
            )
        )]
        public array $data,
        #[OA\Property(
            property: 'meta',
            ref: new Model(type: PaginationMeta::class),
            description: 'Pagination metadata'
        )]
        public PaginationMeta $meta,
    ) {
    }
}

