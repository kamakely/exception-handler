<?php

namespace Tounaf\Exception\FormatResponse;

use Symfony\Component\HttpFoundation\Response;

class HtmlFormatResponse implements FormatResponseInterface
{
    /**
     * @var    array $data
     */
    public function render(array $data): Response
    {
        return new Response('Resource not found');
    }

    /**
     * @var    string $format
     */
    public function supportsFormat(string $format): bool
    {
        return $format === 'html';
    }

}
