<?php

declare(strict_types=1);

namespace Saloon\Http;

use LogicException;
use Saloon\Core\AbstractRequest;
use Saloon\Enums\Method;
use Saloon\Traits\HandlesPsrRequest;
use Saloon\Traits\RequestProperties\HasRequestProperties;

abstract class Request extends AbstractRequest
{
    use HasRequestProperties;
    use HandlesPsrRequest;

    /**
     * Define the HTTP method.
     */
    protected Method $method;

    /**
     * Get the method of the request.
     */
    public function getMethod(): Method
    {
        if (! isset($this->method)) {
            throw new LogicException('Your request is missing a HTTP method. You must add a method property like [protected Method $method = Method::GET]');
        }

        return $this->method;
    }

    /**
     * Define the endpoint for the request.
     */
    abstract public function resolveEndpoint(): string;
}
