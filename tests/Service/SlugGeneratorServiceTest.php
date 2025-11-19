<?php

namespace App\Tests\Service;

use App\Service\SlugGeneratorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

final class SlugGeneratorServiceTest extends TestCase
{
    private SluggerInterface $slugger;
    private SlugGeneratorService $slugGeneratorService;

    protected function setUp(): void
    {
        $this->slugger = $this->createMock(SluggerInterface::class);
        $this->slugGeneratorService = new SlugGeneratorService($this->slugger);
    }

    public function testGenerateSlug(): void
    {
        $text = 'Hello World';
        $expectedSlug = 'hello-world';

        $unicodeString = new UnicodeString('Hello-World');
        $this->slugger
            ->expects($this->once())
            ->method('slug')
            ->with($text)
            ->willReturn($unicodeString);

        $result = $this->slugGeneratorService->generateSlug($text);

        $this->assertEquals($expectedSlug, $result);
    }

    public function testGenerateSlugWithSpecialCharacters(): void
    {
        $text = 'Test & Example!';
        $expectedSlug = 'test-example';

        $unicodeString = new UnicodeString('Test-Example');
        $this->slugger
            ->expects($this->once())
            ->method('slug')
            ->with($text)
            ->willReturn($unicodeString);

        $result = $this->slugGeneratorService->generateSlug($text);

        $this->assertEquals($expectedSlug, $result);
    }

    public function testGenerateUniqueSlugWhenSlugDoesNotExist(): void
    {
        $text = 'Hello World';
        $baseSlug = 'hello-world';

        $unicodeString = new UnicodeString('Hello-World');
        $this->slugger
            ->expects($this->once())
            ->method('slug')
            ->with($text)
            ->willReturn($unicodeString);

        $existsCallback = function (string $slug) {
            return false;
        };

        $result = $this->slugGeneratorService->generateUniqueSlug($text, $existsCallback);

        $this->assertEquals($baseSlug, $result);
    }

    public function testGenerateUniqueSlugWhenSlugExists(): void
    {
        $text = 'Hello World';
        $baseSlug = 'hello-world';

        $unicodeString = new UnicodeString('Hello-World');
        $this->slugger
            ->expects($this->once())
            ->method('slug')
            ->with($text)
            ->willReturn($unicodeString);

        $callCount = 0;
        $existsCallback = function (string $slug) use ($baseSlug, &$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return $slug === $baseSlug;
            }
            return false;
        };

        $result = $this->slugGeneratorService->generateUniqueSlug($text, $existsCallback);

        $this->assertEquals('hello-world-1', $result);
        $this->assertEquals(2, $callCount);
    }

    public function testGenerateUniqueSlugWithExcludeSlug(): void
    {
        $text = 'Hello World';
        $baseSlug = 'hello-world';
        $excludeSlug = 'hello-world';

        $unicodeString = new UnicodeString('Hello-World');
        $this->slugger
            ->expects($this->once())
            ->method('slug')
            ->with($text)
            ->willReturn($unicodeString);

        $existsCallback = function (string $slug) {
            return true;
        };

        $result = $this->slugGeneratorService->generateUniqueSlug($text, $existsCallback, $excludeSlug);

        $this->assertEquals($baseSlug, $result);
    }

    public function testGenerateUniqueSlugWithExcludeSlugDifferent(): void
    {
        $text = 'Hello World';
        $baseSlug = 'hello-world';
        $excludeSlug = 'hello-world-1';

        $unicodeString = new UnicodeString('Hello-World');
        $this->slugger
            ->expects($this->once())
            ->method('slug')
            ->with($text)
            ->willReturn($unicodeString);

        $callCount = 0;
        $existsCallback = function (string $slug) use ($baseSlug, &$callCount) {
            $callCount++;
            return $slug === $baseSlug;
        };

        $result = $this->slugGeneratorService->generateUniqueSlug($text, $existsCallback, $excludeSlug);

        $this->assertEquals('hello-world-1', $result);
        $this->assertEquals(2, $callCount);
    }
}

