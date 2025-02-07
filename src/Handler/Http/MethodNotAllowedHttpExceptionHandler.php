<?php

namespace Tounaf\Exception\Handler\Http;

use Tounaf\Exception\Exception\AbstractException;
use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

class MethodNotAllowedHttpExceptionHandler extends AbstractException implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    public function __construct(private FormatResponseInterface $formatResponseInterface)
    {

    }

    public function handleException(\Throwable $throwable): Response
    {
        return new JsonResponse(
            array_merge(
                [
                'message' => $throwable->getMessage(),
                'http_message' => 'Method Not Allowed',
                'code' => Response::HTTP_METHOD_NOT_ALLOWED,
                ],
                $this->getMessageParts($throwable)
            )
        );
    }

    public function supportsException(\Throwable $throwable): bool
    {
        return $throwable instanceof MethodNotAllowedHttpException;
    }

    public function setFormat(FormatResponseInterface $formatResponseInterface): void
    {
        $this->formatResponseInterface = $formatResponseInterface;
    }
}
