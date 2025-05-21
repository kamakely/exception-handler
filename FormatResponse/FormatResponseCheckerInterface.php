<?php

namespace Tounaf\Exception\FormatResponse;

/**
 * Interface for classes that require a specific response formatter.
 *
 * Classes implementing this interface, typically exception handlers,
 * can have a `FormatResponseInterface` instance injected into them.
 * This allows the exception handling system to provide the appropriate
 * formatter (e.g., JSON, HTML) to the handler at runtime.
 */
interface FormatResponseCheckerInterface
{
    /**
     * Sets the response formatter instance.
     *
     * This method is used by the exception handling system (e.g., ExceptionRegistry)
     * to inject the chosen `FormatResponseInterface` implementation that the
     * class should use to format its output.
     *
     * @param FormatResponseInterface $formatResponse The response formatter instance.
     * @return void
     */
    public function setFormat(FormatResponseInterface $formatResponse): void;
}
