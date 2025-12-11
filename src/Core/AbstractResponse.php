<?php

namespace Saloon\Core;

use Illuminate\Support\Collection;
use LogicException;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Contracts\FakeResponse;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Response;
use Saloon\Helpers\ArrayHelpers;
use Saloon\Helpers\ObjectHelpers;
use Saloon\Helpers\RequestExceptionHelper;
use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Traits\Macroable;
use Saloon\XmlWrangler\XmlReader;
use SimpleXMLElement;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

abstract class AbstractResponse implements Response
{
    use Macroable;

    /**
     * The pending request that has all the request properties
     */
    protected readonly PendingRequest $pendingRequest;

    /**
     * The original sender exception
     */
    protected ?Throwable $senderException = null;

    /**
     * The decoded JSON response.
     *
     * @var array<array-key, mixed>
     */
    protected array $decodedJson;

    /**
     * The decoded JSON response object.
     */
    protected mixed $decodedJsonObject;

    /**
     * The decoded XML response.
     */
    protected string $decodedXml;

    /**
     * Denotes if the response has been mocked.
     */
    protected bool $mocked = false;

    /**
     * Denotes if the response has been cached.
     */
    protected bool $cached = false;

    /**
     * The simulated response payload if the response was simulated.
     */
    protected ?FakeResponse $fakeResponse = null;

    /**
     * Create a new response instance. This has had the parameters reordered to make it more reusable across different response types.
     */
    final public function __construct(mixed $response, PendingRequest $pendingRequest, ?Throwable $senderException = null, mixed ...$params)
    {
        $this->coreBootstrap($pendingRequest, $senderException);
        $this->bootstrap(...func_get_args());
    }

    private function coreBootstrap(PendingRequest $pendingRequest, ?Throwable $senderException = null): void
    {
        $this->pendingRequest = $pendingRequest;
        $this->senderException = $senderException;
    }
    abstract public function bootstrap(mixed $response, PendingRequest $pendingRequest, ?Throwable $senderException = null, mixed ...$params): void;

    /**
     * Get the pending request that created the response.
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }

    /**
     * Get the connector that sent the request
     */
    public function getConnector(): Connector
    {
        return $this->pendingRequest->getConnector();
    }

    /**
     * Get the original request that created the response.
     */
    public function getRequest(): Request
    {
        return $this->pendingRequest->getRequest();
    }

    /**
     * Get the status code of the response.
     */
    abstract public function status(): int;

    abstract public function statusMessage(): ?string;

    /**
     * Get the original sender exception
     */
    public function getSenderException(): ?Throwable
    {
        return $this->senderException;
    }

    /**
     * Get the body of the response as string.
     */
    abstract public function body(): string;

    /**
     * Cast the response to a DTO.
     */
    public function dto(): mixed
    {
        $request = $this->pendingRequest->getRequest();
        $connector = $this->pendingRequest->getConnector();

        $dataObject = $request->createDtoFromResponse($this) ?? $connector->createDtoFromResponse($this);

        if ($dataObject instanceof WithResponse) {
            $dataObject->setResponse($this);
        }

        return $dataObject;
    }

