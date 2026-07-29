<?php

declare(strict_types=1);

namespace JiNexus\Route\Test\Fixture;

use JiNexus\Route\Redirect\RedirectInterface;
use JiNexus\Route\Route\AbstractRoute;

/**
 * Test double for exercising AbstractBase::__call() through a route.
 *
 * AbstractRoute exposes $redirect as a public hooked property, so both the
 * magic accessor and the magic mutator reach it. This fixture carries the
 * "@method" tags for those calls, which keeps them statically resolvable
 * instead of reading as undefined methods.
 *
 * @method RedirectInterface getRedirect() Magic accessor for the public $redirect.
 * @method self setRedirect(RedirectInterface $redirect) Magic mutator for the public $redirect.
 */
final class RouteDouble extends AbstractRoute
{ }
