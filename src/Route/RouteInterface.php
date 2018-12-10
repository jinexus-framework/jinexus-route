<?php
namespace JiNexus\Route\Route;

use JiNexus\Route\Base\BaseInterface;
use JiNexus\Route\Exception;
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
     * @param array $routes
     * @param string $uri
     * @return array
     */
    public function getMatchRoute($routes = [], $uri = '');

    /**
     * @return RedirectInterface
     */
    public function getRedirect();

    /**
     * @return array
     */
    public function getRoutes();

    /**
     * @param array $routes
     */
    public function setRoutes($routes = []);

    /**
     * @param string $routeName
     * @param array $routes
     * @return string
     * @throws Exception
     */
    public function getRouteUri($routeName = '', $routes = []);

    /**
     * Get URI
     *
     * @return string
     */
    public function getUri();
}