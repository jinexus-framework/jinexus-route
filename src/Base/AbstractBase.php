<?php

declare(strict_types=1);

namespace JiNexus\Route\Base;

use JiNexus\Route\RouteException;
use ReflectionException;

/**
 * Class AbstractBase
 * @package JiNexus\Route\Base
 */
abstract class AbstractBase implements BaseInterface
{
    /**
     * Base setters and getters
     *
     * @param $property
     * @param array $arguments
     * @return $this|mixed
     * @throws RouteException | ReflectionException
     */
    public function __call($property, array $arguments)
    {
        $action = substr($property, 0, 3);

        if ( $action == 'get' || $action == 'set' ) {
            $property = lcfirst(substr($property, 3));

            if ( property_exists($this, $property) ) {
                $reflection = new \ReflectionObject($this);

                if ( $reflection->getProperty($property)->isPublic() ) {
                    if ( $action == 'get' ) {
                        return $this->{$property};
                    } else {
                        $this->{$property} = $arguments ? $arguments[0] : null;

                        return $this;
                    }
                }
            }
        }

        throw new RouteException('Not implemented: ' . get_called_class() . '::' . $property);
    }
}
