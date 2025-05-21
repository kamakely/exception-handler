<?php

namespace Tounaf\Exception\FormatResponse;

use Symfony\Component\HttpFoundation\Request;

/**
 * Manages a collection of response formatters.
 *
 * This class is responsible for storing different implementations of
 * `FormatResponseInterface` and providing a way to retrieve the appropriate
 * formatter based on a requested format string (e.g., 'json', 'html').
 * If no specific formatter is found for the requested format, it defaults
 * to `JsonFormatResponse`.
 */
final class FormatResponseManager
{
    /**
     * @var array<FormatResponseInterface> A collection of registered response formatter instances.
     */
    private $formatHandlers = [];

    /**
     * @var Request The current HTTP request.
     * @deprecated This property and its setter `setRequest` seem unused within the class for core logic
     *             of getting format handlers. Consider removing if not essential for other external uses.
     */
    private $request;

    /**
     * Sets the current HTTP request.
     *
     * @param Request $request The current HTTP request.
     * @deprecated This method seems to set a property that is not directly used by `getFormatHandler`
     *             or `addFormatResponse`. Its necessity should be reviewed.
     * @return void
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Adds a response formatter to the manager.
     *
     * @param FormatResponseInterface $formatResponseInterface The response formatter instance to add.
     * @return void
     */
    public function addFormatResponse(FormatResponseInterface $formatResponseInterface): void
    {
        $this->formatHandlers[] = $formatResponseInterface;
    }

    /**
     * Retrieves a response formatter that supports the given format.
     *
     * Iterates through the registered formatters and returns the first one
     * that supports the specified format string. If no matching formatter is
     * found, it defaults to returning a new `JsonFormatResponse` instance.
     *
     * @param string|null $format The desired format string (e.g., 'json', 'html', 'xml').
     *                            If null, it will likely try to find a handler that supports null or default.
     * @return FormatResponseInterface The appropriate response formatter.
     */
    public function getFormatHandler(?string $format): FormatResponseInterface
    {
        foreach ($this->formatHandlers as $formatHandler) {
            if ($formatHandler->supportsFormat($format)) {
                return $formatHandler;
            }
        }

        // Default to JsonFormatResponse if no specific handler is found
        return new JsonFormatResponse();
    }

    /**
     * Retrieves all registered response formatters.
     *
     * @return array<FormatResponseInterface> An array of all registered response formatter instances.
     */
    public function getFormatHandlers(): array
    {
        return $this->formatHandlers;
    }
}
