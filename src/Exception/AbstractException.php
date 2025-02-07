<?php

namespace Tounaf\Exception\Exception;

abstract class AbstractException implements ExceptionHandlerInterface
{
    /**
     * @return array
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
