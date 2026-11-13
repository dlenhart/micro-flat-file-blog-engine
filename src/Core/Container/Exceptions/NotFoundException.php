<?php

namespace App\Core\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Thrown when no container entry exists for the requested identifier.
 */
class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
