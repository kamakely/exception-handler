<?php

namespace Tounaf\Exception\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for request matchers.
 *
 * Implementations of this interface are responsible for determining if a given
 * Symfony `Request` object meets specific criteria. This is used, for example,
 * by the `FormatNegociator` to apply different formatting rules based on
 * characteristics of the request (e.g., path, host, headers).
 */
interface MatcherRequestInterface
{
    /**
     * Checks if the given request matches the criteria defined by the implementation.
     *
     * @param Request $request The HTTP request to check.
     * @return bool True if the request matches the criteria, false otherwise.
     */
    public function match(Request $request): bool;
}
