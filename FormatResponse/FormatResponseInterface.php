<?php

namespace Tounaf\Exception\FormatResponse;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for response formatters.
 *
 * Implementations of this interface are responsible for rendering a structured
 * array of error data into a Symfony `Response` object in a specific format
 * (e.g., JSON, HTML, XML). They also declare which format they support.
 */
interface FormatResponseInterface
{
    /**
     * Renders the given data into a Symfony Response object.
     *
     * The data array typically contains structured error information such as
     * message, code, and other details relevant to the exception.
     *
     * @param  array $data The data to be rendered. This array usually includes
     *                     keys like 'message', 'http_message', 'code', etc.
     * @return Response The Symfony HTTP response object, formatted according to
     *                  the implementation (e.g., JsonResponse, HTML Response).
     */
    public function render(array $data): Response;

    /**
     * Checks if this formatter supports the given format string.
     *
     * For example, a JSON formatter would return true if $format is 'json'.
     * This method is used by the `FormatResponseManager` to select the
     * appropriate formatter based on the requested format.
     *
     * @param  string $format The format string to check (e.g., 'json', 'html', 'xml').
     * @return bool True if the formatter supports the specified format, false otherwise.
     */
    public function supportsFormat(string $format): bool;

}
