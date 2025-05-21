# Tounaf Exception Handler Bundle

## 1. Introduction

The Tounaf Exception Handler Bundle provides a flexible and decoupled way to manage exceptions within your Symfony applications. Instead of relying on monolithic exception handling logic, this bundle allows you to create dedicated handler classes for specific exceptions. This promotes cleaner code, better separation of concerns, and easier maintenance.

**Key Benefits:**

*   **Decoupled Handlers:** Assign a specific handler class to each exception type, making your error handling logic modular and easy to understand.
*   **Format-Agnostic Responses:** Render error responses in various formats (HTML, JSON, XML, etc.) based on content negotiation or specific requirements.
*   **Extensible Architecture:** Utilize decorators to add functionalities like logging, sending notifications, or modifying responses without altering core handler logic.
*   **Conditional Handling:** Employ request matchers to apply specific exception handlers based on incoming request attributes (e.g., route, host, method).
*   **Symfony Integration:** The bundle seamlessly integrates with the Symfony framework by listening to kernel exception events (`kernel.exception` event) and delegating handling to the appropriate registered service. Your custom handlers are registered as services and tagged, allowing the bundle to discover and manage them.

## 2. Installation

```bash
composer require tounaf/exception-handler
```

## 3. Core Components

This bundle is built around several key components that work together to provide robust exception handling.

### 3.1. `ExceptionHandlerInterface`

The cornerstone of the bundle, this interface defines the contract for all exception handlers. Any class that needs to handle an exception must implement it.

**Interface Definition:**

```php
<?php

namespace Tounaf\Exception;

use Symfony\Component\HttpFoundation\Response;

interface ExceptionHandlerInterface
{
    /**
     * Handles the given throwable and returns a Symfony Response.
     * This method is called only if supportsException() returns true for the given throwable.
     *
     * @param \Throwable $throwable The exception or error to handle.
     * @return Response The HTTP response to send.
     */
    public function handleException(\Throwable $throwable): Response;

    /**
     * Checks if this handler supports the given throwable.
     *
     * @param \Throwable $throwable The exception or error to check.
     * @return bool True if this handler can handle the throwable, false otherwise.
     */
    public function supportsException(\Throwable $throwable): bool;
}
```

**`handleException(\Throwable $throwable): Response`**

*   **Purpose:** This method contains the core logic for transforming the caught exception (`$throwable`) into a Symfony `Response` object. You might generate a JSON error response, render an HTML error page, or create any other type of response suitable for your application.
*   **Usage:** Implement this method to define how a specific exception (or a category of exceptions) should be presented to the user or calling client.

**`supportsException(\Throwable $throwable): bool`**

*   **Purpose:** This method determines whether the current handler is capable of (and responsible for) handling the given `$throwable`. It allows the bundle to select the most appropriate handler from potentially many registered handlers.
*   **Usage:** Typically, you'll check if the `$throwable` is an `instanceof` a specific exception class or interface that this handler is designed for. You can also add more complex logic, like checking exception codes or messages if needed. The first handler (ordered by priority) that returns `true` for a given exception will be used.

**Service Tagging for Discovery:**

To make your custom handler discoverable by the bundle, you need to tag it as a service in your Symfony application's service configuration (usually `config/services.yaml`):

```yaml
# config/services.yaml
services:
    App\Handler\MyCustomExceptionHandler: # Your handler class name
        # Your service definition...
        tags:
            # The 'tounaf_exception.handler' tag registers your class as an exception handler.
            # The 'priority' attribute (optional, defaults to 0) determines the order of execution.
            # Higher priority numbers are checked first. Handlers with the same priority are
            # processed in the order they are defined in the service container.
            - { name: tounaf_exception.handler, priority: 0 }
```

This tagging mechanism is crucial for the bundle to automatically find and utilize your custom handlers.

### 3.2. FormatResponse Component

The `FormatResponse` component is responsible for enabling different response formats (e.g., JSON, XML, HTML) for your exceptions. This is particularly useful when your application serves various types of clients (e.g., web browsers, API consumers).

