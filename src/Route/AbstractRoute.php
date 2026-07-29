<?php

declare(strict_types=1);

namespace JiNexus\Route\Route;

use JiNexus\Route\Base\AbstractBase;
use JiNexus\Route\RouteException;
use JiNexus\Route\Redirect\RedirectInterface;

/**
 * Class AbstractRoute
 * @package JiNexus\Route\Route
 */
abstract class AbstractRoute extends AbstractBase implements RouteInterface
{
    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * @var RedirectInterface
     */
    public RedirectInterface $redirect {
        get {
            return $this->redirect;
        }
    }

    /**
     * AbstractRoute constructor.
     * @param RedirectInterface $redirect
     */
    public function __construct(RedirectInterface $redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * Retrieve the matching Route from URI
     *
     * Matches against $routes when one is supplied, otherwise against the routes
     * registered through setRoutes(). Falls back to the current request URI when
     * $uri is omitted.
     *
     * @param array $routes
     * @param string $uri
     * @return array
     */
    public function getMatchRoute(array $routes = [], string $uri = ''): array
    {
        if (! $uri) {
            $uri = $this->getUri();
        }

        if (! $routes) {
            $routes = $this->routes;
        }

        $result = [];
        foreach ($routes as $name => $route) {
            if ($route['route'] == $uri) {
                $result[$name] = $route;
                break;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes = []): void
    {
        $this->routes = $routes;
    }

    /**
     * Resolve a route name to its URI
     *
     * It looks the name up in $routes when one is supplied, otherwise in the routes
     * registered through setRoutes(). Only the first slash-separated segment of
     * the name is used.
     *
     * @param string $routeName
     * @param array $routes
     * @return string
     * @throws RouteException
     */
    public function getRouteUri(string $routeName = '', array $routes = []): string
    {
        if (! $routes) {
            $routes = $this->routes;
        }

        $explodeRouteName = explode('/', $routeName);

        if (array_key_exists(current($explodeRouteName), $routes)) {
            return $routes[current($explodeRouteName)]['route'];
        }
        else {
            throw new RouteException('Route "' . $routeName . '" not found');
        }
    }

    /**
     * Get URI
     *
     * @return string
     */
    public function getUri(): string
    {
        $basePath = explode('/', $_SERVER['SCRIPT_NAME'])
                |> (fn($x) => array_slice($x, 0, -1))
                |> (fn($x) => implode('/', $x) . '/');
        $uri = substr($_SERVER['REQUEST_URI'], strlen($basePath));
        if (str_contains($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        return '/' . trim($uri, '/');
    }
}
