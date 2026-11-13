<?php

namespace App\Core\Http\Interfaces;

interface SanitizerInterface
{
    public function sanitize(string $value): string;
}
