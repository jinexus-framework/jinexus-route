<?php
namespace JiNexus\Route\Base;

use JiNexus\Route\Exception;

/**
 * Class AbstractBase
 * @package JiNexus\Route\Base
 */
abstract class AbstractBase implements BaseInterface
{
    /**
     * Base setters and getters
     *
     * @param $name
     * @param array $arguments
     * @return $this|mixed
     * @throws Exception
     */
    public function __call($name, array $arguments)
    {
        $action = substr($name, 0, 3);

        if ( $action == 'get' || $action == 'set' ) {
            $property = lcfirst(substr($name, 3));

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

        throw new Exception('Not implemented: ' . get_called_class() . '::' . $name);
    }
}
