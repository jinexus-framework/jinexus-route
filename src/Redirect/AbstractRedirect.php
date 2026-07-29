<?php

declare(strict_types=1);

namespace JiNexus\Route\Redirect;

use JiNexus\Route\Base\AbstractBase;
use JiNexus\Route\RouteException;

/**
 * Class AbstractRedirect
 * @package JiNexus\Route\Redirect
 */
abstract class AbstractRedirect extends AbstractBase implements RedirectInterface
{
    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * Redirect constructor
     */
    public function __construct() { }

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes = []): void
    {
        $this->routes = $routes;
    }

    /**
     * @param string $routeName
     * @param bool $permanent
     * @throws RouteException
     */
    public function toRoute(string $routeName = '', bool $permanent = false): void
    {
        if (! $routeName) {
            throw new RouteException('Route name must be provided');
        }

        if (! array_key_exists($routeName, $this->routes)) {
            throw new RouteException('Route "' . $routeName . '" not found');
        }

        $route = $this->routes[$routeName];

        if ($this->headersSent() === false)
        {
            $this->sendHeader('Location: ' . $route['route'], true, $permanent ? 301 : 302);
        }

        $this->terminate();
    }

    /**
     * @return bool
     */
    protected function headersSent(): bool
    {
        return headers_sent();
    }

    /**
     * @param string $header
     * @param bool $replace
     * @param int $statusCode
     * @return void
     */
    protected function sendHeader(string $header, bool $replace, int $statusCode): void
    {
        header($header, $replace, $statusCode);
    }

    /**
     * Isolated behind a seam so tests can stub it; exit() cannot be executed
     * under test without taking the test process down with it.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    protected function terminate(): void
    {
        exit();
    }
}
