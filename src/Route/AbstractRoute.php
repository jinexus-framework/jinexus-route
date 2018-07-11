<?php
namespace JiNexus\Route\Route;

use JiNexus\Route\Base\AbstractBase;

/**
 * Class AbstractRoute
 * @package JiNexus\Route\Route
 */
abstract class AbstractRoute extends AbstractBase implements RouteInterface
{
    protected $routes = [];

    /**
     * AbstractRoute constructor
     * @param array $routes
     */
    public function __construct($routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * Retrieve the matching Route from URI
     *
     * @return array
     */
    public function getMatchRoute()
    {
        $uri = $this->getUri();

        $result = [];
        foreach ($this->routes as $name => $route) {
            if ($route['route'] == $uri) {
                $result[$name] = $route;
                break;
            }
        }

        return $result;
    }

    /**
     * Get URI
     *
     * @return string
     */
    public function getUri()
    {
        $basePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        $uri = substr($_SERVER['REQUEST_URI'], strlen($basePath));
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        $uri = '/' . trim($uri, '/');

        return $uri;
    }
}