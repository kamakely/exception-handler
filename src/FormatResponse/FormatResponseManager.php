<?php

namespace Tounaf\Exception\FormatResponse;

use Symfony\Component\HttpFoundation\Request;

final class FormatResponseManager
{
    private $formatHandlers = [];
    public function addFormatResponse(FormatResponseInterface $formatResponseInterface): void
    {
        $this->formatHandlers[] = $formatResponseInterface;
    }

    public function getFormatHandler(?string $format): FormatResponseInterface
    {
        foreach($this->formatHandlers as $formatHandler) {
            if($formatHandler->supportsFormat($format)) {
                return $formatHandler;
            }
        }

        return new JsonFormatResponse();
    }

    public function getFormatHandlers()
    {
        return $this->formatHandlers;
    }
}
