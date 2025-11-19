<?php

namespace App\Service;

use App\DTO\PaginationMeta;
use Doctrine\ORM\Tools\Pagination\Paginator;

final class PaginationService
{
    /**
     * @param Paginator $paginator
     * @param int $page
     * @param int $limit
     * @return PaginationMeta
     */
    public function createMeta(Paginator $paginator, int $page, int $limit): PaginationMeta
    {
        $total = $paginator->count();
        $totalPages = (int)ceil($total / $limit);

        return new PaginationMeta(
            page: $page,
            limit: $limit,
            total: $total,
            totalPages: $totalPages,
        );
    }
}

