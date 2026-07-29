# JiNexus Route

`JiNexus Route` provides simple route matching and access to every registered route in an
application, together with a redirect helper, for jinexus-mvc applications.

A `Route` holds a name-keyed table of route definitions, derives the current request URI
from `$_SERVER`, and matches it against that table. A `Redirect` sends a `Location` header
for a named route and terminates the request.

- Documentation: https://framework.jinexus.com/documentation
- Issues: https://github.com/jinexus-framework/jinexus-route/issues

## Requirements

- PHP `^8.5`

## Installation

Install via [Composer](https://getcomposer.org/):

```bash
composer require jinexus-framework/jinexus-route
```

## Usage

### Registering routes

A route table is an array keyed by the route name. Each entry needs a `route` key holding the
URI; any other keys are yours to use and are returned untouched on a match.

```php
use JiNexus\Route\Route\Route;
use JiNexus\Route\Route\Factory\RouteFactory;
use JiNexus\Route\Redirect\Redirect;

// Via the factory (builds and wires a Redirect for you)...
$route = RouteFactory::build();

// ...or explicitly.
$route = new Route(new Redirect());

$route->setRoutes([
    'home'  => ['route' => '/',         'controller' => HomeController::class],
    'about' => ['route' => '/about-us', 'controller' => AboutController::class],
    'user'  => ['route' => '/user',     'controller' => UserController::class],
]);

$route->getRoutes();   // the full table
$route->setRoutes();   // called with no argument, resets to []
```

### Matching the current request

```php
// Match an explicit URI.
$route->getMatchRoute([], '/about-us');
// ['about' => ['route' => '/about-us', 'controller' => AboutController::class]]

// Omit the URI to match against the current request.
$route->getMatchRoute();

// No match returns an empty array.
$route->getMatchRoute([], '/nope');   // []
```

A match returns the **whole entry, keyed by its route name**. Matching stops at the first
hit, so if two names share a URI, the earlier one wins.

You can also match against a table passed in directly, without registering it. A supplied
table takes precedence; an empty one falls back to the registered routes:

```php
$route->getMatchRoute(['about' => ['route' => '/about-us']], '/about-us');
// ['about' => ['route' => '/about-us']]
```

### Resolving a route's URI by name

```php
$routes = [
    'home' => ['route' => '/'],
    'user' => ['route' => '/user'],
];

$route->getRouteUri('home', $routes);              // '/'
$route->getRouteUri('user/profile/42', $routes);   // '/user' — only the first segment is looked up
$route->getRouteUri('missing', $routes);           // throws RouteException

// Omit the table to resolve against the registered routes.
$route->setRoutes($routes);
$route->getRouteUri('home');                       // '/'
```

`getRouteUri()` follows the same rule as `getMatchRoute()`: a supplied `$routes` table wins,
and an empty one falls back to whatever `setRoutes()` registered. It throws a
`RouteException` when the name is found in neither.

### Deriving the request URI

`getUri()` strips the directory the front controller lives in, drops the query string, and
normalizes the surrounding slashes:

```php
// SCRIPT_NAME = /app/public/index.php
// REQUEST_URI = /app/public/user/42?ref=nav
$route->getUri();   // '/user/42'
```

| `SCRIPT_NAME` | `REQUEST_URI` | `getUri()` |
|---------------|---------------|------------|
| `/index.php` | `/` | `/` |
| `/index.php` | `/about-us/` | `/about-us` |
| `/index.php` | `/search?q=a/b` | `/search` |
| `/app/public/index.php` | `/app/public/` | `/` |
| `/app/public/index.php` | `/app/public/user/42` | `/user/42` |

This reads `$_SERVER['SCRIPT_NAME']` and `$_SERVER['REQUEST_URI']` directly, so both must
be present.

### Redirecting

```php
use JiNexus\Route\Redirect\Redirect;
use JiNexus\Route\Redirect\Factory\RedirectFactory;

$redirect = RedirectFactory::build();   // or: new Redirect()

$redirect->setRoutes([
    'home'  => ['route' => '/'],
    'about' => ['route' => '/about-us'],
]);

$redirect->toRoute('about');         // 302 Location: /about-us, then exits
$redirect->toRoute('about', true);   // 301 Location: /about-us, then exits
```

`toRoute()` throws a `RouteException` when the name is empty or unregistered and skips the
header if headers have already been sent. It terminates the request either way, so nothing
after the call runs.

The redirect a `Route` was constructed with is reachable from it:

```php
$route->redirect;         // the RedirectInterface
$route->getRedirect();    // same, through AbstractBase::__call()
```

### Magic getters and setters

`AbstractBase::__call()` resolves `getX()`/`setX()` against **public** properties only.
Setters are fluent. Anything it cannot resolve — a protected property, an unknown property,
or a method that isn't a `get`/`set` prefix — throws a `RouteException`:

```php
$route->getRedirect();              // reads the public $redirect
$route->setRedirect($otherOne);     // returns $route, so it chains

$redirect->getRoutes();             // throws: $routes is protected
// RouteException: Not implemented: JiNexus\Route\Redirect\Redirect::routes
```

### Error handling

Package-level failures throw `JiNexus\Route\RouteException` (a subclass of `\Exception`):

| Message | Thrown by |
|---------|-----------|
| `Route "<name>" not found` | `getRouteUri()`, `toRoute()` |
| `Route name must be provided` | `toRoute()` with an empty name |
| `Not implemented: <class>::<property>` | `AbstractBase::__call()` |

## Extending

`Route` and `Redirect` are deliberately empty subclasses of `AbstractRoute` and
`AbstractRedirect`. Keep them that way — the public accessors are implemented with PHP
property hooks over backing fields, and declaring a real property would shadow them. For
IDE autocompletion, prefer `@property` PHPDoc tags over real properties.

`AbstractRedirect` exposes three `protected` seams so the terminating redirect can be
tested: `headersSent()`, `sendHeader()`, and `terminate()`. Override them in a subclass to
observe or intercept the redirect instead of ending the request.

## Testing

```bash
composer install
composer test            # or: ./vendor/bin/phpunit
composer test:coverage   # text coverage report
composer test:testdox    # readable, per-test output
```

The suite is 52 tests at 100% line, method, and class coverage. `phpunit.dist.xml` is the
committed configuration. Use `XDEBUG_MODE=off` to silence the local Xdebug notice:

```bash
XDEBUG_MODE=off ./vendor/bin/phpunit --testdox
```

## Contributing

Please see [CONDUCT.md](CONDUCT.md) for the code of conduct. Contributions should include
tests and a `CHANGELOG.md` entry. See [AGENTS.md](AGENTS.md) for detailed build, style, and
workflow conventions.

## License

BSD-3-Clause. See [LICENSE.md](LICENSE.md).
