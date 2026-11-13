<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Blog\Post;
use App\Core\Blog\Repositories\PostRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class PostRepositoryTest extends TestCase
{
    private function repository(string $directory = __DIR__ . '/fixtures/'): PostRepository
    {
        return new PostRepository($directory, ['post'], 200, new NullLogger());
    }

    public function testAllReturnsASummaryForEachPostFile(): void
    {
        $summaries = $this->repository()->all();

        $this->assertContains('sample', array_column($summaries, 'file'));
    }

    public function testAllExposesMetadataAndATruncatedPreview(): void
    {
        $summary = $this->repository()->all()[0];

        $this->assertArrayHasKey('meta', $summary);
        $this->assertLessThanOrEqual(200, strlen($summary['content']));
    }

    public function testAllToleratesPostsWithoutADate(): void
    {
        $summaries = $this->repository(__DIR__ . '/fixtures/undated/')->all();

        $this->assertCount(1, $summaries);
        $this->assertSame('Undated Post', $summaries[0]['meta']['title']);
    }

    public function testPaginateReturnsTheFirstPageSlice(): void
    {
        $page = $this->repository(__DIR__ . '/fixtures/paged/')->paginate(1, 2);

        $this->assertCount(2, $page->items());
        $this->assertSame(2, $page->totalPages());
        $this->assertFalse($page->hasPrevious());
        $this->assertTrue($page->hasNext());
        $this->assertSame('Alpha', $page->items()[0]['meta']['title']);
    }

    public function testPaginateReturnsTheRemainderOnTheLastPage(): void
    {
        $page = $this->repository(__DIR__ . '/fixtures/paged/')->paginate(2, 2);

        $this->assertCount(1, $page->items());
        $this->assertTrue($page->hasPrevious());
        $this->assertFalse($page->hasNext());
        $this->assertSame('Gamma', $page->items()[0]['meta']['title']);
    }

    public function testPaginateClampsAnOutOfRangePage(): void
    {
        $page = $this->repository(__DIR__ . '/fixtures/paged/')->paginate(99, 2);

        $this->assertSame(2, $page->currentPage());
        $this->assertSame('Gamma', $page->items()[0]['meta']['title']);
    }

    public function testFindReturnsThePostForAKnownSlug(): void
    {
        $post = $this->repository()->find('sample');

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame('Sample Post', $post->metadata()['title']);
    }

    public function testFindReturnsNullForAnUnknownSlug(): void
    {
        $this->assertNull($this->repository()->find('does-not-exist'));
    }

    /**
     * @return list<array{0: string}>
     */
    public static function traversalSlugs(): array
    {
        return [
            ['../sample'],
            ['../../config'],
            ['..'],
            ['foo/bar'],
            [''],
        ];
    }

    #[DataProvider('traversalSlugs')]
    public function testFindRejectsDirectoryTraversal(string $slug): void
    {
        $this->assertNull($this->repository()->find($slug));
    }
}