At its core, this component might involve:

*   **`FormatResponseInterface` (Hypothetical):** An interface that handlers can optionally implement to indicate they can provide responses in multiple formats.
*   **Format-specific Serializers/Renderers:** Services that take the exception data (or an intermediate representation) and convert it into the desired output format. Symfony's Serializer component is often leveraged here.
*   **Content Negotiation Logic:** Determining the desired response format based on the request's `Accept` header.

**Example Scenario: Supporting JSON and HTML**

Imagine you want a specific handler, `App\Handler\ProductNotFoundExceptionHandler`, to return JSON for API requests (`Accept: application/json`) and a user-friendly HTML page for browser requests (`Accept: text/html`).

1.  **Handler Implementation (Conceptual):**

    Your `ProductNotFoundExceptionHandler` might internally decide the format or delegate to a formatting service.

    ```php
    <?php // App\Handler\ProductNotFoundExceptionHandler (Illustrative)

    // ... (other use statements)
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\Serializer\SerializerInterface; // For JSON
    use Twig\Environment; // For HTML (Twig templating)

    class ProductNotFoundExceptionHandler implements ExceptionHandlerInterface // and possibly a custom FormatAwareInterface
    {
        private $requestStack;
        private $serializer;
        private $twig;

        public function __construct(RequestStack $requestStack, SerializerInterface $serializer, Environment $twig)
        {
            $this->requestStack = $requestStack;
            $this->serializer = $serializer;
            $this->twig = $twig;
        }

        public function handleException(\Throwable $throwable): Response
        {
            $request = $this->requestStack->getCurrentRequest();
            $acceptableTypes = $request->getAcceptableContentTypes();

            $data = ['error' => $throwable->getMessage(), 'product_id' => $throwable->getProductId()]; // Assuming custom exception

            // Prioritize JSON if acceptable
            if (in_array('application/json', $acceptableTypes)) {
                return new JsonResponse(
                    $this->serializer->serialize($data, 'json'),
                    Response::HTTP_NOT_FOUND,
                    [],
                    true // True if $data is already JSON
                );
            }

            // Default to HTML
            return new Response(
                $this->twig->render('errors/product_not_found.html.twig', ['exception' => $throwable]),
                Response::HTTP_NOT_FOUND
            );
        }

        public function supportsException(\Throwable $throwable): bool
        {
            return $throwable instanceof ProductNotFoundException;
        }
    }
    ```

2.  **Bundle's Role (Tounaf Exception Handler):**

    The Tounaf Exception Handler bundle might provide utilities or a more structured way to achieve this, potentially through:
    *   **Dedicated Formatters:** Classes specifically designed to format exception data.
    *   **Configuration:** Allowing you to define supported formats and associate them with handlers or exception types in `config/packages/tounaf_exception.yaml`.

    For example, the bundle might define a service that your handler can use, or it might automatically apply content negotiation if your handler is tagged appropriately.

    *(The exact implementation details of how `FormatResponse` is achieved in the `tounaf/exception-handler` bundle would need to be verified from its source code or specific documentation. The example above is a general illustration of the concept.)*

    **Key Idea:** The goal is to separate the *logic* of handling an exception from the *presentation* of that exception. The `FormatResponse` component (or similar mechanism) facilitates this separation.

### 3.3. Handler Component (Exception Handler Decorators & Chain of Responsibility)

While the `ExceptionHandlerInterface` defines individual handlers, the "Handler" component itself (or related services within the bundle) often deals with how these handlers are invoked and managed. This can include concepts like handler decorators and the chain of responsibility pattern.

**Handler Decorators:**

Decorators provide a powerful way to add common functionalities to your exception handlers without modifying their core logic. This adheres to the Open/Closed Principle.

