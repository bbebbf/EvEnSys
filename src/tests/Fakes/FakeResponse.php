<?php
declare(strict_types=1);

namespace Tests\Fakes;

/**
 * Thrown by FakeResponse::redirect() so tests can assert the redirect target
 * without calling header() or exit.
 */
class RedirectException extends \RuntimeException {}

/**
 * Thrown by FakeResponse::abort403() / abort404() so tests can assert which
 * HTTP error code the controller intended to send.
 */
class AbortException extends \RuntimeException
{
    public function __construct(public readonly int $statusCode)
    {
        parent::__construct("HTTP {$statusCode}");
    }
}

/**
 * Test double for ResponseInterface.
 * All methods satisfy the `never` return type by throwing exceptions,
 * which lets PHPUnit catch and inspect them.
 */
class FakeResponse implements \ResponseInterface
{
    public function redirect(string $path): never
    {
        throw new RedirectException($path);
    }

    public function abort403(): never
    {
        throw new AbortException(403);
    }

    public function abort404(): never
    {
        throw new AbortException(404);
    }
}
