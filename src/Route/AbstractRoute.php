<?php
namespace JiNexus\Route\Route;

use JiNexus\Route\Base\AbstractBase;
use JiNexus\Route\Exception;
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
    protected $routes = [];

    /**
     * @var RedirectInterface
     */
    protected $redirect;

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
     * @param array $routes
     * @param string $uri
     * @return array
     */
    public function getMatchRoute($routes = [], $uri = '')
    {
        if (! $uri) {
            $uri = $this->getUri();
        }

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
     * @return RedirectInterface
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param array $routes
     */
    public function setRoutes($routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * @param string $routeName
     * @param array $routes
     * @return string
     * @throws Exception
     */
    public function getRouteUri($routeName = '', $routes = [])
    {
        $explodeRouteName = explode('/', $routeName);

        if (array_key_exists(current($explodeRouteName), $routes)) {
            return $routes[current($explodeRouteName)]['route'];
        }
        else {
            throw new Exception('Route "' . $routeName . '" not found');
        }
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