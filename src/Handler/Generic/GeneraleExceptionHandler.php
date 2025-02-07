<?php

namespace Tounaf\Exception\Handler\Generic;

use Tounaf\Exception\Exception\Exception;
use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tounaf\Exception\Exception\TounafException;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

class GeneraleExceptionHandler implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    public function __construct(private FormatResponseInterface $formatResponseInterface)
    {

    }
    /**
     * @param  \Throwable $exception
     */
    public function handleException(\Throwable $throwable): Response
    {
        return new JsonResponse(
            [
            'message' => $throwable->getMessage(),
            'http_message' => 'Internal error',
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }

    /**
     * @param  \Exception $exception
     */
    public function supportsException(\Throwable $throwable): bool
    {
        return $throwable instanceof TounafException;
    }

    public function setFormat(FormatResponseInterface $formatResponseInterface): void
    {
        $this->formatResponseInterface = $formatResponseInterface;
    }

}
