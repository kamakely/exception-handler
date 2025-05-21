<?php

namespace Tounaf\Exception\Exception;

use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseManager;
use Symfony\Component\HttpFoundation\Request;
use Tounaf\Exception\Handler\LogicalExceptionHandler;

/**
 * Registry for managing and retrieving exception handlers.
 *
 * This class holds a collection of available exception handlers and provides logic
 * to select the appropriate handler for a given exception and request.
 * It also manages format response handlers and decorator handlers.
 */
class ExceptionRegistry
{
    /**
     * @var iterable<ExceptionHandlerInterface> A collection of registered exception handlers.
     */
    private $exceptionHandlers;

    /**
     * @var FormatResponseManager Manages different response formatters (e.g., JSON, HTML).
     */
    private $formatResponseManager;

    /**
     * @var DecoratorExceptionHandlerInterface[] A collection of registered decorator exception handlers.
     */
    private $decoratorExceptionHandlers = [];

    /**
     * ExceptionRegistry constructor.
     *
     * @param iterable<ExceptionHandlerInterface> $exceptionHandlers An iterable collection of exception handlers.
     *                                                              These are the primary handlers for specific exceptions.
     */
    public function __construct(iterable $exceptionHandlers)
    {
        $this->exceptionHandlers = $exceptionHandlers;
    }

    /**
     * Sets the format response manager.
     *
     * The format response manager is responsible for providing the correct
     * response formatter based on the request or other criteria.
     *
     * @param FormatResponseManager $formatResponseManager The format response manager instance.
     * @return void
     */
    public function setFormatManager(FormatResponseManager $formatResponseManager): void
    {
        $this->formatResponseManager = $formatResponseManager;
    }

    /**
     * Adds a decorator exception handler to the registry.
     *
     * Decorator handlers wrap other handlers to add or modify behavior.
     * They are applied after a primary handler is selected.
     *
     * @param DecoratorExceptionHandlerInterface $decoratorExceptionHandler The decorator handler to add.
     * @return void
     */
    public function addDecorator(DecoratorExceptionHandlerInterface $decoratorExceptionHandler): void
    {
        $this->decoratorExceptionHandlers[] = $decoratorExceptionHandler;
    }

    /**
     * Retrieves the appropriate exception handler for a given throwable and request.
     *
     * It iterates through the registered handlers, checks for support,
     * and applies formatting and decorators.
     *
     * @param \Throwable $throwable The exception or error to handle.
     * @param Request    $request   The current HTTP request.
     *
     * @return ExceptionHandlerInterface The selected and potentially decorated exception handler.
     *                                   Returns a GenericExceptionHandler or LogicalExceptionHandler as fallbacks.
     * @throws TounafException If a registered handler does not implement ExceptionHandlerInterface.
     */
    public function getExceptionHandler(\Throwable $throwable, Request $request): ExceptionHandlerInterface
    {
        $formatResponse = $this->formatResponseManager->getFormatHandler($request->getRequestFormat(null));
        // Initialize with a generic handler as a default
        $handler = new GenericExceptionHandler();
        if ($handler instanceof FormatResponseCheckerInterface) {
            $handler->setFormat($formatResponse);
        }

        try {
            foreach ($this->exceptionHandlers as $exceptionHandler) {
                if (!$exceptionHandler instanceof ExceptionHandlerInterface) {
                    throw new TounafException(
                        sprintf(
                            'Handler %s must implement the %s interface',
                            is_object($exceptionHandler) ? get_class($exceptionHandler) : gettype($exceptionHandler),
                            ExceptionHandlerInterface::class
                        )
                    );
                }

                if ($exceptionHandler->supportsException($throwable)) {
                    if ($exceptionHandler instanceof FormatResponseCheckerInterface) {
                        $exceptionHandler->setFormat($formatResponse);
                    }
                    $handler = $exceptionHandler;
                    // Break here if we want the first supported handler (respecting order/priority from service container)
                    // If multiple handlers can support an exception, the last one in the iterable will win without a break.
                    // This behavior depends on how priorities are handled during service configuration and injection.
                    break;
                }
            }
        } catch (\Throwable $exception) {
            // If any error occurs during handler selection (e.g., misconfiguration),
            // fallback to a logical exception handler.
            if (!$handler instanceof LogicalExceptionHandler) { // Avoid re-wrapping if already a LogicalExceptionHandler
                 $handler = new LogicalExceptionHandler($formatResponse);
                 if ($handler instanceof FormatResponseCheckerInterface) { // Though LogicalExceptionHandler might not implement it
                     $handler->setFormat($formatResponse);
                 }
            }
        }

        return $this->decoratesHandler($handler);
    }

    /**
     * Applies registered decorator handlers to the selected primary handler.
     *
     * Decorators are applied in the order they were added.
     *
     * @param ExceptionHandlerInterface $handler The primary exception handler to be decorated.
     *
     * @return ExceptionHandlerInterface The fully decorated exception handler.
     */
    private function decoratesHandler(ExceptionHandlerInterface $handler): ExceptionHandlerInterface
    {
        foreach ($this->decoratorExceptionHandlers as $decoratorHandler) {
            $decoratorHandler->decoratesHandler($handler);
            $handler = $decoratorHandler;
        }

        return $handler;
    }
}
