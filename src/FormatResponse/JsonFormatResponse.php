<?php

namespace Tounaf\Exception\FormatResponse;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JsonFormatResponse implements FormatResponseInterface
{
    /**
     * @var    array $data
     */
    public function render(array $data): Response
    {
        return new JsonResponse($data);
    }

    /**
     * @var    string $format
     */
    public function supportsFormat(string $format): bool
    {
        return $format === 'json';
    }

}
