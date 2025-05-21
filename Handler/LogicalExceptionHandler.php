<?php

namespace Tounaf\Exception\Handler;

use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

/**
 * Handler for logical exceptions or as a fallback.
 *
 * This exception handler can be used to handle generic logical exceptions
 * or serve as a last-resort handler within the `ExceptionRegistry` when
 * an error occurs during the handler selection process itself.
 * It uses an injected `FormatResponseInterface` to render the response.
 * Its `supportsException` method returns `false` by default, suggesting it's
 * not meant to be automatically selected based on exception type but rather
 * explicitly assigned or used as a default by the registry.
 */
class LogicalExceptionHandler implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    /**
     * LogicalExceptionHandler constructor.
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
     * Handles the exception and returns a formatted response.
     *
     * It uses the injected `formatResponseInterface` to render the response,
     * including the original exception message and a generic "Logical Exception" http_message.
     * The HTTP status code is not explicitly set here and would depend on the
     * `FormatResponseInterface::render` implementation or a default (e.g., 500).
     *
     * @param  \Throwable $throwable The exception or error to handle.
     * @return Response The HTTP response.
     * @todo Consider adding a 'code' (HTTP status code) to the data passed to render,
     *       e.g., Response::HTTP_INTERNAL_SERVER_ERROR, if this is a fallback for server errors.
     */
    public function handleException(\Throwable $throwable): Response
    {
        $data = [
            'message' => $throwable->getMessage(),
            'http_message' => 'Logical Exception', // Indicates a server-side logical issue or fallback
            // 'code' => Response::HTTP_INTERNAL_SERVER_ERROR // Consider adding a default status code
        ];
        return $this->formatResponseInterface->render($data);
    }

    /**
     * Checks if this handler supports the given exception.
     *
     * By default, this handler returns `false`, meaning it won't be automatically
     * selected by the `ExceptionRegistry` based on the exception type. It's typically
     * used as a fallback or explicitly invoked by the registry when other handlers
     * fail or when an internal error occurs in the registry itself.
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
