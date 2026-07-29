<?php

declare(strict_types=1);

namespace JiNexus\Route\Route;

use JiNexus\Route\Base\BaseInterface;
use JiNexus\Route\RouteException;
use JiNexus\Route\Redirect\RedirectInterface;

/**
 * Interface RouteInterface
 * @package JiNexus\Route\Route
 */
interface RouteInterface extends BaseInterface
{
    /**
     * AbstractRoute constructor.
     * @param RedirectInterface $redirect
     */
    public function __construct(RedirectInterface $redirect);

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
    public function getMatchRoute(array $routes = [], string $uri = ''): array;

    public RedirectInterface $redirect {
        get;
    }

    /**
     * @return array
     */
    public function getRoutes(): array;

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes = []);

    /**
     * Resolve a route name to its URI
     *
     * Looks the name up in $routes when one is supplied, otherwise in the routes
     * registered through setRoutes(). Only the first slash-separated segment of
     * the name is used.
     *
     * @param string $routeName
     * @param array $routes
     * @return string
     * @throws RouteException
     */
    public function getRouteUri(string $routeName = '', array $routes = []): string;

    /**
     * Get URI
     *
     * @return string
     */
    public function getUri(): string;
}
