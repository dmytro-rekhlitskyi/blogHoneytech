<?php

namespace App\Tests\Controller\Front;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class TagsControllerTest extends WebTestCase
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
        $crawler = $this->client->request('GET', '/tags');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tags');
    }

    public function testIndexPageWithPagination(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $tag = new Tag();
            $tag->setName("Tag {$i}");
            $tag->setSlug("tag-{$i}");
            $this->entityManager->persist($tag);
        }
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/tags?page=1&limit=20');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.list__table');
    }

    public function testIndexPageWithQueryFilter(): void
    {
        $tag = new Tag();
        $tag->setName('PHP');
        $tag->setSlug('php');
        $this->entityManager->persist($tag);

        $tag2 = new Tag();
        $tag2->setName('Python');
        $tag2->setSlug('python');
        $this->entityManager->persist($tag2);

        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/tags?query=php');

        $this->assertResponseIsSuccessful();
    }

    public function testCreateTagPageLoads(): void
    {
        $crawler = $this->client->request('GET', '/tags/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreateTagSuccess(): void
    {
        $crawler = $this->client->request('GET', '/tags/create');

        $form = $crawler->selectButton('Create')->form([
            'tag[name]' => 'New Test Tag',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/tags');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.list__flash--success', 'Tag successfully created');
    }

    public function testCreateTagWithValidationError(): void
    {
        $crawler = $this->client->request('GET', '/tags/create');

        $form = $crawler->selectButton('Create')->form([
            'tag[name]' => '',
        ]);

        $crawler = $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSelectorExists('.form__field ul');
    }

    public function testEditTagPageLoads(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setSlug('test-tag');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', "/tags/{$tag->getId()}/edit");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertInputValueSame('tag[name]', 'Test Tag');
    }

    public function testEditTagNotFound(): void
    {
        $this->client->request('GET', '/tags/99999/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testEditTagSuccess(): void
    {
        $tag = new Tag();
        $tag->setName('Original Tag');
        $tag->setSlug('original-tag');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', "/tags/{$tag->getId()}/edit");

        $form = $crawler->selectButton('Update')->form([
            'tag[name]' => 'Updated Tag',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/tags');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.list__flash--success', 'Tag successfully updated');
    }

    public function testDeleteTagSuccess(): void
    {
        $tag = new Tag();
        $tag->setName('Tag to Delete');
        $tag->setSlug('tag-to-delete');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
        $tagId = $tag->getId();

        $crawler = $this->client->request('GET', '/tags');

        $form = $crawler->filter("form[action='/tags/{$tagId}/delete']")->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/tags');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.list__flash--success', 'Tag successfully deleted');

        $deletedTag = $this->entityManager->getRepository(Tag::class)->find($tagId);
        $this->assertNull($deletedTag);
    }

    public function testDeleteTagNotFound(): void
    {
        $this->client->request('POST', '/tags/99999/delete');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}

