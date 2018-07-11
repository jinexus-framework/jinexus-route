<?php
namespace JiNexus\Route\Base;

/**
 * Interface BaseInterface
 * @package JiNexus\Route\Base
 */
interface BaseInterface
{
    /**
     * Getters and Setters
     *
     * @param $property
     * @param array $arguments
     * @return mixed
     */
    public function __call($property, array $arguments);
}
