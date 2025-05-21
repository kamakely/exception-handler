<?php

namespace Tounaf\Exception\Exception;

use Symfony\Component\HttpFoundation\Response;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

/**
 * A generic exception handler that acts as a fallback.
 *
 * This handler is typically used when no other specific handler supports
 * the thrown exception. It provides a default error response, formatted
 * by an injected `FormatResponseInterface` implementation.
 *
 * By default, its `supportsException` method returns `false`, meaning it usually
 * needs to be explicitly chosen or used as a last resort by the ExceptionRegistry.
 */
class GenericExceptionHandler implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    /**
     * @var FormatResponseInterface The response formatter responsible for rendering the error output.
     */
    private $formatResponseInterface;

    /**
     * Handles the exception by rendering a generic error response.
     *
     * Uses the injected format response handler to create a response
     * with a generic "Internal Error" message and an HTTP 500 status code.
     * The original exception message is also included in the response data.
     *
     * @param  \Throwable $throwable The exception or error to handle.
     * @return Response The HTTP response.
     */
    public function handleException(\Throwable $throwable): Response
    {
        $messageExeption = $throwable->getMessage();
        return $this->formatResponseInterface->render(
            [
                'message' => $messageExeption,
                'http_message' => 'Erreur interne', // French for "Internal Error"
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }

    /**
     * Determines if this handler supports the given exception.
     *
     * By default, this generic handler does not support any specific exception
     * (returns `false`). It is intended to be used as a fallback handler
     * when no other handler is found for a given exception.
     *
     * @param  \Throwable $throwable The exception or error to check.
     * @return bool Always returns `false`.
     */
    public function supportsException(\Throwable $throwable): bool
    {
        return false;
    }

    /**
     * Sets the response formatter for this handler.
     *
     * This method allows the `ExceptionRegistry` or a similar mechanism
     * to inject the appropriate `FormatResponseInterface` instance (e.g., for JSON, HTML)
     * that should be used to render the response.
     *
     * @param FormatResponseInterface $formatResponseInterface The response formatter instance.
     * @return void
     */
    public function setFormat(FormatResponseInterface $formatResponseInterface): void
    {
        $this->formatResponseInterface = $formatResponseInterface;
    }

}