*   **Purpose:** To wrap an existing exception handler to extend or modify its behavior.
*   **Common Use Cases:**
    *   **Logging:** Log exception details before or after the primary handler acts.
    *   **Monitoring:** Send metrics to a monitoring system (e.g., Sentry, New Relic) when an exception is handled.
    *   **Transformation:** Modify the Response object returned by the wrapped handler (e.g., adding custom headers).
    *   **Conditional Execution:** Decide whether to execute the wrapped handler based on certain conditions.

*   **Implementation:**
    A decorator would also implement `ExceptionHandlerInterface`. It takes another `ExceptionHandlerInterface` instance (the "decorated" handler) in its constructor.

    ```php
    <?php // App\Handler\Decorator\LoggingExceptionHandlerDecorator

    namespace App\Handler\Decorator;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\HttpFoundation\Response;
    use Tounaf\Exception\ExceptionHandlerInterface;

    class LoggingExceptionHandlerDecorator implements ExceptionHandlerInterface
    {
        private $decoratedHandler;
        private $logger;

        public function __construct(ExceptionHandlerInterface $decoratedHandler, LoggerInterface $logger)
        {
            $this->decoratedHandler = $decoratedHandler;
            $this->logger = $logger;
        }

        public function handleException(\Throwable $throwable): Response
        {
            $this->logger->error(
                sprintf('Exception caught: %s - %s', get_class($throwable), $throwable->getMessage()),
                ['exception' => $throwable]
            );

            // Delegate to the wrapped handler
            $response = $this->decoratedHandler->handleException($throwable);

            $this->logger->info(
                sprintf('Response generated for %s with status %s', get_class($throwable), $response->getStatusCode())
            );

            return $response;
        }

        public function supportsException(\Throwable $throwable): bool
        {
            // The decorator usually supports the same exceptions as the handler it wraps.
            // It relies on the container's configuration (see below) to be placed correctly in the chain.
            return $this->decoratedHandler->supportsException($throwable);
        }
    }
    ```

*   **Service Configuration (Symfony):**
    Symfony's service decoration feature is used to apply decorators.

    ```yaml
    # config/services.yaml
    services:
        App\Handler\MyCustomExceptionHandler: # The original handler
            # ... (its definition)
            tags:
                - { name: tounaf_exception.handler, priority: 0 }

        App\Handler\Decorator\LoggingExceptionHandlerDecorator:
            decorates: App\Handler\MyCustomExceptionHandler # Specify the service ID of the handler to decorate
            arguments: ['@.inner', '@monolog.logger'] # '@.inner' refers to the decorated (MyCustomExceptionHandler) instance
            # The decorator itself doesn't need the 'tounaf_exception.handler' tag usually,
            # as it operates by wrapping a tagged handler.
            # However, if it were to be a standalone handler that also logs, it would need the tag.
            # For pure decoration of a specific handler, the 'decorates' attribute is key.
            # If the decorator is meant to apply to *all* handlers or a group via priority,
            # it might be tagged with a higher priority itself.
    ```
    Alternatively, if the bundle uses a chain of handlers (see below), a decorator might be tagged with a higher priority to intercept the exception before the primary handler.

**Chain of Responsibility / Handler Priority:**

The bundle processes handlers in a specific order, typically determined by a `priority` attribute on the service tag (as shown in `ExceptionHandlerInterface` and "Basic Usage" sections).

*   The `kernel.exception` event listener within the bundle likely retrieves all services tagged as `tounaf_exception.handler`.
*   It sorts these handlers by priority.
*   It then iterates through them, calling `supportsException()` on each.
*   The first handler to return `true` has its `handleException()` method called, and the process stops.

This is a form of the chain of responsibility pattern. Decorators can be integrated into this chain:

1.  **Specific Decoration (as above):** The decorator replaces the original handler in the container, and the decorator itself calls the original.
2.  **General Decoration via Priority:** A logging decorator could be a separate handler with a very high priority. Its `supportsException()` might return `true` for all (or many) exceptions. It logs the exception and then, instead of returning a `Response`, it could re-throw the exception or call a service that continues iterating through the remaining (lower priority) handlers. However, this is less common than explicit service decoration for this purpose. The Tounaf bundle might have its own specific mechanism for global pre/post processing.

