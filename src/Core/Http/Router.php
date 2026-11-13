<?php

namespace App\Core\Http;

use App\Core\Container\Container;
use App\Core\Http\Exceptions\RouteException;

class Router
{
    public $routes = [
        'GET'      =>   [],
        'POST'     =>   []
    ];

    /**
     * Container used to resolve controllers.
     */
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Load routes
     *
     * @param string    $file      - file to load
     * @param Container $container - container for resolving controllers
     *
     * @return self
     */
    public static function load($file, Container $container)
    {
        $router = new self($container);

        include $file;

        return $router;
    }

    /**
     * Set routes
     *
     * @param string $routes - set routes
     *
     * @return void
     */
    public function define($routes)
    {
        $this->routes = $routes;
    }

    /**
     * GET - Set GET route
     *
     * @param string $uri        - uri
     * @param string $controller - controller
     *
     * @return void
     */
    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    /**
     * POST - Set POST route
     *
     * @param string $uri        - uri
     * @param string $controller - controller
     *
     * @return void
     */
    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    /**
     * Determine is the requested route exists in our
     * routes array.
     *
     * @param string $uri  - uri to validate
     * @param string $type - http method to validate
     *
     * @return boolean
     */
    public function hasRoute(string $uri, string $type)
    {
        return array_key_exists($uri, $this->routes[$type]);
    }

    /**
     * Dispatch the resource described by the request.
     *
     * @param Request $request - incoming request
     *
     * @return mixed
     */
    public function dispatch(Request $request)
    {
        $uri = $request->uri();
        $type = $request->method();

        if (!isset($this->routes[$type])) {
            logger()->error("HTTP method not allowed: {$uri} - {$type}");

            return notFound('405 | METHOD NOT ALLOWED.', 405);
        }

        if ($this->hasRoute($uri, $type)) {
            return $this->dispatchAction($this->routes[$type][$uri]);
        }

        foreach ($this->routes[$type] as $route => $controller) {
            if (!str_contains($route, '{')) {
                continue;
            }

            if (preg_match($this->compile($route), $uri, $matches)) {
                return $this->dispatchAction($controller, array_slice($matches, 1));
            }
        }

        logger()->error("File not found requesting: {$uri} - {$type}");

        return notFound('404 | FILE NOT FOUND.', 404);
    }

    /**
     * Turn a route with {placeholders} into a matching regex.
     *
     * @param string $route - route pattern, e.g. "post/{post}"
     *
     * @return string
     */
    private function compile(string $route)
    {
        $pattern = preg_replace('/\{[A-Za-z_][A-Za-z0-9_]*\}/', '([^/]+)', $route);

        return '#^' . $pattern . '$#';
    }

    /**
     * Resolve the controller and call its method with any captured segments.
     *
     * @param string $controller - "Class@method" string
     * @param array  $params      - captured uri segments, passed as arguments
     *
     * @return mixed
     */
    protected function dispatchAction($controller, array $params = [])
    {
        [$class, $method] = explode('@', $controller);

        $instance = $this->container->get("App\\Controllers\\{$class}");

        if (! method_exists($instance, $method)) {
            throw new RouteException(
                "{$class} is unable to call the '{$method}' method!"
            );
        }

        return $instance->$method(...$params);
    }
}
