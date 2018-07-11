<?php
namespace JiNexus\Route\Route;

use JiNexus\Route\Base\BaseInterface;

/**
 * Interface RouteInterface
 * @package JiNexus\Route\Route
 */
interface RouteInterface extends BaseInterface
{
    /**
     * RouteInterface constructor
     */
    public function __construct();

    /**
     * Retrieve the matching Route from URI
     *
     * @return array
     */
    public function getMatchRoute();

    /**
     * Get URI
     *
     * @return string
     */
    public function getUri();
}