The `Handler` component, therefore, is not just about individual handlers but also the overarching strategy the bundle employs to select and execute them, and to allow for behaviors like decoration.

### 3.4. Negotiation Component (Content Negotiation)

The Negotiation component is responsible for determining the best response format based on the client's request and the server's capabilities. This is commonly known as Content Negotiation, primarily using the `Accept` HTTP header sent by the client.

**Purpose:**

*   To allow a single exception handler endpoint to serve different representations of the error (e.g., JSON for an API client, HTML for a web browser) without needing separate handlers for each format.
*   To gracefully handle cases where the client requests a format the server cannot produce.

**How it Works (General Principles):**

1.  **Client Sends `Accept` Header:** The client includes an `Accept` header in its request, listing the MIME types it can understand, often with quality factors (e.g., `Accept: application/json; q=0.9, text/html; q=0.8, */*; q=0.5`).
2.  **Server Capabilities:** The exception handler (or a service it uses) knows which formats it can generate for a given exception (e.g., `ProductNotFoundException` can be rendered as JSON or HTML).
3.  **Matching:** The Negotiation component compares the client's requested types with the server's available types and selects the best match.
    *   Symfony's `Request::getAcceptableContentTypes()` method is often used to get a list of types from the `Accept` header, ordered by preference.
    *   The bundle might provide a utility or service to perform this matching.

**Integration with `ExceptionHandlerInterface` and `FormatResponse`:**

*   An `ExceptionHandlerInterface` implementation would use a negotiation service (or perform negotiation logic directly) within its `handleException` method.
*   Based on the negotiated format, it would then use an appropriate formatter (from the `FormatResponse` concept) to generate the `Response`.

**Example within an Exception Handler:**

```php
<?php // App\Handler\MyNegotiatingExceptionHandler (Illustrative)

namespace App\Handler;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tounaf\Exception\ExceptionHandlerInterface;
// Assuming some Tounaf\Exception\Negotiation\NegotiatorInterface or similar utility
use Tounaf\Exception\Negotiation\NegotiatorInterface; // Hypothetical interface from the bundle
use Tounaf\Exception\Formatter\JsonFormatter;         // Hypothetical formatter
use Tounaf\Exception\Formatter\HtmlFormatter;         // Hypothetical formatter

class MyNegotiatingExceptionHandler implements ExceptionHandlerInterface
{
    private $requestStack;
    private $negotiator; // Service that handles content negotiation
    private $formatters; // e.g., ['json' => JsonFormatter, 'html' => HtmlFormatter]

    public function __construct(
        RequestStack $requestStack,
        NegotiatorInterface $negotiator, // Injected negotiation service
        iterable $formatters // Tagged collection of formatters
    ) {
        $this->requestStack = $requestStack;
        $this->negotiator = $negotiator;
        foreach ($formatters as $formatter) {
            // Assuming formatters can report their supported format
            $this->formatters[$formatter->getSupportedFormat()] = $formatter;
        }
    }

    public function handleException(\Throwable $throwable): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        $availableFormats = array_keys($this->formatters); // e.g., ['json', 'html']

        // Use the negotiator to get the best format (e.g., 'json', 'html', or a default)
        $format = $this->negotiator->getBestFormat($request, $availableFormats) ?? 'html'; // Default to HTML

        if (isset($this->formatters[$format])) {
            $formatter = $this->formatters[$format];
            // The formatter would take the exception and return a Response
            return $formatter->format($throwable);
        }

        // Fallback if no suitable formatter found (should ideally not happen if negotiator is robust)
        return new Response('Error: Cannot produce a response in the requested format.', Response::HTTP_NOT_ACCEPTABLE);
    }

    public function supportsException(\Throwable $throwable): bool
    {
        return $throwable instanceof \App\Exception\MySpecificApiException;
    }
}
```

**Configuration (Hypothetical `tounaf_exception.yaml`):**

The bundle might offer configuration to simplify content negotiation:

