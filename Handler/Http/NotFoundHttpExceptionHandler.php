<?php

namespace Tounaf\Exception\Handler\Http;

use Tounaf\Exception\Exception\AbstractException;
use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles `NotFoundHttpException` (HTTP 404 Not Found).
 *
 * This exception handler is specifically designed to catch `NotFoundHttpException`
 * instances. These exceptions are thrown when the server cannot find the requested
 * resource.
 * It extends `AbstractException` to use utility methods like `getMessageParts` and
 * uses an injected `FormatResponseInterface` to render the error response.
 */
class NotFoundHttpExceptionHandler extends AbstractException implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    /**
     * NotFoundHttpExceptionHandler constructor.
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
     * Handles the `NotFoundHttpException` and returns a formatted response.
     *
     * It uses the `getMessageParts()` method from `AbstractException` to potentially
     * split the exception message into a title and detailed message.
     * The response is then rendered using the injected `formatResponseInterface`.
     *
     * @param  \Throwable $throwable The exception to handle, expected to be a `NotFoundHttpException`.
     * @return Response The HTTP response.
     */
    public function handleException(\Throwable $throwable): Response
    {
        $data = array_merge(
            [
                'message' => $throwable->getMessage(),  // Fallback message
                'http_message' => 'Not Found',         // Standard HTTP message for 404
                'code' => Response::HTTP_NOT_FOUND,    // HTTP status code
            ],
            $this->getMessageParts($throwable) // Potentially overrides 'message' and adds 'title'
        );

        return $this->formatResponseInterface->render($data);
    }

    /**
     * Checks if this handler supports the given exception.
     *
     * This handler only supports exceptions that are instances of `NotFoundHttpException`.
     *
     * @param  \Throwable $throwable The exception to check.
     * @return bool True if the exception is an instance of `NotFoundHttpException`, false otherwise.
     */
    public function supportsException(\Throwable $throwable): bool
    {
        return $throwable instanceof NotFoundHttpException;
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
