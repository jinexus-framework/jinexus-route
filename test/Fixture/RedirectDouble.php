<?php

declare(strict_types=1);

namespace JiNexus\Route\Test\Fixture;

use JiNexus\Route\Redirect\AbstractRedirect;

/**
 * Test double for exercising AbstractBase::__call() through a redirect, and for
 * driving AbstractRedirect::toRoute() past the point where it would exit().
 *
 * AbstractRedirect declares $routes as protected, so the magic getter must
 * refuse it. This fixture carries the "@method" tag for that call, which keeps it
 * statically resolvable instead of reading as an undefined method.
 *
 * The three protected seams are overridden to record what toRoute() did:
 * headersSent() and sendHeader() still delegate upwards, so the shipped
 * one-liners stay exercised rather than shadowed, while terminate() must not,
 * since exit() would take the test process down with it.
 *
 * @method mixed getRoutes() Magic accessor __call() must refuse, because
 *                           $routes is protected rather than public.
 */
final class RedirectDouble extends AbstractRedirect
{
    /**
     * Forces headersSent() to report true, so the branch of toRoute() that skips
     * the header can be reached. headers_sent() is false inside PHPUnit, which
     * buffers its own output, so this flag is the only way in.
     */
    public bool $headersAlreadySent = false;

    /**
     * Every sendHeader() call in order, as ['header', 'replace', 'statusCode'].
     */
    public array $sentHeaders = [];

    /**
     * How many times toRoute() reached terminate().
     */
    public int $terminateCount = 0;

    protected function headersSent(): bool
    {
        return $this->headersAlreadySent || parent::headersSent();
    }

    protected function sendHeader(string $header, bool $replace, int $statusCode): void
    {
        $this->sentHeaders[] = [
            'header' => $header,
            'replace' => $replace,
            'statusCode' => $statusCode,
        ];

        parent::sendHeader($header, $replace, $statusCode);
    }

    protected function terminate(): void
    {
        $this->terminateCount++;
    }
}
