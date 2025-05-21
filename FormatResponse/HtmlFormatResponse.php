<?php

namespace Tounaf\Exception\FormatResponse;

use Symfony\Component\HttpFoundation\Response;

/**
 * HTML response formatter.
 *
 * This class implements `FormatResponseInterface` to provide error responses
 * in HTML format. It checks if the 'html' format is requested and renders
 * a basic HTML response.
 *
 * @todo The render method currently returns a static "Resource not found" message.
 *       This should be improved to use the provided $data array to generate
 *       a more informative and user-friendly HTML error page, possibly using a templating engine.
 */
class HtmlFormatResponse implements FormatResponseInterface
{
    /**
     * HtmlFormatResponse constructor.
     *
     * The constructor currently has no specific dependencies or actions.
     */
    public function __construct()
    {
        // Constructor logic can be added here if needed,
        // e.g., injecting a templating service.
    }

    /**
     * Renders the given data into an HTML Symfony Response object.
     *
     * Note: The current implementation returns a generic "Resource not found" HTML response
     * and does not utilize the $data parameter. This should be enhanced to produce
     * dynamic HTML content based on the error data.
     *
     * @param  array $data The data to be rendered. Expected to contain error details
     *                     like 'message', 'code', etc., though currently unused.
     * @return Response A Symfony HTTP response object containing basic HTML.
     */
    public function render(array $data): Response
    {
        // TODO: Implement a more sophisticated HTML rendering logic.
        // For example, using a templating engine and the $data array:
        // $content = sprintf("<html><body><h1>Error %s</h1><p>%s</p></body></html>",
        //     htmlspecialchars($data['code'] ?? 'Error'),
        //     htmlspecialchars($data['message'] ?? 'An unexpected error occurred.')
        // );
        // return new Response($content, $data['code'] ?? Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type' => 'text/html']);

        return new Response('<h1>Resource not found</h1><p>The page you requested could not be found.</p>', Response::HTTP_NOT_FOUND, ['Content-Type' => 'text/html']);
    }

    /**
     * Checks if this formatter supports the 'html' format.
     *
     * @param  string $format The format string to check.
     * @return bool True if $format is 'html', false otherwise.
     */
    public function supportsFormat(string $format): bool
    {
        return $format === 'html';
    }

}
