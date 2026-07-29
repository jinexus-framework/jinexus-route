<?php

declare(strict_types=1);

namespace JiNexus\Route\Test\Fixture;

use JiNexus\Route\Base\AbstractBase;

/**
 * Test double for exercising AbstractBase::__call().
 *
 * AbstractBase's magic getters/setters only operate on *public* properties,
 * so this fixture exposes both a public and a protected property to cover the
 * accessible path and the "declared but not public" path.
 *
 * @method mixed getName() Magic accessor for the public $name.
 * @method self  setName(mixed $name = null) Magic mutator for the public $name.
 * @method mixed getSecret()
 * @method mixed getUnknown()
 * @method mixed doSomething()
 */
final class BaseDouble extends AbstractBase
{
    /**
     * Reachable via getName()/setName() through __call().
     */
    public mixed $name = null;

    /**
     * Declared but NOT public: __call() must refuse to touch it.
     */
    protected mixed $secret = 'hidden';
}
