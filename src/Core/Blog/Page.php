<?php

namespace App\Core\Blog;

/**
 * One page of post summaries plus the counters a view needs to draw
 * pagination links.
 */
class Page
{
    /**
     * @param list<array{file: string, meta: array<string, mixed>, content: string}> $items
     */
    public function __construct(
        private array $items,
        private int $currentPage,
        private int $perPage,
        private int $total,
    ) {
    }

    /**
     * @return list<array{file: string, meta: array<string, mixed>, content: string}>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function totalPages(): int
    {
        return max(1, (int) ceil($this->total / $this->perPage));
    }

    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages();
    }

    public function previousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function nextPage(): int
    {
        return min($this->totalPages(), $this->currentPage + 1);
    }
}
