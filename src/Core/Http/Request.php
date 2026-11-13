<?php

namespace App\Core\Http;

use App\Core\Http\Interfaces\SanitizerInterface;
use stdClass;

/**
 * Value object for the incoming HTTP request.
 *
 * The constructor takes plain arrays so it can be tested without superglobals;
 * only capture() reads $_SERVER/$_GET/$_POST.
 */
class Request
{
    /**
     * @param array<string, mixed> $server Server/headers bag (mirrors $_SERVER).
     * @param array<string, mixed> $query  Query-string bag (mirrors $_GET).
     * @param array<string, mixed> $post   Request-body bag (mirrors $_POST).
     */
    public function __construct(
        private array $server = [],
        private array $query = [],
        private array $post = [],
        private ?SanitizerInterface $sanitizer = null,
    ) {
    }

    /**
     * Build a Request from PHP superglobals.
     */
    public static function capture(): self
    {
        return new self($_SERVER, $_GET, $_POST);
    }

    /**
     * Path portion of the request URI, trimmed of surrounding slashes.
     */
    public function uri(): string
    {
        $path = parse_url((string) ($this->server['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

        return trim((string) $path, '/');
    }

    /**
     * HTTP method (defaults to GET when absent).
     */
    public function method(): string
    {
        return (string) ($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Query parameters, each run through the sanitizer.
     */
    public function params(): stdClass
    {
        $sanitizer = $this->sanitizer ?? new HtmlSanitizer();
        $object = new stdClass();

        foreach ($this->query as $key => $value) {
            $object->$key = $sanitizer->sanitize((string) $value);
        }

        return $object;
    }
}
