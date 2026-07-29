<?php

declare(strict_types=1);

namespace JiNexus\Route\Redirect;

use JiNexus\Route\Base\BaseInterface;
use JiNexus\Route\RouteException;

/**
 * Interface RedirectInterface
 * @package JiNexus\Route\Redirect
 */
interface RedirectInterface extends BaseInterface
{
    /**
     * Redirect constructor
     */
    public function __construct();

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes = []);

    /**
     * @param string $routeName
     * @param bool $permanent
     * @throws RouteException
     */
    public function toRoute(string $routeName = '', bool $permanent = false);
}
