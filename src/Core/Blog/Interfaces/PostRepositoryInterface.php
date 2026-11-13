<?php

namespace App\Core\Blog\Interfaces;

use App\Core\Blog\Page;
use App\Core\Blog\Post;

interface PostRepositoryInterface
{
    /**
     * Post summaries, newest first.
     *
     * @return list<array{file: string, meta: array<string, mixed>, content: string}>
     */
    public function all(): array;

    /**
     * One page of post summaries, newest first.
     */
    public function paginate(int $page, int $perPage): Page;

    public function find(string $slug): ?Post;
}
