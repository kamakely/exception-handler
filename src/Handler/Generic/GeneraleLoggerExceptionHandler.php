<?php

namespace Tounaf\Exception\Handler\Generic;

use Psr\Log\LoggerInterface;
use ReflectionObject;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Tounaf\Exception\Exception\ExceptionHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Tounaf\Exception\Exception\AbstractExceptionHandler;

class GeneraleLoggerExceptionHandler extends AbstractExceptionHandler
{
    private LoggerInterface $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handleException(\Throwable $throwable): Response
    {
        $e = FlattenException::createFromThrowable($throwable);

        $handlers = $this->extractHandler($this);


        $this->logException(
            $throwable,
            sprintf(
                'Uncaught PHP Exception %s: "%s" at %s line %s',
                $e->getClass(),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ),
            $handlers
        );

        return $this->decoratedExceptionHandlerInterface->handleException($throwable);
    }


    private function extractHandler(ExceptionHandlerInterface $handler): array
    {
        $handlers = [];
        $reflection = new ReflectionObject($handler);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $h = $property->getValue($handler);
            if ($h instanceof ExceptionHandlerInterface) {
                $handlers [] = get_class($h);
                $handlers [] = $this->extractHandler($h);
            }
        }

        return $handlers;
    }


    protected function logException(\Throwable $exception, $message, array $handlers = []): void
    {
        $context = ['exception' => $exception, 'handlers' => $handlers];
        if (!$exception instanceof RequestExceptionInterface) {
            $this->logger->critical(
                $message,
                $context
            );
        } else {
            $this->logger->error(
                $message,
                $context
            );
        }
    }

}
