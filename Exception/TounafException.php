<?php

namespace Tounaf\Exception\Exception;

/**
 * Base exception class for the Tounaf Exception Handler bundle.
 *
 * This exception can be thrown by the bundle's components to indicate
 * internal errors or misconfigurations within the exception handling system itself.
 * For example, it might be thrown if a registered handler is invalid or
 * if a required service is not found.
 */
class TounafException extends \Exception
{
}
