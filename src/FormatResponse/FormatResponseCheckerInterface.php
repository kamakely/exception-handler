<?php

namespace Tounaf\Exception\FormatResponse;

interface FormatResponseCheckerInterface
{
    /**
     * @var $formatResponse FormatResponseInterface
     */
    public function setFormat(FormatResponseInterface $formatResponse): void;
}