```yaml
# config/packages/tounaf_exception.yaml (Hypothetical)
tounaf_exception:
    # ...
    content_negotiation:
        default_format: 'html'
        format_priorities: # Define order for ambiguous Accept headers
            - 'application/json'
            - 'text/html'
            - 'application/xml'
    handlers:
        App\Handler\MyNegotiatingExceptionHandler:
            supported_formats: ['json', 'html'] # Inform the bundle about this handler's capabilities
```

**Key Points:**

*   The `Negociation` component is crucial for RESTful APIs and applications that need to support multiple client types.
*   It often works hand-in-hand with formatters (`FormatResponse`) to deliver the correctly formatted error.
*   The exact implementation can vary, from direct `Accept` header parsing in the handler to dedicated services and configuration provided by the Tounaf bundle. You would need to check the bundle's specific classes and documentation for `Tounaf\Exception\Negociation` to see its precise tools.

### 3.5. RequestMatcher Component

The `RequestMatcher` component allows you to enable specific exception handlers or groups of handlers based on characteristics of the incoming `Symfony\Component\HttpFoundation\Request` object. This provides fine-grained control over when certain exception handling logic should apply.

**Purpose:**

*   To apply different exception handling strategies for different parts of your application (e.g., `/api` routes vs. web UI routes).
*   To vary exception handling based on request method (GET, POST, etc.), host, IP address, headers, or other request attributes.
*   To enable or disable entire sets of handlers for specific scenarios.

**How it Works:**

The bundle likely uses Symfony's `RequestMatcherInterface` or a similar mechanism. You would define rules or conditions, and if the current request matches these rules, associated exception handlers are considered active for that request.

*   **`Tounaf\Exception\RequestMatcher\RequestMatcherInterface` (or similar):** The bundle might define its own interface or leverage Symfony's. Implementations of this interface would contain the logic to match a request.
*   **Configuration:** You would typically configure these matchers in your `config/packages/tounaf_exception.yaml` or by tagging services.

**Example Scenarios:**

1.  **API vs. Web Exception Handling:**
    You might want JSON responses for exceptions under `/api` and HTML error pages for the rest of the site.

    *   One set of handlers (producing JSON) would be active if the request path starts with `/api`.
    *   Another set of handlers (producing HTML) would be active otherwise.

2.  **Handling Exceptions Differently for a Specific Host:**
    If your application serves multiple domains, you might want custom error pages for one particular domain.

**Implementation (Conceptual):**

An exception handler itself might not directly use a request matcher. Instead, the bundle's central exception listener service would use request matchers to filter or prioritize the available `ExceptionHandlerInterface` services.

**Configuration Example (Hypothetical `tounaf_exception.yaml`):**

```yaml
# config/packages/tounaf_exception.yaml (Hypothetical)
tounaf_exception:
    # ...
    request_matchers:
        api_matcher: # A name for your matcher
            path: '^/api/' # Regular expression for the path
            # host: 'api.example.com'
            # methods: ['POST', 'PUT']
            # ips: ['192.168.1.100']
            # headers: {'X-Custom-Header': 'some-value'}

    handlers:
        # This handler is only active if 'api_matcher' matches the request
        App\Handler\Api\GenericApiExceptionHandler:
            tags:
                - { name: tounaf_exception.handler, priority: 10, request_matcher: api_matcher }

        App\Handler\Web\GenericWebExceptionHandler:
            tags:
                - { name: tounaf_exception.handler, priority: 0 } # No matcher, applies if others don't
```

In this hypothetical configuration:

*   A `request_matcher` named `api_matcher` is defined, which matches any request whose path starts with `/api/`.
*   `GenericApiExceptionHandler` is associated with this `api_matcher`. It will only be considered if the request matches the `/api/` path. Due to its higher `priority`, if it matches and supports the exception, it will handle it.
*   `GenericWebExceptionHandler` has no specific `request_matcher`, so it acts as a default or applies to requests not matching `api_matcher`.

**Actual Usage in `tounaf/exception-handler`:**

