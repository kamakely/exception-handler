<?php

namespace Tounaf\Exception\Exception;

abstract class AbstractExceptionHandler implements ExceptionHandlerInterface
{
    protected ExceptionHandlerInterface $decoratedExceptionHandlerInterface;

    public function decoratesHandler(ExceptionHandlerInterface $decoratedExceptionHandler): void
    {
        $this->decoratedExceptionHandlerInterface = $decoratedExceptionHandler;
    }

    public function supportsException(\Throwable $throwable): bool
    {
        return true;
    }
}
