<?php

namespace Tounaf\Exception\Exception;

use Tounaf\Exception\FormatResponse\FormatResponseCheckerInterface;
use Tounaf\Exception\FormatResponse\FormatResponseManager;
use Symfony\Component\HttpFoundation\Request;
use Tounaf\Exception\Handler\LogicalExceptionHandler;

class ExceptionRegistry
{
    /**
     * @var PosExceptionInterface[] $exceptionHandlers
     */
    private $exceptionHandlers;

    /**
     * @var FormatResponseManager
     */
    private $formatResponseManager;

    /**
     * @var AbstractExceptionHandler[] $decoratorExceptionHandler
     */
    private $decoratorExceptionHandlers = [];

    /**
     * @var ExceptionHandlerInterface
     */
    private $defaultExceptionHandler;

    /**
     * @var ExceptionHandlerInterface
     */
    private $exceptionHandler;

    public function __construct(iterable $exceptionHandlers)
    {
        $this->exceptionHandlers = iterator_to_array($exceptionHandlers);
    }

    public function setFormatManager(FormatResponseManager $formatResponseManager): void
    {
        $this->formatResponseManager = $formatResponseManager;
    }

    public function addDecorator(AbstractExceptionHandler $decoratorExceptionHandler): void
    {
        $this->decoratorExceptionHandlers [] = $decoratorExceptionHandler;
    }

    public function setDefaultHandler(ExceptionHandlerInterface $defaultExceptionHandler): void
    {
        $this->defaultExceptionHandler = $defaultExceptionHandler;
    }

    public function addHandler(ExceptionHandlerInterface $handler): void
    {
        $this->exceptionHandlers [] = $handler;
    }

    /**
     * @return AbstractException|ExceptionHandlerInterface
     */
    public function getExceptionHandler(\Throwable $throwable, Request $request)
    {
        $formatResponse = $this->formatResponseManager->getFormatHandler($request->getRequestFormat(null));

        try {

            $this->addHandler($this->defaultExceptionHandler);

            foreach($this->exceptionHandlers as $exceptionHandler) {
                if (!$exceptionHandler instanceof ExceptionHandlerInterface) {
                    throw new TounafException(
                        sprintf(
                            'Handler %s must implement the %s interface',
                            get_class($exceptionHandler),
                            ExceptionHandlerInterface::class
                        )
                    );
                }

                if ($exceptionHandler->supportsException($throwable)) {

                    if ($exceptionHandler instanceof FormatResponseCheckerInterface) {
                        $exceptionHandler->setFormat($formatResponse);
                    }

                    $this->exceptionHandler = $exceptionHandler;
                    break;
                }
            }

        } catch(\Throwable $exception) {
            $this->exceptionHandler = new LogicalExceptionHandler($formatResponse);
        }

        return  $this->decoratesHandler($this->exceptionHandler);
    }

    private function decoratesHandler(ExceptionHandlerInterface $handler)
    {
        foreach($this->decoratorExceptionHandlers as $decoratorHandler) {
            /**
             * @var AbstractExceptionHandler $decoratorHandler
             */
            $decoratorHandler->decoratesHandler($handler);
            $handler = $decoratorHandler;
        }

        return $handler;
    }
}
