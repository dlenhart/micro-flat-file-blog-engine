<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Http\Request;
use App\Core\Middleware\Interfaces\MiddlewareInterface;
use App\Core\Middleware\Pipeline;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the middleware pipeline (step 4, piece c).
 */
final class PipelineTest extends TestCase
{
    public function testRunsTheFinalHandlerWhenAllMiddlewareDelegate(): void
    {
        $pipeline = new Pipeline([$this->passThrough()]);

        $result = $pipeline->handle(new Request(), fn (Request $request) => 'handled');

        $this->assertSame('handled', $result);
    }

    public function testMiddlewareCanShortCircuitTheFinalHandler(): void
    {
        $reachedHandler = false;
        $pipeline = new Pipeline([$this->shortCircuit('blocked')]);

        $result = $pipeline->handle(
            new Request(),
            function (Request $request) use (&$reachedHandler) {
                $reachedHandler = true;
                return 'handled';
            }
        );

        $this->assertSame('blocked', $result);
        $this->assertFalse($reachedHandler, 'Short-circuit must not reach the final handler');
    }

    public function testMiddlewareRunOutermostFirst(): void
    {
        /** @var \ArrayObject<int, string> $order */
        $order = new \ArrayObject();

        $pipeline = new Pipeline([$this->recorder('first', $order), $this->recorder('second', $order)]);
        $pipeline->handle(new Request(), function (Request $request) use ($order) {
            $order->append('handler');
            return null;
        });

        $this->assertSame(['first', 'second', 'handler'], $order->getArrayCopy());
    }

    private function passThrough(): MiddlewareInterface
    {
        return new class () implements MiddlewareInterface {
            public function process(Request $request, callable $next): mixed
            {
                return $next($request);
            }
        };
    }

    private function shortCircuit(string $response): MiddlewareInterface
    {
        return new class ($response) implements MiddlewareInterface {
            public function __construct(private string $response)
            {
            }

            public function process(Request $request, callable $next): mixed
            {
                return $this->response;
            }
        };
    }

    /**
     * @param \ArrayObject<int, string> $order
     */
    private function recorder(string $tag, \ArrayObject $order): MiddlewareInterface
    {
        return new class ($tag, $order) implements MiddlewareInterface {
            /**
             * @param \ArrayObject<int, string> $order
             */
            public function __construct(private string $tag, private \ArrayObject $order)
            {
            }

            public function process(Request $request, callable $next): mixed
            {
                $this->order->append($this->tag);
                return $next($request);
            }
        };
    }
}
