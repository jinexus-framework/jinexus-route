# AGENTS.md

Guidance for AI coding agents (and humans) working in the `jinexus-framework/jinexus-route`
package. Read this before making changes.

## What this package is

Route registration and matching for jinexus-mvc applications, plus a redirect helper. A
`Route` holds a name-keyed table of route definitions, resolves the current request URI
against it, and looks up a route's URI by name. A `Redirect` sends a `Location` header for
a named route and terminates the request. Both hang off a shared `AbstractBase` that
provides reflection-driven magic getters and setters for public properties.

Unlike the sibling `jinexus-http` package, this one reads `$_SERVER` directly in
`AbstractRoute::getUri()` — there is no injection seam. Tests snapshot and restore the
super-global instead.

## Build & test commands

Run everything from the package root (the directory containing `composer.json`).

```bash
# Install dependencies
composer install

# Regenerate autoloader after adding/moving/renaming classes or namespaces
composer dump-autoload

# Run the full test suite (auto-discovers phpunit.dist.xml)
./vendor/bin/phpunit

# Equivalent via Composer scripts
composer test
composer test:coverage        # sets XDEBUG_MODE=coverage and prints a text report
composer test:testdox         # readable, per-test output

# Silence the local Xdebug "could not connect" notice
XDEBUG_MODE=off ./vendor/bin/phpunit

# Run a single file / a single test by name (regex)
XDEBUG_MODE=off ./vendor/bin/phpunit test/Route/RouteTest.php
XDEBUG_MODE=off ./vendor/bin/phpunit --filter get_uri_drops_the_query_string
```

There is no build step — this is a source-only library consumed via Composer.

The suite is currently **52 tests / 75 assertions** at **100% line, method, and class
coverage**. Treat a coverage regression as a failure, not a detail.

## Project architecture

```
src/                                Namespace: JiNexus\Route\
  RouteException.php                Base exception (extends \Exception)
  Base/
    BaseInterface.php               Declares __call()
    AbstractBase.php                Reflection-based magic getX()/setX() for PUBLIC properties;
                                    throws RouteException on anything it can't resolve
  Route/
    RouteInterface.php              Contract: constructor(RedirectInterface), readable $redirect,
                                    getMatchRoute/getRoutes/setRoutes/getRouteUri/getUri
    AbstractRoute.php               Route table + matching + URI derivation
    Route.php                       Concrete, intentionally EMPTY subclass of AbstractRoute
    Factory/RouteFactory.php        static build(): Route — wires in RedirectFactory::build()
  Redirect/
    RedirectInterface.php           Contract: constructor(), setRoutes(), toRoute()
    AbstractRedirect.php            Location-header redirect with protected seams
                                    (headersSent/sendHeader/terminate)
    Redirect.php                    Concrete, intentionally EMPTY subclass of AbstractRedirect
    Factory/RedirectFactory.php     static build(): Redirect
  Factory/
    FactoryInterface.php            Marker interface extending BaseInterface
    AbstractFactory.php             Base for factories (extends AbstractBase)

test/                               Namespace: JiNexus\Route\Test\
  Base/AbstractBaseTest.php         Covers AbstractBase::__call (public/protected/unknown paths)
  Route/RouteTest.php               Covers AbstractRoute (matching, name lookup, getUri)
  Route/Factory/RouteFactoryTest.php
  Redirect/RedirectTest.php         Covers AbstractRedirect (guards, header, status code, terminate)
  Redirect/Factory/RedirectFactoryTest.php
  Fixture/BaseDouble.php            Public + protected property, for __call tests
  Fixture/RouteDouble.php           Carries @method tags for getRedirect()/setRedirect()
  Fixture/RedirectDouble.php        Carries @method tag for getRoutes(); overrides the three seams
```

Inheritance chains: `Route` → `AbstractRoute` → `AbstractBase`; `Redirect` →
`AbstractRedirect` → `AbstractBase`; the factories → `AbstractFactory` → `AbstractBase`.

### The `$routes` argument convention (read before touching AbstractRoute)

`getMatchRoute()` and `getRouteUri()` both take a `$routes` array, and as of 1.1.0 they
treat it **identically**: the argument wins when supplied, and an empty argument falls back
to the routes registered through `setRoutes()`.

