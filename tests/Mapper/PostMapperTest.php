<?php

namespace App\Tests\Mapper;

use App\Entity\Post;
use App\Entity\Tag;
use App\Mapper\PostMapper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PostMapperTest extends TestCase
{
    private PostMapper $postMapper;

    protected function setUp(): void
    {
        $this->postMapper = new PostMapper();
    }

    public function testToArrayWithPostWithoutTags(): void
    {
        $post = new Post();
        $this->setPrivateProperty($post, 'id', 1);
        $post->setTitle('Test Post');
        $post->setSlug('test-post');
        $post->setBody('Test body content');

        $result = $this->postMapper->toArray($post);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Test Post', $result['title']);
        $this->assertEquals('test-post', $result['slug']);
        $this->assertEquals('Test body content', $result['body']);
        $this->assertIsArray($result['tags']);
        $this->assertEmpty($result['tags']);
    }

    public function testToArrayWithPostWithTags(): void
    {
        $post = new Post();
        $this->setPrivateProperty($post, 'id', 1);
        $post->setTitle('Test Post');
        $post->setSlug('test-post');
        $post->setBody('Test body content');

        $tag1 = new Tag();
        $this->setPrivateProperty($tag1, 'id', 1);
        $tag1->setName('PHP');
        $tag1->setSlug('php');

        $tag2 = new Tag();
        $this->setPrivateProperty($tag2, 'id', 2);
        $tag2->setName('Symfony');
        $tag2->setSlug('symfony');

        $post->addTag($tag1);
        $post->addTag($tag2);

        $result = $this->postMapper->toArray($post);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Test Post', $result['title']);
        $this->assertEquals('test-post', $result['slug']);
        $this->assertEquals('Test body content', $result['body']);
        $this->assertIsArray($result['tags']);
        $this->assertCount(2, $result['tags']);

        $this->assertEquals(1, $result['tags'][0]['id']);
        $this->assertEquals('PHP', $result['tags'][0]['name']);
        $this->assertEquals('php', $result['tags'][0]['slug']);

        $this->assertEquals(2, $result['tags'][1]['id']);
        $this->assertEquals('Symfony', $result['tags'][1]['name']);
        $this->assertEquals('symfony', $result['tags'][1]['slug']);
    }

    public function testToArrayWithNullValues(): void
    {
        $post = new Post();

        $result = $this->postMapper->toArray($post);

        $this->assertIsArray($result);
        $this->assertNull($result['id']);
        $this->assertNull($result['title']);
        $this->assertNull($result['slug']);
        $this->assertNull($result['body']);
        $this->assertIsArray($result['tags']);
    }

    public function testToArrayCollectionWithEmptyCollection(): void
    {
        $posts = [];

        $result = $this->postMapper->toArrayCollection($posts);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testToArrayCollectionWithMultiplePosts(): void
    {
        $post1 = new Post();
        $this->setPrivateProperty($post1, 'id', 1);
        $post1->setTitle('Post 1');
        $post1->setSlug('post-1');
        $post1->setBody('Body 1');

        $post2 = new Post();
        $this->setPrivateProperty($post2, 'id', 2);
        $post2->setTitle('Post 2');
        $post2->setSlug('post-2');
        $post2->setBody('Body 2');

        $posts = [$post1, $post2];

        $result = $this->postMapper->toArrayCollection($posts);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Post 1', $result[0]['title']);

        $this->assertEquals(2, $result[1]['id']);
        $this->assertEquals('Post 2', $result[1]['title']);
    }

    public function testToArrayCollectionWithIterable(): void
    {
        $post1 = new Post();
        $this->setPrivateProperty($post1, 'id', 1);
        $post1->setTitle('Post 1');
        $post1->setSlug('post-1');
        $post1->setBody('Body 1');

        $post2 = new Post();
        $this->setPrivateProperty($post2, 'id', 2);
        $post2->setTitle('Post 2');
        $post2->setSlug('post-2');
        $post2->setBody('Body 2');

        $posts = (function () use ($post1, $post2) {
            yield $post1;
            yield $post2;
        })();

        $result = $this->postMapper->toArrayCollection($posts);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);

        $property->setValue($object, $value);
    }
}

