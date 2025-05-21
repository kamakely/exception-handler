<?php

namespace Tounaf\Exception\Handler\Http;

use Tounaf\Exception\Exception\AbstractException;
use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

/**
 * Handles `MethodNotAllowedHttpException` (HTTP 405 Method Not Allowed).
 *
 * This exception handler is specifically designed to catch `MethodNotAllowedHttpException`
 * instances. These exceptions are thrown when a request method is known by the server
 * but is not supported by the target resource.
 * It extends `AbstractException` to use utility methods like `getMessageParts` and
 * uses an injected `FormatResponseInterface` to render the error response.
 */
class MethodNotAllowedHttpExceptionHandler extends AbstractException implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    /**
     * MethodNotAllowedHttpExceptionHandler constructor.
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
     * Handles the `MethodNotAllowedHttpException` and returns a response.
     *
     * It uses the `getMessageParts()` method from `AbstractException` to potentially
     * split the exception message into a title and detailed message.
     * The response is then rendered using the injected `formatResponseInterface`.
     *
     * Note: This method currently hardcodes a `JsonResponse`. It should ideally use
     * the injected `$formatResponseInterface` to render the response for consistency.
     *
     * @param  \Throwable $throwable The exception to handle, expected to be a `MethodNotAllowedHttpException`.
     * @return Response The HTTP response.
     * @todo Utilize the injected `$this->formatResponseInterface` for rendering the response.
     */
    public function handleException(\Throwable $throwable): Response
    {
        $data = array_merge(
            [
                'message' => $throwable->getMessage(),      // Fallback message
                'http_message' => 'Method Not Allowed',    // Standard HTTP message for 405
                'code' => Response::HTTP_METHOD_NOT_ALLOWED, // HTTP status code
            ],
            $this->getMessageParts($throwable) // Potentially overrides 'message' and adds 'title'
        );

        // TODO: Use $this->formatResponseInterface->render($data) instead of hardcoding JsonResponse.
        // The status code should be passed to the render method or set on the JsonResponse.
        return new JsonResponse($data, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Checks if this handler supports the given exception.
     *
     * This handler only supports exceptions that are instances of `MethodNotAllowedHttpException`.
     *
     * @param  \Throwable $throwable The exception to check.
     * @return bool True if the exception is an instance of `MethodNotAllowedHttpException`, false otherwise.
     */
    public function supportsException(\Throwable $throwable): bool
    {
        return $throwable instanceof MethodNotAllowedHttpException;
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
