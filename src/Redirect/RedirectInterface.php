<?php
namespace JiNexus\Route\Redirect;

use JiNexus\Route\Base\BaseInterface;
use JiNexus\Route\Exception;

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
    public function setRoutes($routes = []);

    /**
     * @param string $routeName
     * @param bool $permanent
     * @throws Exception
     */
    public function toRoute($routeName = '', $permanent = false);
}