<?php

namespace App\Core\Container;

use App\Core\Container\Exceptions\ContainerException;
use App\Core\Container\Exceptions\NotFoundException;
use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

/**
 * PSR-11 container with constructor autowiring.
 *
 * Register entries as transient factories (bind), shared factories (singleton),
 * or ready-made objects (instance). Unknown concrete classes are built by
 * reflection, resolving their constructor's class dependencies in turn.
 */
class Container implements ContainerInterface
{
    /**
     * Resolved shared instances, keyed by identifier.
     *
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * Registered factories, keyed by identifier.
     *
     * @var array<string, array{factory: Closure, shared: bool}>
     */
    private array $bindings = [];

    /**
     * Identifiers currently being built; used to spot dependency cycles.
     *
     * @var array<string, true>
     */
    private array $resolving = [];

    /**
     * Register a transient factory: a fresh value on every resolution.
     */
    public function bind(string $id, Closure $factory): void
    {
        $this->bindings[$id] = ['factory' => $factory, 'shared' => false];
    }

    /**
     * Register a shared factory: resolved once, then cached.
     */
    public function singleton(string $id, Closure $factory): void
    {
        $this->bindings[$id] = ['factory' => $factory, 'shared' => true];
    }

    /**
     * Register an already-constructed object.
     */
    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $object = ($this->bindings[$id]['factory'])($this);

            if ($this->bindings[$id]['shared']) {
                $this->instances[$id] = $object;
            }

            return $object;
        }

        if (class_exists($id)) {
            return $this->build($id);
        }

        throw new NotFoundException("No container entry found for '{$id}'.");
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances)
            || isset($this->bindings[$id])
            || class_exists($id);
    }

    /**
     * Autowire a concrete class, recursively resolving its dependencies.
     *
     * @param class-string $class
     */
    private function build(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new ContainerException("Class '{$class}' is not instantiable.");
        }

        if (isset($this->resolving[$class])) {
            $chain = implode(' -> ', [...array_keys($this->resolving), $class]);

            throw new ContainerException("Circular dependency detected: {$chain}.");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $this->resolving[$class] = true;

        try {
            $arguments = [];

            foreach ($constructor->getParameters() as $parameter) {
                $type = $parameter->getType();

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $arguments[] = $this->get($type->getName());
                    continue;
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new ContainerException(
                    "Unable to autowire parameter \${$parameter->getName()} of '{$class}'."
                );
            }

            return $reflection->newInstanceArgs($arguments);
        } finally {
            unset($this->resolving[$class]);
        }
    }
}
