<?php

declare(strict_types=1);

namespace JiNexus\Route\Test\Redirect;

use JiNexus\Route\Redirect\AbstractRedirect;
use JiNexus\Route\Redirect\RedirectInterface;
use JiNexus\Route\RouteException;
use JiNexus\Route\Test\Fixture\RedirectDouble;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractRedirect::class)]
final class RedirectTest extends TestCase
{
    private RedirectDouble $redirect;

    protected function setUp(): void
    {
        $this->redirect = new RedirectDouble();
    }

    #[Test]
    public function it_is_a_redirect_interface(): void
    {
        // Assert on the class's interface list rather than the (already typed)
        // instance, so the assertion reflects a real contract instead of
        // folding to a compile-time constant.
        self::assertContains(RedirectInterface::class, class_implements(RedirectDouble::class));
    }

    #[Test]
    public function to_route_throws_when_no_route_name_is_given(): void
    {
        $this->redirect->setRoutes(['home' => ['route' => '/']]);

        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Route name must be provided', '/') . '/');

        $this->redirect->toRoute();
    }

    #[Test]
    public function to_route_throws_for_an_explicitly_empty_route_name(): void
    {
        // The empty string is falsy, so it hits the same guard as the default,
        // regardless of the $permanent flag.
        $this->redirect->setRoutes(['home' => ['route' => '/']]);

        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Route name must be provided', '/') . '/');

        $this->redirect->toRoute('', true);
    }

    #[Test]
    public function to_route_throws_for_a_route_name_that_is_not_registered(): void
    {
        $this->redirect->setRoutes(['home' => ['route' => '/']]);

        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Route "contact" not found', '/') . '/');

        $this->redirect->toRoute('contact');
    }

    #[Test]
    public function to_route_throws_when_no_routes_are_registered_at_all(): void
    {
        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Route "home" not found', '/') . '/');

        $this->redirect->toRoute('home');
    }

    #[Test]
    public function set_routes_with_no_argument_resets_to_empty(): void
    {
        // $routes is protected with no getter, so the reset is observed through
        // the lookup in toRoute() no longer finding a previously known name.
        $this->redirect->setRoutes(['about' => ['route' => '/about-us']]);
        $this->redirect->setRoutes();

        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Route "about" not found', '/') . '/');

        $this->redirect->toRoute('about');
    }

    /**
     * @throws RouteException
     */
    #[Test]
    public function to_route_sends_a_temporary_redirect_by_default(): void
    {
        // The seams in AbstractRedirect are what make this reachable: without
        // them toRoute() would exit() before the test could assert anything.
        $this->redirect->setRoutes(['about' => ['route' => '/about-us']]);

        $this->redirect->toRoute('about');

        self::assertSame(
            [['header' => 'Location: /about-us', 'replace' => true, 'statusCode' => 302]],
            $this->redirect->sentHeaders,
        );
    }

    /**
     * @throws RouteException
     */
    #[Test]
    public function to_route_sends_a_permanent_redirect_when_asked(): void
    {
        $this->redirect->setRoutes(['about' => ['route' => '/about-us']]);

        $this->redirect->toRoute('about', true);

        self::assertSame(
            [['header' => 'Location: /about-us', 'replace' => true, 'statusCode' => 301]],
            $this->redirect->sentHeaders,
        );
    }

    /**
     * @throws RouteException
     */
    #[Test]
    public function to_route_terminates_once_after_redirecting(): void
    {
        $this->redirect->setRoutes(['home' => ['route' => '/']]);

        $this->redirect->toRoute('home');

        self::assertSame(1, $this->redirect->terminateCount);
    }

    /**
     * @throws RouteException
     */
    #[Test]
    public function to_route_skips_the_header_when_headers_are_already_sent(): void
    {
        $this->redirect->setRoutes(['home' => ['route' => '/']]);
        $this->redirect->headersAlreadySent = true;

        $this->redirect->toRoute('home');

        self::assertSame([], $this->redirect->sentHeaders);
        // Terminating is not conditional on the header having gone out.
        self::assertSame(1, $this->redirect->terminateCount);
    }

    #[Test]
    public function to_route_has_no_side_effect_when_a_guard_rejects_the_route(): void
    {
        // Both guards must short-circuit before any header or termination.
        try {
            $this->redirect->toRoute('contact');
        } catch (RouteException) {
            // Expected: nothing is registered under that name.
        }

        self::assertSame([], $this->redirect->sentHeaders);
        self::assertSame(0, $this->redirect->terminateCount);
    }

    #[Test]
    public function it_refuses_to_expose_its_routes_through_a_magic_getter(): void
    {
        // $routes is declared but protected, so __call() must refuse it. The
        // double is used here rather than the subject under test only because it
        // carries the @method tag that keeps getRoutes() statically resolvable;
        // __call() reports get_called_class(), hence the fixture in the message.
        $redirect = new RedirectDouble();
        $redirect->setRoutes(['home' => ['route' => '/']]);

        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote(RedirectDouble::class . '::routes', '/') . '/');

        $redirect->getRoutes();
    }
}
