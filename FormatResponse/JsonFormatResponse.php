<?php

namespace Tounaf\Exception\FormatResponse;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * JSON response formatter.
 *
 * This class implements `FormatResponseInterface` to provide error responses
 * in JSON format. It checks if the 'json' format is requested and renders
 * the provided data as a `JsonResponse`.
 */
class JsonFormatResponse implements FormatResponseInterface
{
    /**
     * Renders the given data into a JSON Symfony Response object.
     *
     * The input data array is directly passed to the `JsonResponse` constructor,
     * which handles the JSON encoding and setting the appropriate `Content-Type` header.
     * The HTTP status code for the `JsonResponse` will typically be derived from
     * the 'code' field within the $data array by the calling handler, or default
     * to 200 if not specified (though for errors, it should be an error code).
     *
     * @param  array $data The data to be rendered as JSON. This array usually includes
     *                     keys like 'message', 'code', 'http_message', and other error details.
     * @return Response A `JsonResponse` object containing the error data.
     */
    public function render(array $data): Response
    {
        // The status code for JsonResponse should ideally be set from $data['code']
        // or determined by the ExceptionHandler. JsonResponse defaults to 200 if not specified.
        // Example: return new JsonResponse($data, $data['code'] ?? Response::HTTP_INTERNAL_SERVER_ERROR);
        return new JsonResponse($data);
    }

    /**
     * Checks if this formatter supports the 'json' format.
     *
     * @param  string $format The format string to check.
     * @return bool True if $format is 'json', false otherwise.
     */
    public function supportsFormat(string $format): bool
    {
        return $format === 'json';
    }

}
