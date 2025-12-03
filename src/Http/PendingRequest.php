<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Core\AbstractPendingRequest;
use Saloon\Enums\Method;
use Saloon\Contracts\Request;
use Saloon\Helpers\URLHelper;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\PendingRequest\MergeBody;
use Saloon\Http\PendingRequest\MergeDelay;
use Saloon\Traits\PendingRequest\ManagesPsrRequests;
use Saloon\Http\PendingRequest\MergeRequestProperties;
use Saloon\Traits\RequestProperties\HasRequestProperties;


class PendingRequest extends AbstractPendingRequest
{

    use HasRequestProperties;
    use ManagesPsrRequests;

    /**
     * The method the request will use.
     */
    protected Method $method;

    /**
     * The URL the request will be made to.
     */
    protected string $url;

    /**
     * The body of the request.
     */
    protected ?BodyRepository $body = null;

    /**
     * Determine if the pending request is asynchronous - this is a PSR feature.
     */
    protected bool $asynchronous = false;

    /**
     * Build up the request payload.
     */
    public function make(Connector $connector, Request $request, ?MockClient $mockClient = null): void
    {
        // Let's start by getting our PSR factory collection. This object contains all the
        // relevant factories for creating PSR-7 requests as well as URIs and streams.

        $this->factoryCollection = $connector->sender()->getFactoryCollection();

        // Now we'll set the base properties

        $this->method = $request->getMethod();
        $this->url = URLHelper::join($this->connector->resolveBaseUrl(), $this->request->resolveEndpoint());

        // Tap the specific middleware for this request type.
        $this
            ->tap(new MergeRequestProperties)
            ->tap(new MergeBody)
            ->tap(new MergeDelay);

    }

    /**
     * Get the URL of the request.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the URL of the PendingRequest
     *
     * Note: This will be combined with the query parameters to create
     * a UriInterface that will be passed to a PSR-7 request.
     *
     * @return $this
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the HTTP method used for the request
     */
    public function getMethod(): Method
    {
        return $this->method;
    }

    /**
     * Set the method of the PendingRequest
     *
     * @return $this
     */
    public function setMethod(Method $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Retrieve the body on the instance
     */
    public function body(): ?BodyRepository
    {
        return $this->body;
    }

    /**
     * Set the body repository
     *
     * @return $this
     */
    public function setBody(?BodyRepository $body): static
    {
        $this->body = $body;

        return $this;
    }


    /**
     * Check if the request is asynchronous
     */
    public function isAsynchronous(): bool
    {
        return $this->asynchronous;
    }

    /**
     * Set if the request is going to be sent asynchronously
     *
     * @return $this
     */
    public function setAsynchronous(bool $asynchronous): static
    {
        $this->asynchronous = $asynchronous;

        return $this;
    }




}
