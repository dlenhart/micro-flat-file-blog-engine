<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Blog\Post;
use PHPUnit\Framework\TestCase;

final class PostTest extends TestCase
{
    private string $fixture;

    protected function setUp(): void
    {
        $this->fixture = __DIR__ . '/fixtures/sample.post';
    }

    public function testMetadataIsParsedFromTheJsonHeader(): void
    {
        $post = Post::fromFile($this->fixture);

        $this->assertSame(
            [
                'title' => 'Sample Post',
                'author' => 'Test Author',
                'date' => '2020-01-02',
                'slug' => 'sample',
            ],
            $post->metadata()
        );
    }

    public function testContentExcludesTheMetadataHeader(): void
    {
        $post = Post::fromFile($this->fixture);

        $this->assertStringContainsString('First body line.', $post->content());
        $this->assertStringNotContainsString('"title"', $post->content());
    }

    public function testContentPreservesLiteralDelimitersInTheBody(): void
    {
        $post = Post::fromFile($this->fixture);

        $this->assertStringContainsString(
            'A literal #content# marker inside the body.',
            $post->content()
        );
    }
}
