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
    /**
     * @param  \Throwable $throwable
     * @return Response
     */
    public function handleException(\Throwable $throwable): Response
    {
        return $this->formatResponseInterface->render(
            [
                'message' => $throwable->getMessage(),
                'http_message' => 'Logical Exception'
            ]
        );
    }

    /**
     * @param  \Throwable $throwable
     * @return bool
     */
    public function supportsException(\Throwable $throwable): bool
    {
        return false;
    }

    public function setFormat(FormatResponseInterface $formatResponseInterface): void
    {
        $this->formatResponseInterface = $formatResponseInterface;
    }
}
