<?php

namespace Tounaf\Exception\Handler\Http;

use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

/**
 * Handles `AccessDeniedHttpException` (HTTP 403 Forbidden).
 *
 * This exception handler is specifically designed to catch `AccessDeniedHttpException`
 * instances, which are typically thrown when a user is authenticated but lacks
 * the necessary permissions to access a resource. It uses an injected
 * `FormatResponseInterface` to render the error response.
 */
class AccessDeniedHttpExceptionHandler implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    /**
     * AccessDeniedHttpExceptionHandler constructor.
     *
     * @param FormatResponseInterface $formatResponseInterface The response formatter to be used.
     *                                                        It's injected via the constructor
     *                                                        but can be overridden by `setFormat`.
     */
    public function __construct(private FormatResponseInterface $formatResponseInterface)
    {
        // The formatResponseInterface is initialized via constructor property promotion.
    }

    /**
     * Handles the `AccessDeniedHttpException` and returns a formatted response.
     *
     * It uses the injected `formatResponseInterface` to render the response,
     * typically including a "Forbidden" message and an HTTP 403 status code.
     *
     * @param  \Throwable $throwable The exception to handle, expected to be an `AccessDeniedHttpException`.
     * @return Response The HTTP response.
     */
    public function handleException(\Throwable $throwable): Response
    {
        return $this->formatResponseInterface->render(
            [
                'message' => $throwable->getMessage(), // Original exception message
                'http_message' => 'Forbidden',         // Standard HTTP message for 403
                'code' => Response::HTTP_FORBIDDEN   // HTTP status code
            ]
        );
    }

    /**
     * Checks if this handler supports the given exception.
     *
     * This handler only supports exceptions that are instances of `AccessDeniedHttpException`.
     *
     * @param  \Throwable $throwable The exception to check.
     * @return bool True if the exception is an instance of `AccessDeniedHttpException`, false otherwise.
     */
    public function supportsException(\Throwable $throwable): bool
    {
        return $throwable instanceof AccessDeniedHttpException;
    }

    /**
     * Sets the response formatter for this handler.
     *
     * This method allows the `ExceptionRegistry` or a similar mechanism
     * to inject or override the `FormatResponseInterface` instance that should be
     * used to render the response.
     *
     * @param FormatResponseInterface $formatResponseInterface The response formatter instance.
     * @return void
     */
    public function setFormat(FormatResponseInterface $formatResponseInterface): void
    {
        $this->formatResponseInterface = $formatResponseInterface;
    }

}
