<?php

namespace Tounaf\Exception\FormatResponse;

use Symfony\Component\HttpFoundation\Response;

interface FormatResponseInterface
{
    /**
     * @var    array $data
     */
    public function render(array $data): Response;

    /**
     * @var    string $format
     */
    public function supportsFormat(string $format): bool;

}
