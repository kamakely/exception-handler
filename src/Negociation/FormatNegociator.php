<?php

namespace Tounaf\Exception\Negociation;

use Symfony\Component\HttpFoundation\Request;
use Tounaf\Exception\RequestMatcher\MatcherRequestInterface;

abstract class FormatNegociator
{
    /**
     * Retour the best format from the given request
     * @return string
     */
    abstract public function getBest(): ?string;

}
