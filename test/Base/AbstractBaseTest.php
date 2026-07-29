<?php

declare(strict_types=1);

namespace JiNexus\Route\Test\Base;

use JiNexus\Route\Base\AbstractBase;
use JiNexus\Route\RouteException;
use JiNexus\Route\Test\Fixture\BaseDouble;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractBase::class)]
final class AbstractBaseTest extends TestCase
{
    private BaseDouble $base;

    protected function setUp(): void
    {
        $this->base = new BaseDouble();
    }

    #[Test]
    public function magic_setter_assigns_a_public_property_and_is_fluent(): void
    {
        $result = $this->base->setName('jinexus');

        self::assertSame($this->base, $result);
        self::assertSame('jinexus', $this->base->name);
    }

    #[Test]
    public function magic_getter_reads_a_public_property(): void
    {
        $this->base->name = 'framework';

        self::assertSame('framework', $this->base->getName());
    }

    #[Test]
    public function magic_setter_with_no_argument_assigns_null(): void
    {
        $this->base->setName('something');
        $this->base->setName();

        self::assertNull($this->base->name);
    }

    #[Test]
    public function it_refuses_to_read_a_non_public_property(): void
    {
        // 'secret' exists but is protected, so __call() must not expose it
        // and instead falls through to the "not implemented" guard.
        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote(BaseDouble::class . '::secret', '/') . '/');

        //noinspection PhpUndefinedMethodInspection
        $this->base->getSecret();
    }

    #[Test]
    public function it_throws_for_a_getter_on_an_unknown_property(): void
    {
        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Not implemented: ' . BaseDouble::class . '::unknown', '/') . '/');

        //noinspection PhpUndefinedMethodInspection
        $this->base->getUnknown();
    }

    #[Test]
    public function it_throws_for_a_completely_unknown_method(): void
    {
        // Not a get/set prefix, so the property name is left untouched.
        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Not implemented: ' . BaseDouble::class . '::doSomething', '/') . '/');

        //noinspection PhpUndefinedMethodInspection
        $this->base->doSomething();
    }
}
