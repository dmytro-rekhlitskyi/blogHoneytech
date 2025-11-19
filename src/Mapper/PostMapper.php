<?php

namespace App\Mapper;

use App\Entity\Post;

final class PostMapper
{
    /**
     * @param Post $post
     * @return array
     */
    public function toArray(Post $post): array
    {
        $tags = [];
        foreach ($post->getTags() as $tag) {
            $tags[] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'slug' => $tag->getSlug(),
            ];
        }

        return [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'slug' => $post->getSlug(),
            'body' => $post->getBody(),
            'tags' => $tags,
        ];
    }

    /**
     * @param iterable $posts
     * @return array
     */
    public function toArrayCollection(iterable $posts): array
    {
        $result = [];
        foreach ($posts as $post) {
            $result[] = $this->toArray($post);
        }
        return $result;
    }
}

