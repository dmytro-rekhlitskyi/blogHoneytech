<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return Paginator<Post>
     */
    public function findPaginated(
        ?string $tagSlug = null,
        int $page = 1,
        int $limit = 20
    ): Paginator {
        $qb = $this->createQueryBuilder('p')
            ->distinct()
            ->orderBy('p.createdAt', 'ASC');

        if ($tagSlug !== null) {
            $slugsArray = array_filter(array_map('trim', explode(';', $tagSlug)));

            if (!empty($slugsArray)) {
                $qb->innerJoin('p.tags', 't')
                    ->andWhere('t.slug IN (:slugs)')
                    ->setParameter('slugs', $slugsArray)
                    ->distinct();
            }
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb, true);
    }
}
