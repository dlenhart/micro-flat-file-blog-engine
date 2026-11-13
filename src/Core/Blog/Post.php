<?php

namespace App\Core\Blog;

/**
 * A parsed post file: a JSON metadata header and a body, split on the
 * "#content#" delimiter.
 */
class Post
{
    private const CONTENT_DELIMITER = '#content#';

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private array $metadata,
        private string $content,
    ) {
    }

    public static function fromFile(string $path): self
    {
        $segments = explode(self::CONTENT_DELIMITER, (string) file_get_contents($path));
        $metadata = json_decode((string) array_shift($segments), true);

        return new self(
            is_array($metadata) ? $metadata : [],
            implode(self::CONTENT_DELIMITER, $segments)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function content(): string
    {
        return $this->content;
    }
}
