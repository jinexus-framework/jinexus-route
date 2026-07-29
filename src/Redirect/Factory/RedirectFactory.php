<?php

declare(strict_types=1);

namespace JiNexus\Route\Redirect\Factory;

use JiNexus\Route\Factory\AbstractFactory;
use JiNexus\Route\Redirect\Redirect;

/**
 * Class RedirectFactory
 * @package JiNexus\Route\Redirect\Factory
 */
class RedirectFactory extends AbstractFactory
{
    /**
     * @return Redirect
     */
    public static function build(): Redirect
    {
        return new Redirect();
    }
}
