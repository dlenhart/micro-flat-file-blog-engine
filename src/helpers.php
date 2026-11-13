<?php

use App\Core\Http\Response;
use App\Core\Http\Request;

if (! defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (! function_exists('view')) {
    /**
     * Return a view
     *
     * @param string $name - name of view
     * @param array  $data - optional data to inject
     *
     * @return mixed
     */
    function view($name, $data = [])
    {
        extract($data);
        return include BASE_PATH . "/resources/views/{$name}.view.php";
    }
}

if (! function_exists('notFound')) {
    /**
     * Return a not found view
     *
     * @param string $message - message for view
     *
     * @return mixed
     */
    function notFound($message, $status = 404)
    {
        http_response_code($status);
        return view('notfound', ['message' => $message]);
    }
}

if (! function_exists('json')) {
    /**
     * Return json response
     *
     * @param array $data   - array to convert
     * @param int   $status - http status code
     *
     * @return never
     */
    function json($data = [], $status = 200)
    {
        Response::toJson($data, $status);
    }
}

if (! function_exists('arrayGet')) {
    /**
     * Recursive tree search
     *
     * @param string $query - search term
     * @param array  $array - array to search in
     * @param int    $depth - how deep
     *
     * @return mixed
     */
    function arrayGet($query, $array, $depth = 0)
    {
        if (is_string($query)) {
            $query = explode(".", $query);
        }

        $search = $query[$depth];

        if (!isset($array[$search])) {
            return false;
        }

        if ($depth == count($query) - 1) {
            return $array[$search];
        }

        return arrayGet($query, $array[$search], ++$depth);
    }
}

if (! function_exists('config')) {
    /**
     * Return configuration value
     *
     * @param string $key - search term
     *
     * @return mixed
     */
    function config($key)
    {
        static $config = null;

        if ($config === null) {
            $config = include BASE_PATH . '/config.php';
        }

        return arrayGet($key, $config);
    }
}

if (! function_exists('app')) {
    /**
     * The shared application container, with the core services already bound.
     *
     * @return \App\Core\Container\Container
     */
    function app()
    {
        static $container = null;

        if ($container === null) {
            $container = new \App\Core\Container\Container();

            $container->singleton(
                \App\Core\Log\Logger::class,
                fn () => new \App\Core\Log\Logger()
            );
            $container->singleton(
                \App\Core\Http\Request::class,
                fn () => \App\Core\Http\Request::capture()
            );
            $container->singleton(
                \Psr\Log\LoggerInterface::class,
                fn ($c) => $c->get(\App\Core\Log\Logger::class)
            );
            $container->singleton(
                \App\Core\Blog\Interfaces\PostRepositoryInterface::class,
                fn ($c) => new \App\Core\Blog\Repositories\PostRepository(
                    config('app.posts_dir'),
                    flatten(config('app.allowed_post_file_types')),
                    config('app.post_preview_text_limit'),
                    $c->get(\Psr\Log\LoggerInterface::class)
                )
            );
        }

        return $container;
    }
}

if (! function_exists('logger')) {
    /**
     * Shared PSR-3 logger instance, resolved from the container.
     *
     * @return \App\Core\Log\Logger
     */
    function logger()
    {
        return app()->get(\App\Core\Log\Logger::class);
    }
}

if (! function_exists('dd')) {
    /**
     * Dump and Dieeee!
     *
     * @param array $data - data to dump
     *
     * @return void
     */
    function dd($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';

        die();
    }
}

if (! function_exists('request')) {
    /**
     * Shared Request instance, resolved from the container (captured once from
     * the superglobals).
     *
     * @return Request
     */
    function request()
    {
        return app()->get(Request::class);
    }
}

if (! function_exists('params')) {
    /**
     * Sanitized query parameters from the current request.
     *
     * @return \stdClass
     */
    function params()
    {
        return request()->params();
    }
}

if (! function_exists('redirect')) {
    /**
     * Redirect
     *
     * @param string $to      - page to redirect
     * @param int    $status  - http status code
     * @param array  $headers - optional headers
     *
     * @return never
     */
    function redirect($to = null, $status = 302, $headers = [])
    {
        Response::redirect($to, $status, $headers);
    }
}

if (! function_exists('flatten')) {
    /**
     * Flatten array
     *
     * @param array $array - array to flatten
     *
     * @return array
     */
    function flatten(array $array)
    {
        $return = array();

        array_walk_recursive(
            $array,
            function ($a) use (&$return) {
                $return[] = $a;
            }
        );
        return $return;
    }
}
