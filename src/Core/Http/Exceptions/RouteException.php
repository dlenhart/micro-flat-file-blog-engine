<?php

namespace App\Core\Http\Exceptions;

use RuntimeException;

/**
 * Thrown when a route cannot be dispatched, e.g. the mapped controller method
 * does not exist.
 */
class RouteException extends RuntimeException
{
}
