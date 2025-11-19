<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'PaginationMeta',
    description: 'Pagination metadata'
)]
final readonly class PaginationMeta
{
    public function __construct(
        #[OA\Property(description: 'Current page', type: 'integer', example: 1)]
        public int $page,
        #[OA\Property(description: 'Number of items per page', type: 'integer', example: 20)]
        public int $limit,
        #[OA\Property(description: 'Total number of items', type: 'integer', example: 100)]
        public int $total,
        #[OA\Property(description: 'Total number of pages', type: 'integer', example: 5)]
        public int $totalPages,
    ) {
    }
}

