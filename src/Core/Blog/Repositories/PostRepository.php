<?php

namespace App\Core\Blog\Repositories;

use App\Core\Blog\Interfaces\PostRepositoryInterface;
use App\Core\Blog\Page;
use App\Core\Blog\Post;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use SplFileInfo;

class PostRepository implements PostRepositoryInterface
{
    private const EXTENSION = 'post';

    /**
     * @param list<string> $allowedFileTypes
     */
    public function __construct(
        private string $postsDirectory,
        private array $allowedFileTypes,
        private int $previewLength,
        private LoggerInterface $logger,
    ) {
    }

    public function all(): array
    {
        if (!is_dir($this->postsDirectory)) {
            $this->logger->error("Posts directory does not exist: {$this->postsDirectory}");

            return [];
        }

        $summaries = [];

        foreach ($this->postFiles() as $file) {
            $post = Post::fromFile($file);

            $summaries[] = [
                'file'    => pathinfo($file, PATHINFO_FILENAME),
                'meta'    => $post->metadata(),
                'content' => substr($post->content(), 0, $this->previewLength),
            ];
        }

        return $this->sortByDate($summaries);
    }

    public function paginate(int $page, int $perPage): Page
    {
        $perPage = max(1, $perPage);

        $all = $this->all();
        $totalPages = max(1, (int) ceil(count($all) / $perPage));
        $page = max(1, min($page, $totalPages));

        return new Page(
            array_slice($all, ($page - 1) * $perPage, $perPage),
            $page,
            $perPage,
            count($all)
        );
    }

    public function find(string $slug): ?Post
    {
        if (!$this->isSafeSlug($slug)) {
            return null;
        }

        $path = $this->postsDirectory . $slug . '.' . self::EXTENSION;

        return file_exists($path) ? Post::fromFile($path) : null;
    }

    /**
     * Only allow bare slugs, so a caller can't climb out of the posts directory
     * with traversal like "../../config".
     */
    private function isSafeSlug(string $slug): bool
    {
        return $slug !== '' && preg_match('/^[A-Za-z0-9_-]+$/', $slug) === 1;
    }

    /**
     * @return list<string>
     */
    private function postFiles(): array
    {
        $files = [];

        foreach (array_diff(scandir($this->postsDirectory), ['.', '..']) as $entry) {
            $path = $this->postsDirectory . $entry;

            if ($this->isAllowed($path)) {
                $files[] = $path;
            }
        }

        return $files;
    }

    private function isAllowed(string $path): bool
    {
        return filetype($path) === 'file'
            && in_array((new SplFileInfo($path))->getExtension(), $this->allowedFileTypes, true);
    }

    /**
     * @param list<array{file: string, meta: array<string, mixed>, content: string}> $summaries
     *
     * @return list<array{file: string, meta: array<string, mixed>, content: string}>
     */
    private function sortByDate(array $summaries): array
    {
        usort(
            $summaries,
            fn (array $old, array $new): int => $this->postDate($new) <=> $this->postDate($old)
        );

        return $summaries;
    }

    /**
     * Date used for sorting. Undated or malformed posts sort to the bottom
     * instead of breaking the whole listing.
     *
     * @param array{meta: array<string, mixed>} $summary
     */
    private function postDate(array $summary): DateTimeInterface
    {
        $date = $summary['meta']['date'] ?? null;

        try {
            return new DateTimeImmutable(is_string($date) && $date !== '' ? $date : '@0');
        } catch (\Exception) {
            return new DateTimeImmutable('@0');
        }
    }
}
