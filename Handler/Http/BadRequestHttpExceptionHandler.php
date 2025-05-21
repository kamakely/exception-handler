<?php

namespace Tounaf\Exception\Handler\Http;

use Tounaf\Exception\Exception\AbstractException;
use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

/**
 * Handles `BadRequestHttpException` (HTTP 400 Bad Request).
 *
 * This exception handler is specifically designed to catch `BadRequestHttpException`
 * instances. These exceptions are typically thrown when the server cannot or
 * will not process the request due to something that is perceived to be a client error
 * (e.g., malformed request syntax, invalid request message framing, or deceptive request routing).
 * It extends `AbstractException` to use utility methods like `getMessageParts` and
 * uses an injected `FormatResponseInterface` to render the error response.
 */
class BadRequestHttpExceptionHandler extends AbstractException implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    /**
     * BadRequestHttpExceptionHandler constructor.
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
     * Handles the `BadRequestHttpException` and returns a response.
     *
     * It uses the `getMessageParts()` method from `AbstractException` to potentially
     * split the exception message into a title and detailed message.
     * The response is then rendered using the injected `formatResponseInterface`.
     *
     * Note: This method currently hardcodes a `JsonResponse`. It should ideally use
     * the injected `$formatResponseInterface` to render the response for consistency.
     *
     * @param  \Throwable $throwable The exception to handle, expected to be a `BadRequestHttpException`.
     * @return Response The HTTP response.
     * @todo Utilize the injected `$this->formatResponseInterface` for rendering the response.
     */
    public function handleException(\Throwable $throwable): Response
    {
        $data = array_merge(
            [
                'message' => $throwable->getMessage(), // Fallback message if getMessageParts doesn't override
                'http_message' => 'Bad Request',       // Standard HTTP message for 400
                'code' => Response::HTTP_BAD_REQUEST,  // HTTP status code
            ],
            $this->getMessageParts($throwable) // Potentially overrides 'message' and adds 'title'
        );

        // TODO: Use $this->formatResponseInterface->render($data) instead of hardcoding JsonResponse.
        return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Checks if this handler supports the given exception.
     *
     * This handler only supports exceptions that are instances of `BadRequestHttpException`.
     *
     * @param  \Throwable $throwable The exception to check.
     * @return bool True if the exception is an instance of `BadRequestHttpException`, false otherwise.
     */
    public function supportsException(\Throwable $throwable): bool
    {
        return $throwable instanceof BadRequestHttpException;
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
