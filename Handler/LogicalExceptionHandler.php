<?php

namespace Tounaf\Exception\Handler;

use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseInterface;

class LogicalExceptionHandler implements ExceptionHandlerInterface, FormatResponseCheckerInterface
{
    public function __construct(private FormatResponseInterface $formatResponseInterface)
    {

    }
    public function handleException(\Throwable $throwable): Response
    {
        return $this->formatResponseInterface->render(
            [
                'message' => $throwable->getMessage(),
                'http_message' => 'Logical Exception',
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }

    public function supportsException(\Throwable $throwable): bool
    {
        return false;
    }

    public function setFormat(FormatResponseInterface $formatResponseInterface): void
    {
        $this->formatResponseInterface = $formatResponseInterface;
    }

    private function handleExceptionE(\Throwable $throwable): Response
    {
        return $this->formatResponseInterface->render(
            [
                'message' => $throwable->getMessage(),
                'http_message' => 'Logical Exception',
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }

    private function handleExceptionA(\Throwable $throwable): Response
    {
        return $this->formatResponseInterface->render(
            [
                'message' => $throwable->getMessage(),
                'http_message' => 'Logical Exception',
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }

    private function handleExceptionEB(\Throwable $throwable): Response
    {
        return $this->formatResponseInterface->render(
            [
                'message' => $throwable->getMessage(),
                'http_message' => 'Logical Exception',
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }

    private function handleExceptionED(\Throwable $throwable): Response
    {
        return $this->formatResponseInterface->render(
            [
                'message' => $throwable->getMessage(),
                'http_message' => 'Logical Exception',
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }
}
