<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Http\HtmlSanitizer;
use App\Core\Http\Interfaces\SanitizerInterface;
use App\Core\Http\Request;
use PHPUnit\Framework\TestCase;

final class HtmlSanitizerTest extends TestCase
{
    public function testStripsTagsAndEscapesSpecialCharacters(): void
    {
        $sanitizer = new HtmlSanitizer();

        $this->assertSame('alert(&quot;x&quot;)', $sanitizer->sanitize('<script>alert("x")</script>'));
    }

    public function testRequestParamsUseTheInjectedSanitizer(): void
    {
        $request = new Request([], ['q' => 'raw'], [], new class () implements SanitizerInterface {
            public function sanitize(string $value): string
            {
                return 'sanitized:' . $value;
            }
        });

        $this->assertSame('sanitized:raw', $request->params()->q);
    }
}
