<?php

namespace App\DTO\Tags;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    title: 'SearchTagsRequest',
    description: 'Request for searching tags with filtering and pagination'
)]
final readonly class SearchTagsRequest
{
    public function __construct(
        #[OA\Property(
            description: 'Search query to filter tags',
            type: 'string',
            maxLength: 255,
            example: 'php',
            nullable: true
        )]
        #[Assert\AtLeastOneOf([
            new Assert\Blank(),
            new Assert\Length(min: 1, max: 255),
        ])]
        public ?string $query = null,

        #[OA\Property(
            description: 'Page number',
            type: 'integer',
            default: 1,
            minimum: 1,
            example: 1,
            nullable: true
        )]
        #[Assert\When(
            expression: 'value !== null',
            constraints: [
                new Assert\Positive(),
                new Assert\Type('integer'),
            ]
        )]
        public ?int $page = null,

        #[OA\Property(
            description: 'Number of items per page',
            type: 'integer',
            default: 20,
            minimum: 1,
            example: 20,
            nullable: true
        )]
        #[Assert\When(
            expression: 'value !== null',
            constraints: [
                new Assert\Positive(),
                new Assert\Type('integer'),
            ]
        )]
        public ?int $limit = null,
    ) {
    }
}

