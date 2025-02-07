<?php

namespace Tounaf\Exception\Handler\Http;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

class AccessDeniedHttpExceptionHandler implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    public function __construct(private FormatResponseInterface $formatResponseInterface)
    {

    }
    public function handleException(\Throwable $throwable): Response
    {
        return $this->formatResponseInterface->render(
            [
                'message' => $throwable->getMessage(),
                'http_message' => 'Forbidden',
                'code' => Response::HTTP_FORBIDDEN
            ]
        );
    }

    public function supportsException(\Throwable $throwable): bool
    {
        return $throwable instanceof AccessDeniedException;
    }

    public function setFormat(FormatResponseInterface $formatResponseInterface): void
    {
        $this->formatResponseInterface = $formatResponseInterface;
    }

}
