<?php
namespace JiNexus\Route\Route\Factory;

use JiNexus\Route\Factory\AbstractFactory;
use JiNexus\Route\Redirect\Factory\RedirectFactory;
use JiNexus\Route\Route\Route;

/**
 * Class RouteFactory
 * @package JiNexus\Route\Route\Factory
 */
class RouteFactory extends AbstractFactory
{
    /**
     * @return Route
     */
    public static function build()
    {
        $redirect = RedirectFactory::build();

        return new Route($redirect);
    }
}
