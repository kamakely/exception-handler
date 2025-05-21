<?php

namespace Tounaf\Exception\Handler\Generic;

use Tounaf\Exception\Exception\Exception;
use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tounaf\Exception\Exception\TounafException;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

/**
 * Generic exception handler specifically for `TounafException` instances.
 *
 * This handler catches exceptions of type `TounafException`, which are typically
 * internal exceptions to the Tounaf Exception Handler bundle itself.
 * It formats the response using an injected `FormatResponseInterface`, defaulting
 * to a JSON response with a 500 Internal Server Error status.
 */
class GeneraleExceptionHandler implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    /**
     * GeneraleExceptionHandler constructor.
     *
     * @param FormatResponseInterface $formatResponseInterface The response formatter to be used.
     *                                                        Although injected via constructor,
     *                                                        it can be overridden by `setFormat`.
     */
    public function __construct(private FormatResponseInterface $formatResponseInterface)
    {
        // The formatResponseInterface is initialized via constructor property promotion.
    }

    /**
     * Handles the `TounafException` and returns a JSON response.
     *
     * Note: This method currently hardcodes a `JsonResponse`. It should ideally use
     * the injected `$formatResponseInterface` to render the response, making it
     * consistent with the `FormatResponseCheckerInterface` contract.
     *
     * @param  \Throwable $throwable The exception to handle, expected to be a `TounafException`.
     * @return Response A JSON response with error details.
     * @todo Utilize the injected `$this->formatResponseInterface` for rendering the response.
     */
    public function handleException(\Throwable $throwable): Response
    {
        // TODO: Use $this->formatResponseInterface->render() instead of hardcoding JsonResponse.
        // Example:
        // return $this->formatResponseInterface->render([
        //     'message' => $throwable->getMessage(),
        //     'http_message' => 'Internal error (Tounaf Bundle)',
        //     'code' => Response::HTTP_INTERNAL_SERVER_ERROR
        // ]);
        return new JsonResponse(
            [
                'message' => $throwable->getMessage(),
                'http_message' => 'Internal error', // Consider making this message more specific
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }

    /**
     * Checks if this handler supports the given exception.
     *
     * This handler only supports exceptions that are instances of `TounafException`.
     *
     * @param  \Throwable $throwable The exception to check.
     * @return bool True if the exception is an instance of `TounafException`, false otherwise.
     */
    public function supportsException(\Throwable $throwable): bool
    {
        return $throwable instanceof TounafException;
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