*   You'll need to consult the specific documentation or source code of the `tounaf/exception-handler` in the `Tounaf\Exception\RequestMatcher` namespace.
*   The bundle might provide specific `RequestMatcher` classes (e.g., `PathRequestMatcher`, `HostRequestMatcher`) or allow you to define services that implement a relevant interface.
*   The way handlers are associated with these matchers (via service tags, configuration, or compiler passes) is also specific to the bundle.

**Key Advantage:**

Request matchers allow for a very dynamic and context-aware exception handling setup, ensuring that the user experience or API responses are appropriate for the specific type of request that caused the error.

## 5. Advanced Usage

This section provides examples of how to combine the bundle's features for more sophisticated exception handling scenarios.

### 5.1. Advanced Decorator Example: Adding Transaction Rollback and Logging

Imagine you have an operation that involves a database transaction. If an exception occurs, you want to ensure the transaction is rolled back, and the error is logged before the standard response is generated.

1.  **Primary Handler (e.g., for `OrderProcessingException`):**
    This handler focuses solely on generating the correct user-facing error response.

    ```php
    <?php // App\Handler\OrderProcessingExceptionHandler
    namespace App\Handler;

    use App\Exception\OrderProcessingException;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Tounaf\Exception\ExceptionHandlerInterface;

    class OrderProcessingExceptionHandler implements ExceptionHandlerInterface
    {
        public function handleException(\Throwable $throwable): Response
        {
            return new JsonResponse(
                ['error' => 'Order processing failed: ' . $throwable->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        public function supportsException(\Throwable $throwable): bool
        {
            return $throwable instanceof OrderProcessingException;
        }
    }
    ```

2.  **Decorator for Transaction Rollback and Logging:**

    ```php
    <?php // App\Handler\Decorator\TransactionalLoggingDecorator
    namespace App\Handler\Decorator;

    use Doctrine\ORM\EntityManagerInterface; // Or your DBAL connection
    use Psr\Log\LoggerInterface;
    use Symfony\Component\HttpFoundation\Response;
    use Tounaf\Exception\ExceptionHandlerInterface;

    class TransactionalLoggingDecorator implements ExceptionHandlerInterface
    {
        private $decoratedHandler;
        private $entityManager;
        private $logger;

        public function __construct(
            ExceptionHandlerInterface $decoratedHandler,
            EntityManagerInterface $entityManager,
            LoggerInterface $logger
        ) {
            $this->decoratedHandler = $decoratedHandler;
            $this->entityManager = $entityManager;
            $this->logger = $logger;
        }

        public function handleException(\Throwable $throwable): Response
        {
            $this->logger->critical(
                sprintf('[OrderProcessing] Exception: %s', $throwable->getMessage()),
                ['exception' => $throwable, 'trace' => $throwable->getTraceAsString()]
            );

            if ($this->entityManager->getConnection()->isTransactionActive()) {
                try {
                    $this->entityManager->rollback();
                    $this->logger->info('[OrderProcessing] Transaction rolled back due to exception.');
                } catch (\Throwable $e) {
                    $this->logger->error('[OrderProcessing] Failed to roll back transaction: ' . $e->getMessage());
                    // Potentially handle rollback failure, though often just logging is done here
                }
            }

            // Proceed with the original handler to generate the response
            return $this->decoratedHandler->handleException($throwable);
        }

        public function supportsException(\Throwable $throwable): bool
        {
            // This decorator relies on the decorated handler's support logic
            return $this->decoratedHandler->supportsException($throwable);
        }
    }
    ```

