<?php

namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Middleware\Interfaces\MiddlewareInterface;

/**
 * Runs a request through an ordered stack of middleware before a final handler.
 * The first middleware is the outermost layer and runs first.
 */
class Pipeline
{
    /**
     * @param list<MiddlewareInterface> $middleware ordered outermost-first
     */
    public function __construct(private array $middleware = [])
    {
    }

    /**
     * Run the request through every middleware, then the final handler.
     *
     * @param callable(Request): mixed $finalHandler invoked if all middleware delegate
     *
     * @return mixed
     */
    public function handle(Request $request, callable $finalHandler): mixed
    {
        $chain = array_reduce(
            array_reverse($this->middleware),
            fn (callable $next, MiddlewareInterface $middleware): callable
                => fn (Request $request): mixed => $middleware->process($request, $next),
            $finalHandler
        );

        return $chain($request);
    }
}
