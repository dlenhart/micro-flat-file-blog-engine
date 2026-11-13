<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Container\Container;
use App\Core\Container\Exceptions\ContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Unit tests for the PSR-11 container (step 4, piece c).
 */
final class ContainerTest extends TestCase
{
    public function testBindReturnsAFreshValueEachTime(): void
    {
        $container = new Container();
        $container->bind('clock', fn () => new \stdClass());

        $this->assertNotSame($container->get('clock'), $container->get('clock'));
    }

    public function testSingletonReturnsTheSameInstance(): void
    {
        $container = new Container();
        $container->singleton('clock', fn () => new \stdClass());

        $this->assertSame($container->get('clock'), $container->get('clock'));
    }

    public function testInstanceReturnsTheRegisteredObject(): void
    {
        $container = new Container();
        $object = new \stdClass();
        $container->instance('config', $object);

        $this->assertSame($object, $container->get('config'));
    }

    public function testAutowiresAClassWithoutAConstructor(): void
    {
        $container = new Container();

        $this->assertInstanceOf(Alpha::class, $container->get(Alpha::class));
    }

    public function testAutowiresNestedClassDependencies(): void
    {
        $container = new Container();

        $beta = $container->get(Beta::class);

        $this->assertInstanceOf(Beta::class, $beta);
        $this->assertInstanceOf(Alpha::class, $beta->alpha);
    }

    public function testThrowsNotFoundForAnUnknownNonClassIdentifier(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);

        (new Container())->get('does.not.exist');
    }

    public function testHasReflectsBindingsInstancesAndConcreteClasses(): void
    {
        $container = new Container();
        $container->bind('bound', fn () => new \stdClass());

        $this->assertTrue($container->has('bound'));
        $this->assertTrue($container->has(Alpha::class));
        $this->assertFalse($container->has('does.not.exist'));
    }

    public function testThrowsOnACircularDependency(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Circular dependency detected');

        (new Container())->get(CycleA::class);
    }
}

class Alpha
{
}

class Beta
{
    public function __construct(public Alpha $alpha)
    {
    }
}

class CycleA
{
    public function __construct(public CycleB $b)
    {
    }
}

class CycleB
{
    public function __construct(public CycleA $a)
    {
    }
}
