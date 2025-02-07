<?php

namespace Tounaf\Exception\Exception;

use Symfony\Component\HttpFoundation\Response;

interface ExceptionHandlerInterface
{
    public function handleException(\Throwable $throwable): Response;

    public function supportsException(\Throwable $throwable): bool;
}
