<?php

namespace App\Mapper;

use App\Entity\Tag;

final class TagMapper
{
    public function toArray(Tag $tag): array
    {
        return [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'slug' => $tag->getSlug(),
        ];
    }

    /**
     * @param iterable $tags
     * @return array
     */
    public function toArrayCollection(iterable $tags): array
    {
        $result = [];
        foreach ($tags as $tag) {
            $result[] = $this->toArray($tag);
        }
        return $result;
    }
}

