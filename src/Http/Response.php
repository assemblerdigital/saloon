<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Contracts\HttpResponse;
use Saloon\Contracts\PendingRequest;
use Saloon\Core\AbstractResponse;
use Throwable;
use LogicException;
use InvalidArgumentException;
use Saloon\Repositories\ArrayStore;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

class Response extends AbstractResponse implements HttpResponse
{
    /**
     * The PSR request
     */
    protected readonly RequestInterface $psrRequest;

    /**
     * The PSR response from the sender.
     */
    protected readonly ResponseInterface $psrResponse;


    /**
     * Create a new response instance.
     */
    public function bootstrap(PendingRequest $pendingRequest, ?Throwable $senderException = null, mixed ...$args): void
    {
        [$this->psrResponse, $this->psrRequest] = $args;
    }

    /**
     * Create a new response instance
     */
    public static function fromPsrResponse(ResponseInterface $psrResponse, PendingRequest $pendingRequest, RequestInterface $psrRequest, ?Throwable $senderException = null): static
    {
        return new static($pendingRequest, $senderException, $psrResponse, $psrRequest);
    }

    /**
     * Get the PSR-7 request
     */
    public function getPsrRequest(): RequestInterface
    {
        return $this->psrRequest;
    }

    /**
     * Create a PSR response from the raw response.
     */
    public function getPsrResponse(): ResponseInterface
    {
        return $this->psrResponse;
    }

    /**
     * Get the body of the response as string.
     */
    public function body(): string
    {
        $stream = $this->stream();

        $contents = $stream->getContents();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return $contents;
    }

    /**
     * Get the body as a stream.
     */
    public function stream(): StreamInterface
    {
        $stream = $this->psrResponse->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return $stream;
    }

    /**
     * Get the headers from the response.
     */
    public function headers(): ArrayStoreContract
    {
        $headers = array_map(static function (array $header) {
            return count($header) === 1 ? $header[0] : $header;
        }, $this->psrResponse->getHeaders());

        return new ArrayStore($headers);
    }

    /**
     * Get the status code of the response.
     */
    public function status(): int
    {
        return $this->psrResponse->getStatusCode();
    }


    /**
     * Convert the response to a data URL
     */
    public function dataUrl(): string
    {
        return 'data:'.$this->psrResponse->getHeaderLine('Content-Type').';base64,'.base64_encode($this->body());
    }


    /**
     * Get a header from the response.
     *
     * @return string|array<array-key, mixed>|null
     */
    public function header(string $header): string|array|null
    {
        return $this->headers()->get($header);
    }

    /**
     * Determine if the response is in JSON format.
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type');

        if (is_null($contentType)) {
            return false;
        }

        $contentType = is_array($contentType) ? $contentType[0] : $contentType;

        return str_contains($contentType, 'json');
    }

    /**
     * Determine if the response is in XML format.
     */
    public function isXml(): bool
    {
        $contentType = $this->header('Content-Type');

        if (is_null($contentType)) {
            return false;
        }

        $contentType = is_array($contentType) ? $contentType[0] : $contentType;

        return str_contains($contentType, 'xml');
    }

    /**
     * Create a temporary resource for the stream.
     *
     * Useful for storing the file. Make sure to close the raw stream after you have used it.
     *
     * @return resource
     */
    public function getRawStream(): mixed
    {
        $temporaryResource = fopen('php://temp', 'wb+');

        if ($temporaryResource === false) {
            throw new LogicException('Unable to create a temporary resource for the stream.');
        }

        $this->saveBodyToFile($temporaryResource, false);

        return $temporaryResource;
    }

    /**
     * Save the body to a file
     *
     * @param string|resource $resourceOrPath
     */
    public function saveBodyToFile(mixed $resourceOrPath, bool $closeResource = true): void
    {
        if (! is_string($resourceOrPath) && ! is_resource($resourceOrPath)) {
            throw new InvalidArgumentException('The $resourceOrPath argument must be either a file path or a resource.');
        }

        $resource = is_string($resourceOrPath) ? fopen($resourceOrPath, 'wb+') : $resourceOrPath;

        if ($resource === false) {
            throw new LogicException('Unable to open the resource.');
        }

        rewind($resource);

        $stream = $this->stream();

        while (! $stream->eof()) {
            fwrite($resource, $stream->read(1024));
        }

        rewind($resource);

        if ($closeResource === true) {
            fclose($resource);
        }
    }

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close(): static
    {
        $this->stream()->close();

        return $this;
    }


}
