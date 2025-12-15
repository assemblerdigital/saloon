<?php

namespace Saloon\Traits\PendingRequests;

/**
 * Trait HasCustomPendingRequests
 *
 * This trait allows a connector or request to specify a custom PendingRequest class.
 */
trait HasCustomPendingRequests
{
    /**
     * Specify a default PendingRequest.
     *
     * @var class-string<\Saloon\Contracts\PendingRequest>|null
     */
    protected ?string $defaultPendingRequest = null;

    /**
     * Your custom PendingRequest.
     *
     * @var class-string<\Saloon\Contracts\PendingRequest>|null
     */
    protected ?string $pendingRequest = null;

    /**
     * Resolve the custom PendingRequest class
     *
     * @return class-string<\Saloon\Contracts\PendingRequest>|null
     */
    public function resolvePendingRequestClass(): ?string
    {
        if ($this->pendingRequest && class_exists($this->pendingRequest)) {
            return $this->pendingRequest;
        }

        return $this->defaultPendingRequest;
    }
}
