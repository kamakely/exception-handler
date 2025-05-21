<?php

namespace Tounaf\Exception\Exception;

/**
 * Abstract base class for exception handlers.
 * Provides common utility methods that can be used by concrete exception handler implementations.
 */
abstract class AbstractException implements ExceptionHandlerInterface
{
    /**
     * Parses the exception message to extract a title and a message.
     *
     * If the exception message contains a pipe character ('|'), it splits the message
     * into a title and a detailed message. Otherwise, the entire message is used as
     * the detailed message.
     *
     * @param \Exception $exception The exception from which to extract message parts.
     *
     * @return array An associative array containing 'message' and optionally 'title'.
     *               Example: ['message' => 'Detailed error message.', 'title' => 'Error Title']
     *                        or ['message' => 'Simple error message.']
     */
    protected function getMessageParts(\Exception $exception)
    {
        $message = $exception->getMessage();
        $customResponse = [
            'message' => $message,
        ];
        if (strpos($message, '|') !== false) {
            list($title, $message, ) = explode('|', $message);
            $customResponse['message'] = $message;
            $customResponse['title'] = $title;
        }

        return $customResponse;
    }

}
