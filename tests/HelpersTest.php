<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Characterization tests for the pure global helpers in src/helpers.php.
 * The helpers are loaded automatically via Composer "files" autoloading
 * (PHPUnit bootstraps vendor/autoload.php), so no manual require is needed.
 * These pin current behavior ahead of the restructure (step 2+).
 */
final class HelpersTest extends TestCase
{
    public function testArraySearchResolvesDotNotation(): void
    {
        $config = ['app' => ['name' => 'My Blog', 'limit' => 200]];

        $this->assertSame('My Blog', arrayGet('app.name', $config));
        $this->assertSame(200, arrayGet('app.limit', $config));
    }

    public function testArraySearchReturnsFalseForMissingKeys(): void
    {
        $config = ['app' => ['name' => 'My Blog']];

        $this->assertFalse(arrayGet('app.missing', $config));
        $this->assertFalse(arrayGet('nope', $config));
    }

    public function testFlattenCollapsesNestedArrays(): void
    {
        // Mirrors config('app.allowed_post_file_types'), which is doubly nested.
        $input = [['txt', 'json', 'post']];

        $this->assertSame(['txt', 'json', 'post'], flatten($input));
    }

    public function testFlattenHandlesDeeperNesting(): void
    {
        $input = ['a', ['b', ['c', 'd']]];

        $this->assertSame(['a', 'b', 'c', 'd'], flatten($input));
    }
}
