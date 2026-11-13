<?php

namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Middleware\Interfaces\MiddlewareInterface;

/**
 * Serves a 503 maintenance page when the root `maintenance` file has
 * "maintenance": true, otherwise hands off to the next handler.
 */
class MaintenanceMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): mixed
    {
        $file = BASE_PATH . '/maintenance';

        if (file_exists($file)) {
            $data = json_decode((string) file_get_contents($file), true);

            if (is_array($data) && !empty($data['maintenance']) && !empty($data['template'])) {
                http_response_code((int) ($data['status'] ?? 503));

                return include BASE_PATH . "/resources/views/{$data['template']}";
            }
        }

        return $next($request);
    }
}
