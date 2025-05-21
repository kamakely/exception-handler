<?php

namespace Tounaf\Exception\Exception;

/**
 * Interface for exception handlers that act as decorators.
 *
 * Decorator handlers wrap another `ExceptionHandlerInterface` instance to add,
 * extend, or modify its behavior without changing its core implementation.
 * This interface ensures that decorators can identify the handler they are wrapping.
 */
interface DecoratorExceptionHandlerInterface extends ExceptionHandlerInterface
{
    /**
     * Sets the decorated (wrapped) exception handler.
     *
     * This method is used to inject the original exception handler instance
     * that this decorator will be augmenting.
     *
     * @param ExceptionHandlerInterface $decoratedExceptionHandler The exception handler instance to be decorated.
     * @return void
     */
    public function decoratesHandler(ExceptionHandlerInterface $decoratedExceptionHandler): void;
}
