<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class SlugGeneratorService
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function generateSlug(string $text): string
    {
        return $this->slugger->slug($text)->lower()->toString();
    }

    public function generateUniqueSlug(string $text, callable $existsCallback, ?string $excludeSlug = null): string
    {
        $baseSlug = $this->generateSlug($text);
        $slug = $baseSlug;
        $counter = 1;

        while ($existsCallback($slug) && $slug !== $excludeSlug) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

