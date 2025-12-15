<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

trait HasCustomResponses
{
    /**
     * Specify a default PendingRequest.
     *
     * @var class-string<\Saloon\Contracts\PendingRequest>|null
     */
    protected ?string $defaultResponse = null;

    /**
     * Specify a default response.
     *
     * When null or an empty string, the response on the sender will be used.
     *
     * @var class-string<\Saloon\Contracts\Response>|null
     */
    protected ?string $response = null;

    /**
     * Resolve the custom response class
     *
     * @return class-string<\Saloon\Contracts\Response>|null
     */
    public function resolveResponseClass(): ?string
    {
        if ($this->response && class_exists($this->response)) {
            return $this->response;
        }

        return $this->defaultResponse;
    }
}