3.  **Service Configuration (`services.yaml`):**

    ```yaml
    # config/services.yaml
    services:
        _defaults:
            autowire: true
            autoconfigure: true

        App\Handler\OrderProcessingExceptionHandler:
            tags:
                - { name: tounaf_exception.handler, priority: 0 } # Base handler

        App\Handler\Decorator\TransactionalLoggingDecorator:
            decorates: App\Handler\OrderProcessingExceptionHandler
            # Priority of the decorator itself if it were a standalone handler,
            # but when decorating, it effectively "inherits" the decorated service's call order.
            # Arguments are automatically wired if using autowire, or specify them:
            arguments:
                - '@App\Handler\Decorator\TransactionalLoggingDecorator.inner' # Symfony <4.3 style
                # For Symfony >=4.3, use '@.inner'
                # - '@.inner'
                - '@doctrine.orm.default_entity_manager'
                - '@logger'
            # Ensure this decorator is public if other services might need to reference it directly,
            # though for decoration, this is usually not needed.
            # public: false
    ```
    *Note on `@.inner` vs specific service ID for the decorated service: `@.inner` is generally preferred in modern Symfony versions.*

### 5.2. Content Negotiation for API and Web UI

Let's say an `InvalidInputException` can occur in both an API context (e.g., submitting a form via AJAX) and a traditional web form submission. You want to return JSON for API requests and an HTML error page for web requests.

1.  **The Exception:**
    ```php
    <?php // App\Exception\InvalidInputException
    namespace App\Exception;
    class InvalidInputException extends \RuntimeException { /* ... */ }
    ```

2.  **The Handler using Content Negotiation:**

    ```php
    <?php // App\Handler\InvalidInputExceptionHandler
    namespace App\Handler;

    use App\Exception\InvalidInputException;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Tounaf\Exception\ExceptionHandlerInterface;
    use Twig\Environment as TwigEnvironment; // For HTML rendering

    class InvalidInputExceptionHandler implements ExceptionHandlerInterface
    {
        private $requestStack;
        private $twig;

        public function __construct(RequestStack $requestStack, TwigEnvironment $twig)
        {
            $this->requestStack = $requestStack;
            $this->twig = $twig;
        }

        public function handleException(\Throwable $throwable): Response
        {
            $request = $this->requestStack->getCurrentRequest();
            $acceptableTypes = $request->getAcceptableContentTypes();

            $errorData = [
                'message' => 'Invalid input provided.',
                'details' => $throwable->getMessage(), // Or more structured error details
            ];

            // Check if 'application/json' is an acceptable content type
            if (in_array('application/json', $acceptableTypes)) {
                return new JsonResponse($errorData, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Default to HTML response
            return new Response(
                $this->twig->render('errors/invalid_input.html.twig', ['exception' => $throwable, 'errors' => $errorData]),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        public function supportsException(\Throwable $throwable): bool
        {
            return $throwable instanceof InvalidInputException;
        }
    }
    ```
    *Ensure you have a `templates/errors/invalid_input.html.twig` file.*

3.  **Service Configuration (`services.yaml`):**

    ```yaml
    # config/services.yaml
    services:
        App\Handler\InvalidInputExceptionHandler:
            tags:
                - { name: tounaf_exception.handler, priority: 5 } # Give it a relevant priority
    ```

### 5.3. Using Request Matchers for `/api` prefixed routes

You want all exceptions under `/api/v1/` to be handled by a specific set of handlers that always return JSON responses, possibly with a different error structure.

1.  **Define a Request Matcher Configuration (if the bundle uses `services.yaml` for this):**
    *(This part is highly dependent on how `tounaf/exception-handler` implements request matcher configuration. The following is a common approach.)*

    If the bundle allows defining matchers directly in the tag:

    ```yaml
    # config/services.yaml
    services:
        App\Handler\Api\V1\GenericApiExceptionHandler:
            # This handler would produce a standardized JSON error for API v1
            tags:
                - { 
                    name: tounaf_exception.handler, 
                    priority: 100, # High priority for API routes
                    request_matcher_path: '^/api/v1/' # Custom attribute the bundle might look for
                  }
        # ... other API v1 specific handlers with the same request_matcher_path
    ```

    Alternatively, the bundle might require you to define a request matcher service and reference it:

    ```yaml
    # config/services.yaml
    services:
        app.request_matcher.api_v1:
            class: Symfony\Component\HttpFoundation\RequestMatcher # Or a bundle-specific one
            arguments: # Arguments depend on the matcher class
                - '^/api/v1/' # Path for RequestMatcher

        App\Handler\Api\V1\GenericApiExceptionHandler:
            tags:
                - { 
                    name: tounaf_exception.handler, 
                    priority: 100,
                    request_matcher_service: app.request_matcher.api_v1 # Reference the service
                  }
    ```
    *Consult the `tounaf/exception-handler` documentation for the exact way to configure request matchers (e.g., specific tag attributes like `path`, `host`, or a `request_matcher` service reference).*

