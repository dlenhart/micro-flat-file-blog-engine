<?php

namespace App\Core\Middleware\Interfaces;

use App\Core\Http\Request;

/**
 * A single stage in the request pipeline.
 *
 * Like PSR-15, but built around this framework's own Request rather than PSR-7.
 * A middleware either handles the request itself or hands off to $next.
 */
interface MiddlewareInterface
{
    /**
     * @param Request                  $request the incoming request
     * @param callable(Request): mixed $next    the next handler in the chain
     *
     * @return mixed the response produced by this middleware or downstream
     */
    public function process(Request $request, callable $next): mixed;
}
