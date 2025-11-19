<?php

namespace App\Tests\Service;

use App\DTO\PaginationMeta;
use App\Service\PaginationService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class PaginationServiceTest extends TestCase
{
    private PaginationService $paginationService;

    protected function setUp(): void
    {
        $this->paginationService = new PaginationService();
    }

    public function testCreateMetaWithExactDivision(): void
    {
        $paginator = $this->createMockPaginator(100);
        $page = 1;
        $limit = 20;

        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $this->assertInstanceOf(PaginationMeta::class, $meta);
        $this->assertEquals(1, $meta->page);
        $this->assertEquals(20, $meta->limit);
        $this->assertEquals(100, $meta->total);
        $this->assertEquals(5, $meta->totalPages);
    }

    public function testCreateMetaWithRemainder(): void
    {
        $paginator = $this->createMockPaginator(101);
        $page = 1;
        $limit = 20;

        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $this->assertEquals(101, $meta->total);
        $this->assertEquals(6, $meta->totalPages); // ceil(101/20) = 6
    }

    public function testCreateMetaWithSinglePage(): void
    {
        $paginator = $this->createMockPaginator(10);
        $page = 1;
        $limit = 20;

        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $this->assertEquals(10, $meta->total);
        $this->assertEquals(1, $meta->totalPages);
    }

    public function testCreateMetaWithEmptyResult(): void
    {
        $paginator = $this->createMockPaginator(0);
        $page = 1;
        $limit = 20;

        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $this->assertEquals(0, $meta->total);
        $this->assertEquals(0, $meta->totalPages);
    }

    public function testCreateMetaWithDifferentPage(): void
    {
        $paginator = $this->createMockPaginator(100);
        $page = 3;
        $limit = 20;

        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $this->assertEquals(3, $meta->page);
        $this->assertEquals(20, $meta->limit);
        $this->assertEquals(100, $meta->total);
        $this->assertEquals(5, $meta->totalPages);
    }

    public function testCreateMetaWithLargeLimit(): void
    {
        $paginator = $this->createMockPaginator(50);
        $page = 1;
        $limit = 100;

        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $this->assertEquals(50, $meta->total);
        $this->assertEquals(1, $meta->totalPages);
    }

    /**
     * @param int $count
     * @return Paginator&MockObject
     */
    private function createMockPaginator(int $count): Paginator
    {
        $paginator = $this->createMock(Paginator::class);
        $paginator->expects($this->once())
            ->method('count')
            ->willReturn($count);

        return $paginator;
    }
}

