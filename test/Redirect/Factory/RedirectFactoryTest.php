<?php

declare(strict_types=1);

namespace JiNexus\Route\Test\Redirect\Factory;

use JiNexus\Route\Redirect\Factory\RedirectFactory;
use JiNexus\Route\Redirect\RedirectInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RedirectFactory::class)]
final class RedirectFactoryTest extends TestCase
{
    #[Test]
    public function it_builds_something_usable_as_a_redirect(): void
    {
        RedirectFactory::build()
            |> class_implements(...)
            |> (fn($x) => self::assertContains(RedirectInterface::class, $x));
    }

    #[Test]
    public function build_returns_a_fresh_instance_each_call(): void
    {
        self::assertNotSame(RedirectFactory::build(), RedirectFactory::build());
    }
}
