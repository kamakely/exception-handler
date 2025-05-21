<?php

namespace Tounaf\Exception\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for all exception handlers.
 *
 * This interface defines the contract that all specific exception handler classes
 * must implement. It ensures that each handler can process an exception and
 * determine if it is capable of handling a given exception type.
 */
interface ExceptionHandlerInterface
{
    /**
     * Handles the given throwable and returns a Symfony Response.
     *
     * This method is invoked by the exception handling system when this handler
     * has been selected (based on `supportsException`) to process an exception.
     * The implementation should transform the throwable into an appropriate
     * HTTP response to be sent to the client.
     *
     * @param  \Throwable $throwable The exception or error to handle.
     * @return Response The HTTP response to send.
     */
    public function handleException(\Throwable $throwable): Response;

    /**
     * Checks if this handler supports the given throwable.
     *
     * This method is called by the exception handling system to determine if this
     * handler is appropriate for the given exception. Implementations typically
     * check if the `$throwable` is an `instanceof` a specific exception class
     * or interface that this handler is designed for.
     *
     * @param  \Throwable $throwable The exception or error to check.
     * @return bool True if this handler can handle the throwable, false otherwise.
     */
    public function supportsException(\Throwable $throwable): bool;
}
