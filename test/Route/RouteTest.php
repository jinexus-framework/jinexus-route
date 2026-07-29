<?php

declare(strict_types=1);

namespace JiNexus\Route\Test\Route;

use JiNexus\Route\Redirect\Redirect;
use JiNexus\Route\Redirect\RedirectInterface;
use JiNexus\Route\Route\AbstractRoute;
use JiNexus\Route\Route\RouteInterface;
use JiNexus\Route\RouteException;
use JiNexus\Route\Test\Fixture\RouteDouble;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractRoute::class)]
final class RouteTest extends TestCase
{
    private Redirect $redirect;

    private RouteDouble $route;

    private array $server;

    protected function setUp(): void
    {
        // getUri() reads $_SERVER directly and has no injection seam the way
        // Http\Request does, so the super-global is snapshotted and restored.
        $this->server = $_SERVER;

        // The redirect stays a real collaborator; no magic call is made on it.
        $this->redirect = new Redirect();
        $this->route = new RouteDouble($this->redirect);
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
    }

    #[Test]
    public function it_is_a_route_interface(): void
    {
        // Assert on the class's interface list rather than the (already typed)
        // instance, so the assertion reflects a real contract instead of
        // folding to a compile-time constant.
        self::assertContains(RouteInterface::class, class_implements(RouteDouble::class));
    }

    #[Test]
    public function it_exposes_the_redirect_it_was_constructed_with(): void
    {
        self::assertSame($this->redirect, $this->route->redirect);
    }

    #[Test]
    public function the_exposed_redirect_is_a_redirect_interface(): void
    {
        self::assertInstanceOf(RedirectInterface::class, $this->route->redirect);
    }

    #[Test]
    public function the_redirect_is_reachable_through_the_magic_getter(): void
    {
        // The hooked property is public, so AbstractBase::__call() reaches it.
        self::assertSame($this->redirect, $this->route->getRedirect());
    }

    #[Test]
    public function the_redirect_can_be_replaced_through_the_magic_setter(): void
    {
        $replacement = new Redirect();

        $result = $this->route->setRedirect($replacement);

        self::assertSame($this->route, $result);
        self::assertSame($replacement, $this->route->redirect);
    }

    #[Test]
    public function it_starts_with_no_registered_routes(): void
    {
        self::assertSame([], $this->route->getRoutes());
    }

    #[Test]
    public function set_routes_replaces_the_whole_array(): void
    {
        $this->route->setRoutes(['home' => ['route' => '/']]);
        $this->route->setRoutes(['about' => ['route' => '/about-us']]);

        self::assertSame(['about' => ['route' => '/about-us']], $this->route->getRoutes());
    }

    #[Test]
    public function set_routes_with_no_argument_resets_to_empty(): void
    {
        $this->route->setRoutes(['home' => ['route' => '/']]);
        $this->route->setRoutes();

        self::assertSame([], $this->route->getRoutes());
    }

    #[Test]
    public function get_match_route_returns_the_whole_entry_keyed_by_its_name(): void
    {
        $this->route->setRoutes([
            'home'  => ['route' => '/', 'controller' => 'HomeController'],
            'about' => ['route' => '/about-us', 'controller' => 'AboutController'],
        ]);

        self::assertSame(
            ['about' => ['route' => '/about-us', 'controller' => 'AboutController']],
            $this->route->getMatchRoute([], '/about-us'),
        );
    }

    #[Test]
    public function get_match_route_matches_the_root_route(): void
    {
        $this->route->setRoutes(['home' => ['route' => '/']]);

        self::assertSame(['home' => ['route' => '/']], $this->route->getMatchRoute([], '/'));
    }

    #[Test]
    public function get_match_route_returns_an_empty_array_when_nothing_matches(): void
    {
        $this->route->setRoutes(['home' => ['route' => '/']]);

        self::assertSame([], $this->route->getMatchRoute([], '/does-not-exist'));
        self::assertSame([], new RouteDouble(new Redirect())->getMatchRoute([], '/'));
    }

    #[Test]
    public function get_match_route_stops_at_the_first_match(): void
    {
        // Two names share one URI; the loop breaks, so only the first is returned.
        $this->route->setRoutes([
            'first'  => ['route' => '/duplicate'],
            'second' => ['route' => '/duplicate'],
        ]);

        self::assertSame(['first' => ['route' => '/duplicate']], $this->route->getMatchRoute([], '/duplicate'));
    }

    #[Test]
    public function get_match_route_matches_against_its_routes_argument(): void
    {
        // Nothing is registered, so the argument is the only source of routes.
        self::assertSame(
            ['about' => ['route' => '/about-us']],
            $this->route->getMatchRoute(['about' => ['route' => '/about-us']], '/about-us'),
        );
    }

