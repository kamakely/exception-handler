<?php

namespace Tounaf\Exception\Negociation;

use Symfony\Component\HttpFoundation\Request;
use Tounaf\Exception\RequestMatcher\MatcherRequestInterface;

/**
 * Determines the best response format based on request matchers.
 *
 * This class manages a collection of `MatcherRequestInterface` instances,
 * each associated with specific format options. When `getBest()` is called,
 * it iterates through these matchers. The format associated with the first
 * matcher that matches the current request is returned.
 */
class FormatNegociator
{
    /**
     * @var Request|null The current HTTP request.
     */
    private $request;

    /**
     * @var array<array{0: MatcherRequestInterface, 1: array<string, mixed>}>
     *      An array of tuples, where each tuple contains a `MatcherRequestInterface`
     *      instance and an associated options array. The options array is expected
     *      to contain a 'format' key specifying the response format.
     */
    private $map = [];

    /**
     * Adds a request matcher and its associated format options to the negotiator.
     *
     * @param MatcherRequestInterface $requestMatcherInterface The request matcher.
     * @param array<string, mixed>    $options                 An options array, expected to include
     *                                                       a 'format' key (e.g., ['format' => 'json']).
     * @return void
     */
    public function add(MatcherRequestInterface $requestMatcherInterface, array $options = []): void
    {
        $this->map[] = [$requestMatcherInterface, $options];
    }

    /**
     * Determines the best response format for the current request.
     *
     * It iterates through the registered request matchers. If a matcher
     * matches the current request, this method returns the 'format'
     * specified in the options associated with that matcher.
     *
     * @return string|null The best format string (e.g., 'json', 'html') if a match is found,
     *                     otherwise null.
     * @throws \RuntimeException If the current request has not been set via `setRequest()`.
     */
    public function getBest(): ?string
    {
        $request = $this->getRequest();
        foreach ($this->map as $elements) {
            // $elements[0] is the MatcherRequestInterface
            // $elements[1] is the options array
            if (!$elements[0]->matches($request)) {
                continue;
            }

            // Assuming the options array contains a 'format' key.
            return $elements[1]['format'] ?? null;
        }

        return null;
    }

    /**
     * Sets the current HTTP request.
     *
     * This request object will be used by the matchers to determine the best format.
     *
     * @param Request $request The current HTTP request.
     * @return void
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Retrieves the current HTTP request.
     *
     * @return Request The current HTTP request.
     * @throws \RuntimeException If the request has not been set.
     */
    private function getRequest(): Request
    {
        if (null === $this->request) {
            throw new \RuntimeException("There is no current request. Call setRequest() first.");
        }

        return $this->request;
    }
}
