<?php

namespace App\Core\Http;

use App\Core\Http\Interfaces\SanitizerInterface;

class HtmlSanitizer implements SanitizerInterface
{
    public function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags($value));
    }
}