    #[Test]
    public function get_match_route_prefers_its_routes_argument_over_the_registered_routes(): void
    {
        $this->route->setRoutes(['registered' => ['route' => '/shared']]);

        self::assertSame(
            ['supplied' => ['route' => '/shared']],
            $this->route->getMatchRoute(['supplied' => ['route' => '/shared']], '/shared'),
        );
    }

    #[Test]
    public function get_match_route_falls_back_to_the_registered_routes_for_an_empty_argument(): void
    {
        $this->route->setRoutes(['about' => ['route' => '/about-us']]);

        self::assertSame(['about' => ['route' => '/about-us']], $this->route->getMatchRoute([], '/about-us'));
    }

    #[Test]
    public function get_match_route_falls_back_to_the_current_uri(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/app/public/index.php';
        $_SERVER['REQUEST_URI'] = '/app/public/about-us';

        $this->route->setRoutes(['about' => ['route' => '/about-us']]);

        self::assertSame(['about' => ['route' => '/about-us']], $this->route->getMatchRoute());
    }

    /**
     * @throws RouteException
     */
    #[Test]
    public function get_route_uri_returns_the_uri_for_a_route_name(): void
    {
        $routes = [
            'home' => ['route' => '/'],
            'user' => ['route' => '/user'],
        ];

        self::assertSame('/', $this->route->getRouteUri('home', $routes));
        self::assertSame('/user', $this->route->getRouteUri('user', $routes));
    }

    /**
     * @throws RouteException
     */
    #[Test]
    public function get_route_uri_resolves_only_the_first_segment_of_the_name(): void
    {
        // 'user/profile/42' is split on '/', and only 'user' is looked up.
        self::assertSame('/user', $this->route->getRouteUri('user/profile/42', ['user' => ['route' => '/user']]));
    }

    #[Test]
    public function get_route_uri_throws_for_an_unknown_name(): void
    {
        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Route "missing" not found', '/') . '/');

        $this->route->getRouteUri('missing', ['home' => ['route' => '/']]);
    }

    #[Test]
    public function get_route_uri_throws_for_an_empty_name(): void
    {
        // explode() on '' yields [''], which is never a registered key.
        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Route "" not found', '/') . '/');

        $this->route->getRouteUri('', ['home' => ['route' => '/']]);
    }

    /**
     * @throws RouteException
     */
    #[Test]
    public function get_route_uri_falls_back_to_the_registered_routes(): void
    {
        // Symmetrical with getMatchRoute(): omitting the argument resolves
        // against whatever setRoutes() registered.
        $this->route->setRoutes(['home' => ['route' => '/']]);

        self::assertSame('/', $this->route->getRouteUri('home'));
    }

    /**
     * @throws RouteException
     */
    #[Test]
    public function get_route_uri_prefers_its_routes_argument_over_the_registered_routes(): void
    {
        $this->route->setRoutes(['home' => ['route' => '/registered']]);

        self::assertSame('/supplied', $this->route->getRouteUri('home', ['home' => ['route' => '/supplied']]));
    }

    #[Test]
    public function get_route_uri_throws_when_nothing_is_registered_and_nothing_is_supplied(): void
    {
        $this->expectException(RouteException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Route "home" not found', '/') . '/');

        $this->route->getRouteUri('home');
    }

    #[Test]
    public function get_uri_strips_the_directory_the_script_lives_in(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/app/public/index.php';

        $_SERVER['REQUEST_URI'] = '/app/public/user/42';
        self::assertSame('/user/42', $this->route->getUri());

        $_SERVER['REQUEST_URI'] = '/app/public/';
        self::assertSame('/', $this->route->getUri());
    }

    #[Test]
    public function get_uri_returns_a_single_slash_for_the_document_root(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/';

        self::assertSame('/', $this->route->getUri());
    }

    #[Test]
    public function get_uri_normalizes_surrounding_slashes(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $_SERVER['REQUEST_URI'] = '/about-us';
        self::assertSame('/about-us', $this->route->getUri());

        $_SERVER['REQUEST_URI'] = '/about-us/';
        self::assertSame('/about-us', $this->route->getUri());

        $_SERVER['REQUEST_URI'] = '/user/profile/42';
        self::assertSame('/user/profile/42', $this->route->getUri());
    }

    #[Test]
    public function get_uri_drops_the_query_string(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $_SERVER['REQUEST_URI'] = '/search?q=jinexus';
        self::assertSame('/search', $this->route->getUri());

        // A slash inside the query must not survive as a path segment.
        $_SERVER['REQUEST_URI'] = '/search?q=a/b';
        self::assertSame('/search', $this->route->getUri());
    }

    #[Test]
    public function get_uri_drops_a_query_string_hanging_off_a_sub_directory_root(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/app/public/index.php';
        $_SERVER['REQUEST_URI'] = '/app/public/?page=2';

        self::assertSame('/', $this->route->getUri());
    }
}