| Method | Non-empty `$routes` argument | Empty `$routes` argument |
|--------|------------------------------|--------------------------|
| `getMatchRoute(array $routes = [], string $uri = '')` | matches against the argument | matches against `setRoutes()` |
| `getRouteUri(string $routeName = '', array $routes = [])` | resolves from the argument | resolves from `setRoutes()` |

Before 1.1.0 these were asymmetric — `getMatchRoute()` ignored its argument entirely and
`getRouteUri()` ignored the registered routes entirely. Six tests pin the current
behavior (`get_match_route_matches_against_its_routes_argument`,
`..._prefers_its_routes_argument_over_the_registered_routes`,
`..._falls_back_to_the_registered_routes_for_an_empty_argument`, and the three
`get_route_uri_*` equivalents). Keep the two methods symmetrical: if you change the
precedence rule in one, change it in the other, and update both sets of tests.

The fallback is triggered by a **falsy** check (`if (! $routes)`), so an explicitly passed
empty array is indistinguishable from an omitted one — that is intentional, and it is what
keeps the pre-1.1.0 default-argument calls behaving exactly as they did.

Also note `getMatchRoute()` matches with `==` (loose), stops at the first hit via `break`,
and returns the whole entry keyed by its route name (`['about' => ['route' => …, …]]`).

### The redirect seams (read before touching AbstractRedirect)

`toRoute()` ends by terminating the request, which makes it untestable in-process unless
the terminating call is isolated. Three `protected` methods exist for exactly that reason:

| Seam | Real body | Why it exists |
|------|-----------|---------------|
| `headersSent()` | `headers_sent()` | Lets tests force the header-skipping branch |
| `sendHeader()` | `header(...)` | Lets tests capture the `Location` value and status code |
| `terminate()` | `exit()` | Lets tests survive; carries `@codeCoverageIgnore` |

`Fixture/RedirectDouble` overrides all three. `headersSent()` and `sendHeader()` **delegate
up to `parent::`** after recording, so the shipped one-line bodies stay exercised rather
than shadowed; `terminate()` must not delegate, since `exit()` would take the test process
down. Keep `terminate()` a single statement — `@codeCoverageIgnore` hides every line in
that method, so any logic added there becomes silently unmeasured.

Inside PHPUnit, real `headers_sent()` returns `false` (PHPUnit buffers its own output) and
a real `header()` call neither warns nor fails. This is **not** true of bare CLI, where
output before `header()` triggers `Cannot modify header information` — which
`failOnWarning="true"` would turn into a test failure. Because `headers_sent()` is always
false under test, `RedirectDouble::$headersAlreadySent` is the only way to reach the
header-skipping branch.

## Coding standards

- **Language:** PHP `^8.5`. Every PHP file starts with `declare(strict_types=1);`.
- **Autoloading:** PSR-4. `JiNexus\Route\` → `src/`, `JiNexus\Route\Test\` → `test/`.
  One class/interface per file; the file name matches the type name.
- **Naming:** interfaces are suffixed `Interface`; abstract bases are prefixed `Abstract`;
  test doubles are suffixed `Double` and live in `test/Fixture/`. Namespaces mirror the
  directory layout.
- **Property hooks:** `AbstractRoute` exposes its collaborator with a get-only hook
  (`public RedirectInterface $redirect { get { return $this->redirect; } }`). Keep the
  concrete `Route`/`Redirect` classes **empty** — a declared property shadows the
  hook/backing field and changes access semantics. To silence an IDE "undefined property"
  notice, use a `@property` PHPDoc tag, never a real property.
- **Errors:** throw `JiNexus\Route\RouteException` (or a subclass) for package-level
  failures. `AbstractBase::__call` already throws it for unresolved magic calls, with the
  message `Not implemented: <get_called_class()>::<property>`.
- **PHP 8.5 features are in use.** `AbstractRoute::getUri()` and several factory tests use
  the pipe operator `|>`. Keep the `php: "^8.5"` constraint in mind — do not add code
  requiring a higher version, and do not lower the floor to satisfy a tool that can't
  parse 8.5.

### Test conventions

- Tests extend `PHPUnit\Framework\TestCase` and are declared `final`. No class-level
  docblocks.
- Use PHPUnit **attributes**, not annotations: `#[Test]`, `#[CoversClass(...)]`.
- `#[CoversClass]` targets the **abstract** that holds the behavior
  (`AbstractRoute`/`AbstractRedirect`), not the empty concrete subclass.
