<?php

namespace App\Tests\Controller\Front;

use App\Entity\Post;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class PostsControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->createQuery('DELETE FROM App\Entity\Post p')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Tag t')->execute();
        $this->entityManager->clear();
    }

    public function testIndexPageLoads(): void
    {
        $crawler = $this->client->request('GET', '/posts');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Posts');
    }

    public function testIndexPageWithPagination(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $post = new Post();
            $post->setTitle("Test Post {$i}");
            $post->setSlug("test-post-{$i}");
            $post->setBody("Body content {$i}");
            $this->entityManager->persist($post);
        }
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/posts?page=1&limit=20');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.list__table');
    }

    public function testIndexPageWithTagFilter(): void
    {
        $tag = new Tag();
        $tag->setName('PHP');
        $tag->setSlug('php');
        $this->entityManager->persist($tag);

        $post = new Post();
        $post->setTitle('PHP Post');
        $post->setSlug('php-post');
        $post->setBody('PHP content');
        $post->addTag($tag);
        $this->entityManager->persist($post);

        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/posts?tags=php');

        $this->assertResponseIsSuccessful();
    }

    public function testCreatePostPageLoads(): void
    {
        $crawler = $this->client->request('GET', '/posts/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreatePostSuccess(): void
    {
        $crawler = $this->client->request('POST', '/posts/create');

        $form = $crawler->selectButton('Create')->form([
            'post[title]' => 'New Test Post',
            'post[body]' => 'Test body content',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/posts');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.list__flash--success', 'Post successfully created');
    }

    public function testCreatePostWithValidationError(): void
    {
        $crawler = $this->client->request('POST', '/posts/create');

        $form = $crawler->selectButton('Create')->form([
            'post[title]' => 'AB',
            'post[body]' => '',
        ]);

        $crawler = $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSelectorExists('.form__field ul');
    }

    public function testEditPostPageLoads(): void
    {
        $post = new Post();
        $post->setTitle('Test Post');
        $post->setSlug('test-post');
        $post->setBody('Test body');
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', "/posts/{$post->getId()}/edit");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertInputValueSame('post[title]', 'Test Post');
    }

    public function testEditPostNotFound(): void
    {
        $this->client->request('GET', '/posts/99999/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testEditPostSuccess(): void
    {
        $post = new Post();
        $post->setTitle('Original Title');
        $post->setSlug('original-title');
        $post->setBody('Original body');
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', "/posts/{$post->getId()}/edit");

        $form = $crawler->selectButton('Update')->form([
            'post[title]' => 'Updated Title',
            'post[body]' => 'Updated body',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/posts');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.list__flash--success', 'Post successfully updated');
    }

    public function testShowPostPageLoads(): void
    {
        $post = new Post();
        $post->setTitle('Test Post');
        $post->setSlug('test-post');
        $post->setBody('Test body content');
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/posts/test-post');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Test Post');
    }

    public function testShowPostNotFound(): void
    {
        $this->client->request('GET', '/posts/non-existent-slug');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeletePostSuccess(): void
    {
        $post = new Post();
        $post->setTitle('Post to Delete');
        $post->setSlug('post-to-delete');
        $post->setBody('Body content');
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $postId = $post->getId();

        $crawler = $this->client->request('GET', '/posts');

        $form = $crawler->filter("form[action='/posts/{$postId}/delete']")->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/posts');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.list__flash--success', 'Post successfully deleted');

        $deletedPost = $this->entityManager->getRepository(Post::class)->find($postId);
        $this->assertNull($deletedPost);
    }

    public function testDeletePostNotFound(): void
    {
        $this->client->request('POST', '/posts/99999/delete');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}

