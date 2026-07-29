<?php

declare(strict_types=1);

namespace JiNexus\Route\Test\Route\Factory;

use JiNexus\Route\Redirect\RedirectInterface;
use JiNexus\Route\Route\Factory\RouteFactory;
use JiNexus\Route\Route\RouteInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RouteFactory::class)]
final class RouteFactoryTest extends TestCase
{
    #[Test]
    public function it_builds_something_usable_as_a_route(): void
    {
        // build()'s return type is Route (PHP enforces it at the return), so
        // asserting instanceof on the result folds to always-true. Instead, we
        // check the produced class advertises the RouteInterface contract.
        RouteFactory::build()
            |> class_implements(...)
            |> (fn($x) => self::assertContains(RouteInterface::class, $x));
    }

    #[Test]
    public function it_wires_a_redirect_into_the_built_route(): void
    {
        self::assertInstanceOf(RedirectInterface::class, RouteFactory::build()->redirect);
    }

    #[Test]
    public function build_returns_a_fresh_instance_each_call(): void
    {
        self::assertNotSame(RouteFactory::build(), RouteFactory::build());
    }

    #[Test]
    public function built_instances_are_independent(): void
    {
        $one = RouteFactory::build();
        $two = RouteFactory::build();

        $one->setRoutes(['home' => ['route' => '/']]);

        self::assertSame([], $two->getRoutes());
        self::assertNotSame($one->redirect, $two->redirect);
    }
}
