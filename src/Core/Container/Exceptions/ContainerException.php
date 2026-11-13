<?php

namespace App\Core\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Thrown when an entry exists but cannot be resolved (e.g. unsatisfiable
 * autowiring of a constructor dependency).
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
