<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\Tag;
use App\Service\SlugGeneratorService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly SlugGeneratorService $slugGeneratorService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $tags = [];

        for ($i = 1; $i <= 25; $i++) {
            $tag = new Tag();
            $tagName = "tag {$i}";
            $tag->setName($tagName);
            $tag->setSlug($this->slugGeneratorService->generateSlug($tagName));

            $manager->persist($tag);
            $tags[] = $tag;
        }

        $posts = [];

        for ($i = 1; $i <= 25; $i++) {
            $post = new Post();
            $postTitle = "post {$i}";
            $postBody = "post body {$i}";
            $post->setTitle($postTitle);
            $post->setBody($postBody);
            $post->setSlug($this->slugGeneratorService->generateSlug($postTitle));

            $manager->persist($post);
            $posts[] = $post;
        }

        for ($i = 0; $i < 10; $i++) {
            $post = $posts[$i];

            $tagsCount = rand(1, 5);

            $shuffledTags = $tags;
            shuffle($shuffledTags);

            for ($j = 0; $j < $tagsCount; $j++) {
                $post->addTag($shuffledTags[$j]);
            }
        }

        $manager->flush();
    }
}
