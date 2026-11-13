<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Container\Container;
use App\Core\Http\Exceptions\RouteException;
use App\Core\Http\Request;
use App\Core\Http\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private function router(object $controller): Router
    {
        $container = new Container();
        $container->instance('App\\Controllers\\FakeController', $controller);

        return new Router($container);
    }

    private function request(string $uri, string $method = 'GET'): Request
    {
        return new Request(['REQUEST_URI' => $uri, 'REQUEST_METHOD' => $method]);
    }

    private function fakeController(): object
    {
        return new class () {
            public ?string $received = null;

            public function home(): string
            {
                return 'home';
            }

            public function show(string $id): string
            {
                $this->received = $id;

                return "shown:{$id}";
            }
        };
    }

    public function testDispatchesAStaticRoute(): void
    {
        $controller = $this->fakeController();
        $router = $this->router($controller);
        $router->get('home', 'FakeController@home');

        $this->assertSame('home', $router->dispatch($this->request('/home')));
    }

    public function testDispatchesADynamicRouteAndPassesTheSegment(): void
    {
        $controller = $this->fakeController();
        $router = $this->router($controller);
        $router->get('post/{post}', 'FakeController@show');

        $result = $router->dispatch($this->request('/post/my-third_post'));

        $this->assertSame('shown:my-third_post', $result);
        $this->assertSame('my-third_post', $controller->received);
    }

    public function testStaticRouteTakesPrecedenceOverADynamicOne(): void
    {
        $controller = $this->fakeController();
        $router = $this->router($controller);
        $router->get('post/{post}', 'FakeController@show');
        $router->get('post/archive', 'FakeController@home');

        $this->assertSame('home', $router->dispatch($this->request('/post/archive')));
    }

    public function testThrowsWhenTheControllerMethodDoesNotExist(): void
    {
        $router = $this->router($this->fakeController());
        $router->get('missing', 'FakeController@nope');

        $this->expectException(RouteException::class);

        $router->dispatch($this->request('/missing'));
    }
}
