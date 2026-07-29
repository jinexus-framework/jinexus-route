# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## v1.1.0 - 2026-07-30

### Added

- `Route`/`AbstractRoute` holding a name-keyed route table, with `setRoutes()`, `getRoutes()`, and a read-only `$redirect` collaborator exposed through a PHP property hook.
- `getMatchRoute()` resolving a URI against a supplied route table or the registered routes, returning the whole matching entry keyed by its route name, and falling back to the current request URI when none is supplied.
- `getRouteUri()` resolving a route name — or the first segment of a slash-separated name — to its URI, against a supplied route table or the registered routes.
- `getUri()` deriving the request URI from `$_SERVER`: strips the directory the front controller lives in, drops the query string, and normalizes surrounding slashes.
- `Redirect`/`AbstractRedirect` sending a `Location` header for a named route, with `301` when permanent and `302` otherwise, and skipping the header when headers have already been sent.
- Three `protected` seams on `AbstractRedirect` — `headersSent()`, `sendHeader()`, and `terminate()` — so the terminating redirect can be observed, intercepted, or unit-tested instead of ending the process. `terminate()` carries `@codeCoverageIgnore`.
- `RouteFactory::build()` and `RedirectFactory::build()` for constructing instances, with `RouteFactory` wiring in a `Redirect`.
- Reflection-based magic getters/setters for public properties via `AbstractBase::__call()`; setters are fluent, and unresolvable calls throw.
- `RouteException` for package-level error handling.
- `RouteInterface`, `RedirectInterface`, `BaseInterface`, and `FactoryInterface` contracts.
- PHPUnit 13 unit-test suite — 52 tests at 100% line, method, and class coverage — with a committed `phpunit.dist.xml` matching the sibling `config` and `http` packages.
- Test fixtures under `test/Fixture/`: `BaseDouble` for the `__call()` paths, `RouteDouble` and `RedirectDouble` carrying `@method` tags, with `RedirectDouble` overriding the three redirect seams to record what `toRoute()` did.
- GitHub Actions CI workflow (`.github/workflows/php.yml`) that validates `composer.json`, installs dependencies on PHP 8.5, and runs the test suite.
- `composer test` / `composer test:coverage` / `composer test:testdox` scripts.
- `AGENTS.md` with build, coding-standard, architecture, and workflow guidance.
- Expanded `README.md` with installation, usage, error-handling, and testing documentation.

### Changed

- Raised the minimum PHP requirement to `^8.5`; public accessors are implemented with PHP property hooks, and `AbstractRoute::getUri()` uses the pipe operator.
- `AbstractRedirect::toRoute()` now delegates to the `headersSent()`, `sendHeader()`, and `terminate()` seams rather than calling `headers_sent()`, `header()`, and `exit()` inline.
- Renamed the exception class from `Exception` to `RouteException`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- `getMatchRoute()` now honors its `$routes` argument. It previously accepted the argument and silently ignored it, always matching against the routes registered through `setRoutes()`, so passing a table in returned no match.
- `getRouteUri()` now falls back to the registered routes when no `$routes` argument is supplied. It previously read only its argument, so `getRouteUri('home')` always threw `Route "home" not found` even when `home` was registered.
- The two methods are now symmetrical: in both, a supplied route table takes precedence and an empty one falls back to the registered routes. The fallback is a falsy check, so an explicitly passed empty array behaves the same as an omitted one.
- The `autoload-dev` PSR-4 map pointed `JiNexus\Route\` at `test/`, colliding with the production namespace; it now maps `JiNexus\Route\Test\`.
- The package has no third-party requirements: `php: ^8.5` is the only entry under `require`. `terminate()`'s docblock states that it ends the request.

### Upgrading from v1.0.0

Both `getMatchRoute()` and `getRouteUri()` changed behavior, but only along paths that were previously broken. Every call that worked in v1.0.0 behaves identically in v1.1.0:

| Call | v1.0.0 | v1.1.0 |
|------|--------|--------|
| `getMatchRoute()` / `getMatchRoute([], $uri)` | matches registered routes | unchanged |
| `getMatchRoute($routes, $uri)` | argument ignored, matched registered routes | matches `$routes` |
| `getRouteUri($name, $routes)` | resolves from `$routes` | unchanged |
| `getRouteUri($name)` | always threw | resolves from registered routes |

Only two paths differ, and each was previously either a silently ignored argument or an unconditional throw. If you depended on `getMatchRoute($routes, …)` ignoring its first argument, pass `[]` instead. If you depended on `getRouteUri($name)` throwing, check `getRoutes()` yourself before calling.

## v1.0.0 - 2018-07-10

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
