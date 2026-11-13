<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the injectable Request value object (step 4, piece b).
 * Constructed from plain arrays — no superglobals — so the parsing and
 * sanitization logic is verified in isolation.
 */
final class RequestTest extends TestCase
{
    public function testUriReturnsTrimmedPathWithoutQueryString(): void
    {
        $request = new Request(['REQUEST_URI' => '/about?ref=home']);

        $this->assertSame('about', $request->uri());
    }

    public function testUriForRootIsEmptyString(): void
    {
        $request = new Request(['REQUEST_URI' => '/']);

        $this->assertSame('', $request->uri());
    }

    public function testMethodReadsServerBagAndDefaultsToGet(): void
    {
        $this->assertSame('POST', (new Request(['REQUEST_METHOD' => 'POST']))->method());
        $this->assertSame('GET', (new Request())->method());
    }

    public function testParamsStripTagsAndEscapeSpecialCharacters(): void
    {
        $request = new Request([], ['note' => '<b>Tom & "Jerry"</b>']);

        $this->assertSame('Tom &amp; &quot;Jerry&quot;', $request->params()->note);
    }

    public function testParamsAreDrawnFromQueryNotBody(): void
    {
        // Documents the existing quirk: params() reads the query bag ($_GET),
        // so a value present only in the body is not exposed here.
        $request = new Request([], [], ['id' => '5']);

        $this->assertObjectNotHasProperty('id', $request->params());
    }
}
