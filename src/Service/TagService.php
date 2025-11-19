<?php

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class TagService
{
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SlugGeneratorService $slugGeneratorService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function create(Tag $tag): void
    {
        $slug = $this->slugGeneratorService->generateUniqueSlug(
            $tag->getName(),
            fn(string $slug) => $this->tagRepository->findOneBy(['slug' => $slug]) !== null
        );
        $tag->setSlug($slug);

        try {
            $this->entityManager->persist($tag);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->warning('Failed to create tag: duplicate slug', [
                'slug' => $slug,
                'name' => $tag->getName(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('A tag with this name already exists. Please choose a different name.', 0, $e);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create tag', [
                'tag_id' => $tag->getId(),
                'name' => $tag->getName(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('An error occurred while creating the tag. Please try again.', 0, $e);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function update(Tag $tag): void
    {
        $slug = $this->slugGeneratorService->generateUniqueSlug(
            $tag->getName(),
            fn(string $slug) => $this->tagRepository->findOneBy(['slug' => $slug]) !== null,
            $tag->getSlug()
        );
        $tag->setSlug($slug);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->warning('Failed to update tag: duplicate slug', [
                'tag_id' => $tag->getId(),
                'slug' => $slug,
                'name' => $tag->getName(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('A tag with this name already exists. Please choose a different name.', 0, $e);
        } catch (\Exception $e) {
            $this->logger->error('Failed to update tag', [
                'tag_id' => $tag->getId(),
                'name' => $tag->getName(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('An error occurred while updating the tag. Please try again.', 0, $e);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function delete(Tag $tag): void
    {
        try {
            $this->entityManager->remove($tag);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete tag', [
                'tag_id' => $tag->getId(),
                'name' => $tag->getName(),
                'exception' => $e,
            ]);
            throw new \RuntimeException('An error occurred while deleting the tag. Please try again.', 0, $e);
        }
    }
}

