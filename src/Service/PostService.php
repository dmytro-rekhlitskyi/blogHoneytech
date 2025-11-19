<?php

namespace App\Service;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class PostService
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SlugGeneratorService $slugGeneratorService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function create(Post $post): void
    {
        $slug = $this->slugGeneratorService->generateUniqueSlug(
            $post->getTitle(),
            fn(string $slug) => $this->postRepository->findOneBy(['slug' => $slug]) !== null
        );
        $post->setSlug($slug);

        try {
            $this->entityManager->persist($post);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->warning('Failed to create post: duplicate slug', [
                'slug' => $slug,
                'title' => $post->getTitle(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('A post with this title already exists. Please choose a different title.', 0, $e);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create post', [
                'post_id' => $post->getId(),
                'title' => $post->getTitle(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('An error occurred while creating the post. Please try again.', 0, $e);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function update(Post $post): void
    {
        $slug = $this->slugGeneratorService->generateUniqueSlug(
            $post->getTitle(),
            fn(string $slug) => $this->postRepository->findOneBy(['slug' => $slug]) !== null,
            $post->getSlug()
        );
        $post->setSlug($slug);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->warning('Failed to update post: duplicate slug', [
                'post_id' => $post->getId(),
                'slug' => $slug,
                'title' => $post->getTitle(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('A post with this title already exists. Please choose a different title.', 0, $e);
        } catch (\Exception $e) {
            $this->logger->error('Failed to update post', [
                'post_id' => $post->getId(),
                'title' => $post->getTitle(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('An error occurred while updating the post. Please try again.', 0, $e);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function delete(Post $post): void
    {
        try {
            $this->entityManager->remove($post);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete post', [
                'post_id' => $post->getId(),
                'title' => $post->getTitle(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('An error occurred while deleting the post. Please try again.', 0, $e);
        }
    }
}

