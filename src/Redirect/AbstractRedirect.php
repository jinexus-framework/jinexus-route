<?php
namespace JiNexus\Route\Redirect;

use JiNexus\Route\Base\AbstractBase;
use JiNexus\Route\Exception;

/**
 * Class AbstractRedirect
 * @package JiNexus\Route\Redirect
 */
abstract class AbstractRedirect extends AbstractBase implements RedirectInterface
{
    /**
     * @var array
     */
    protected $routes = [];

    /**
     * Redirect constructor
     */
    public function __construct() { }

    /**
     * @param array $routes
     */
    public function setRoutes($routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * @param string $routeName
     * @param bool $permanent
     * @throws Exception
     */
    public function toRoute($routeName = '', $permanent = false)
    {
        if (! $routeName) {
            throw new Exception('Route name must be provided');
        }

        if (! array_key_exists($routeName, $this->routes)) {
            throw new Exception('Route "' . $routeName . '" not found');
        }

        $route = $this->routes[$routeName];

        if (headers_sent() === false)
        {
            header('Location: ' . $route['route'], true, $permanent ? 301 : 302);
        }

        exit();
    }
}