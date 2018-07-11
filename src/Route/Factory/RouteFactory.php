<?php
namespace JiNexus\Route\Route\Factory;

use JiNexus\Route\Factory\AbstractFactory;
use JiNexus\Route\Route\Route;

/**
 * Class RouteFactory
 * @package JiNexus\Route\Route\Factory
 */
class RouteFactory extends AbstractFactory
{
    /**
     * @param $routes
     * @return Route
     */
    public static function build($routes)
    {
        return new Route($routes);
    }
}