- Test method names are `snake_case` and describe the behavior.
- PHPUnit 13: use `expectExceptionMessageMatches()` (regex), **not**
  `expectExceptionMessage()`. Wrap literal text with `preg_quote($text, '/')`.
- **No data providers.** Related cases are grouped as several assertions inside one
  themed test (see the `get_uri_*` tests). Follow that pattern.
- **Prefer assertions that reflect a real runtime contract over ones the type checker can
  fold to a constant.** Because this package leans on typed properties and property hooks,
  `assertInstanceOf(X::class, $typedValue)` is often always-true — assert on
  `class_implements(SomeClass::class)` instead, or assert on actual behavior/data.
- Factories use the pipe idiom for their contract test:
  `RouteFactory::build() |> class_implements(...) |> (fn($x) => self::assertContains(RouteInterface::class, $x));`
- **Doubles carry the `@method` tags, tests stay clean.** Magic calls that `__call()` is
  supposed to resolve *and* ones it is supposed to reject are both declared as `@method` on
  the fixture, so no `//noinspection` comment is needed at the call site. Remember
  `__call()` reports `get_called_class()`, so a message assertion made through a double
  must expect the **fixture's** class name.
- **`$_SERVER` must be snapshotted.** `RouteTest::setUp()` copies `$_SERVER` and
  `tearDown()` restores it, because `getUri()` reads it directly. Any new test touching the
  super-global must go through the same class or repeat the snapshot.
- When asserting a side effect that happens *after* a throw, use `try`/`catch` rather than
  `expectException()` — see `to_route_has_no_side_effect_when_a_guard_rejects_the_route`.

## Workflow rules

- **Before finishing any change, run the suite** and make sure it's green:
  `XDEBUG_MODE=off ./vendor/bin/phpunit`. Then check coverage is still 100%:
  `composer test:coverage`.
- **After touching classes/namespaces**, run `composer dump-autoload`.
- **New behavior requires a new test.** This is a pure-logic library, so unit tests are
  expected to cover every branch you add or change. If a branch is genuinely unreachable
  under test, isolate it in the smallest possible method and annotate that method with
  `@codeCoverageIgnore` — do not exclude a whole file or widen the annotation to cover
  testable logic.
- **Commits:** follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/).
  Format: `type(scope): description`, with an optional body and footers.
  - Common types: `feat`, `fix`, `docs`, `test`, `refactor`, `chore`, `build`, `ci`.
  - Scope is optional and names the affected area (e.g. `feat(redirect): …`).
  - Subject is imperative and lowercase, no trailing period.
  - Breaking changes: add `!` after the type/scope (`feat!:`) and a `BREAKING CHANGE:`
    footer describing the break and its migration.
- **Changelog:** update `CHANGELOG.md` for every user-visible change, following the Keep a
  Changelog structure already in the file (Added / Changed / Deprecated / Removed / Fixed).
  Newest release on top.
- **Versioning:** semantic versioning. When bumping the minor/major line, also update
  `extra.branch-alias.dev-main` in `composer.json` to match the next dev series.
- **Config files:** `phpunit.dist.xml` is the committed default; a local `phpunit.xml`
  (gitignored) overrides it for personal tweaks. Don't commit `phpunit.xml`, `vendor/`, or
  `.phpunit.cache/`. Keep `phpunit.dist.xml` aligned with the sibling `config` and `http`
  packages — the three are intentionally identically apart from the test-suite name.
- **CI:** `.github/workflows/php.yml` runs on pushes and pull requests to `main` — it
  validates `composer.json`, installs on PHP 8.5 (pinned via `shivammathur/setup-php`), and
  runs `composer test`. Keep the workflow's PHP version in sync with the `require.php`
  constraint in `composer.json`.
- **Pull requests:** state what changed and why, note any behavior change explicitly, and
  confirm the suite and coverage are green. If you touched one of the two asymmetric
  signatures or the redirect seams, say so in the description — both have tests that
  deliberately pin surprising behavior.
