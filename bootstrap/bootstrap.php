<?php

use App\Core\Http\Request;
use App\Core\Http\Router;
use App\Core\Middleware\MaintenanceMiddleware;
use App\Core\Middleware\Pipeline;

/* ----------------------------
|
|  Bootstrap
|
|  Load dependencies, then send the request through the middleware pipeline
|  to the router.
|
| ----------------------------*/

require_once __DIR__.'/../vendor/autoload.php';

$container = app();

$router = Router::load(BASE_PATH . '/routes/routes.php', $container);

$pipeline = new Pipeline([
    $container->get(MaintenanceMiddleware::class),
]);

$pipeline->handle(
    request(),
    fn (Request $request) => $router->dispatch($request)
);