    /**
     * Convert the response into a DTO or throw a LogicException if the response failed
     */
    public function dtoOrFail(): mixed
    {
        if ($this->failed()) {
            throw new LogicException('Unable to create data transfer object as the response has failed.', 0, $this->toException());
        }

        return $this->dto();
    }

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param array-key|null $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function json(string|int|null $key = null, mixed $default = null): mixed
    {
        if (! isset($this->decodedJson)) {
            $this->decodedJson = json_decode($this->body() ?: '[]', true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_null($key)) {
            return $this->decodedJson;
        }

        return ArrayHelpers::get($this->decodedJson, $key, $default);
    }

    /**
     * Get the JSON decoded body as an array. Provide a key to find a specific item in the JSON.
     *
     * Alias of json()
     *
     * @param array-key|null $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function array(int|string|null $key = null, mixed $default = null): mixed
    {
        return $this->json($key, $default);
    }

    /**
     * Get the JSON decoded body of the response as an object or scalar value.
     */
    public function object(string|int|null $key = null, mixed $default = null): mixed
    {
        if (! isset($this->decodedJsonObject)) {
            $this->decodedJsonObject = json_decode($this->body() ?: '{}', false, 512, JSON_THROW_ON_ERROR);
        }

        if (is_null($key)) {
            return $this->decodedJsonObject;
        }

        return ObjectHelpers::get($this->decodedJsonObject, (string)$key, $default);
    }

    /**
     * Convert the XML response into a SimpleXMLElement.
     *
     * Suitable for reading small, simple XML responses but not suitable for
     * more advanced XML responses with namespaces and prefixes. Consider
     * using the xmlReader method instead for better compatibility.
     *
     * @see https://www.php.net/manual/en/book.simplexml.php
     */
    public function xml(mixed ...$arguments): SimpleXMLElement|bool
    {
        if (! isset($this->decodedXml)) {
            $this->decodedXml = $this->body();
        }

        return simplexml_load_string($this->decodedXml, ...$arguments);
    }

    /**
     * Load the XML response into a reader
     *
     * Suitable for reading large XML responses and supports a wider range of XML
     * documents. Requires XML Wrangler (composer require saloonphp/xml-wrangler)
     *
     * @see https://github.com/saloonphp/xml-wrangler
     */
    public function xmlReader(): XmlReader
    {
        //TODO: This is an external plugin, so can't override the return type. Let's not use it for now.
        return XmlReader::fromSaloonResponse($this);
    }

    /**
     * Get the JSON decoded body of the response as a collection.
     *
     * Requires Laravel Collections (composer require illuminate/collections)
     * @see https://github.com/illuminate/collections
     *
     * @param array-key|null $key
     * @return \Illuminate\Support\Collection<array-key, mixed>
     */
    public function collect(string|int|null $key = null): Collection
    {
        $data = $this->json($key);

        if (is_null($data)) {
            return Collection::empty();
        }

        if (is_array($data)) {
            return Collection::make($data);
        }

        return Collection::make([$data]);
    }

    /**
     * Parse the HTML or XML body into a Symfony DomCrawler instance.
     *
     * Requires Symfony Crawler (composer require symfony/dom-crawler)
     *
     * @see https://symfony.com/doc/current/components/dom_crawler.html
     */
    public function dom(): Crawler
    {
        return new Crawler($this->body());
    }

    /**
     * Determine if the request was successful.
     */
    abstract public function successful(): bool;

    /**
     * Determine if the response code was "OK".
     */
    abstract public function ok(): bool;

    /**
     * Determine if the response indicates a client or server error occurred.
     */
    public function failed(): bool
    {
        $pendingRequest = $this->getPendingRequest();

        $requestFailedAccordingToConnector = $pendingRequest->getConnector()->hasRequestFailed($this);
        $requestFailedAccordingToRequest = $pendingRequest->getRequest()->hasRequestFailed($this);

        if ($requestFailedAccordingToRequest !== null || $requestFailedAccordingToConnector !== null) {
            return $requestFailedAccordingToRequest || $requestFailedAccordingToConnector;
        }

        return $this->serverError() || $this->clientError();
    }

    /**
     * Determine if the response indicates a client error occurred.
     */
    abstract public function clientError(): bool;

    /**
     * Determine if the response indicates a server error occurred.
     */
    abstract public function serverError(): bool;

    /**
     * Execute the given callback if there was a server or client error.
     *
     * @param callable($this): (void) $callback
     * @return $this
     */
    public function onError(callable $callback): static
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Determine if the response should throw a request exception.
     */
    public function shouldThrowRequestException(): bool
    {
        $pendingRequest = $this->getPendingRequest();

        return $pendingRequest->getRequest()->shouldThrowRequestException($this) || $pendingRequest->getConnector()->shouldThrowRequestException($this);
    }

    /**
     * Create an exception if a server or client error occurred.
     */
    public function toException(): ?Throwable
    {
        if (! $this->shouldThrowRequestException()) {
            return null;
        }

        return $this->createException();
    }

    /**
     * Create the request exception
     */
    protected function createException(): Throwable
    {
        $pendingRequest = $this->getPendingRequest();
        $senderException = $this->getSenderException();

        // We'll first check if the user has defined their own exception handlers.
        // We'll prioritise the request over the connector.

        $exception = $pendingRequest->getRequest()->getRequestException($this, $senderException) ?? $pendingRequest->getConnector()->getRequestException($this, $senderException);

        if ($exception instanceof Throwable) {
            return $exception;
        }

        // Otherwise, we'll throw our own request.

        return RequestExceptionHelper::create($this, $senderException);
    }

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     * @throws \Throwable
     */
    public function throw(): static
    {
        if ($this->shouldThrowRequestException()) {
            throw $this->toException();
        }

        return $this;
    }

    /**
     * Get the body of the response.
     */
    public function __toString(): string
    {
        return $this->body();
    }

    /**
     * Check if the response has been cached
     */
    public function isCached(): bool
    {
        return $this->cached;
    }

    /**
     * Check if the response has been mocked
     */
    public function isMocked(): bool
    {
        return $this->mocked;
    }

    /**
     * Check if the response has been simulated
     */
    public function isFaked(): bool
    {
        return $this->isMocked() || $this->isCached();
    }

    /**
     * Set if a response has been cached or not.
     *
     * @return $this
     */
    public function setCached(bool $value): static
    {
        $this->cached = true;

        return $this;
    }

    /**
     * Set if a response has been mocked or not.
     *
     * @return $this
     */
    public function setMocked(bool $value): static
    {
        $this->mocked = true;

        return $this;
    }

    /**
     * Set the simulated response payload if the response was simulated.
     *
     * @return $this
     */
    public function setFakeResponse(FakeResponse $fakeResponse): static
    {
        $this->fakeResponse = $fakeResponse;

        return $this;
    }

    /**
     * Get the simulated response payload if the response was simulated.
     */
    public function getFakeResponse(): ?FakeResponse
    {
        return $this->fakeResponse;
    }

}