2.  **The API Exception Handler (Example):**

    ```php
    <?php // App\Handler\Api\V1\GenericApiExceptionHandler
    namespace App\Handler\Api\V1;

    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
    use Tounaf\Exception\ExceptionHandlerInterface;

    class GenericApiExceptionHandler implements ExceptionHandlerInterface
    {
        public function handleException(\Throwable $throwable): Response
        {
            $statusCode = $throwable instanceof HttpExceptionInterface ? $throwable->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            $errorMessage = $throwable->getMessage() ?: 'API error occurred.';

            return new JsonResponse(
                ['error' => ['message' => $errorMessage, 'type' => get_class($throwable)]],
                $statusCode
            );
        }

        public function supportsException(\Throwable $throwable): bool
        {
            // This generic handler could support all exceptions for the matched path,
            // or you could make it more specific.
            return true; // Active if request_matcher condition is met by the bundle
        }
    }
    ```

**Important Considerations for Advanced Usage:**

*   **Priority Management:** Carefully manage the `priority` of your handlers, especially when using decorators and request matchers, to ensure the correct handler is invoked.
*   **Bundle Specifics:** The exact implementation details for features like service decoration, content negotiation helpers, and request matcher configuration are specific to the `tounaf/exception-handler` bundle. Always refer to its official documentation or source code for the precise service tags, attributes, and available utility classes.
*   **Testing:** Thoroughly test your exception handling setup, including different request types, accept headers, and paths, to ensure errors are handled as expected.

These examples should provide a solid foundation for implementing advanced, robust, and maintainable error handling in your Symfony applications using the Tounaf Exception Handler bundle.

## 6. Contributing

Contributions are highly welcome! Whether you've found a bug, have an idea for a new feature, or want to improve the documentation, please feel free to:

1.  **Open an Issue:** Discuss the bug or feature idea on the GitHub issue tracker for the `tounaf/exception-handler` repository.
2.  **Fork the Repository:** Create your own copy of the `tounaf/exception-handler` repository.
3.  **Create a Branch:** Make a new branch in your fork for your changes (e.g., `feature/my-new-feature` or `bugfix/issue-123`).
4.  **Make Your Changes:** Implement your fix or feature.
    *   Adhere to the existing coding style and standards.
    *   Add or update tests as appropriate to cover your changes.
    *   Ensure your code is well-commented, especially in complex areas.
5.  **Test Your Changes:** Run the test suite to ensure all tests pass (e.g., using PHPUnit).
    ```bash
    # Example command, actual test execution might vary
    composer test 
    ```
6.  **Update Documentation:** If your changes affect the bundle's usage or API, please update this `Readme.md` or other relevant documentation files.
7.  **Submit a Pull Request:** Push your changes to your fork and open a pull request against the main `tounaf/exception-handler` repository.
    *   Provide a clear title and a detailed description of your changes in the pull request.
    *   Link to any relevant issues.

For more detailed guidelines, please check for a `CONTRIBUTING.md` file in the repository. If one doesn't exist, these general steps are a good starting point.

## 7. License

This bundle is typically released under the MIT License. 

Please check the `LICENSE` file in the root of the project's repository (https://github.com/tounaf/exception-handler/blob/master/LICENSE or similar path) for the exact licensing terms.

---

*This Readme.md provides a comprehensive guide to using the Tounaf Exception Handler Bundle. For specific class/method details not covered here, please refer to the source code or generate API documentation.*