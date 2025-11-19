<?php

namespace App\DTO\Tags;

use App\DTO\PaginationMeta;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'SearchTagsResponse',
    description: 'Response with tags list and pagination metadata'
)]
final readonly class SearchTagsResponse
{

    public function __construct(
        #[OA\Property(
            description: 'Array of tags',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'PHP'),
                    new OA\Property(property: 'slug', type: 'string', example: 'php'),
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

