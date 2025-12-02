<?php

namespace Saloon\Traits\PendingRequests;

trait HasCustomPendingRequests
{
    /**
     * Specify a default response.
     *
     * When null or an empty string, the response on the sender will be used.
     *
     * @var class-string<\Saloon\Contracts\PendingRequest>|null
     */
    protected ?string $pendingRequest = null;

    /**
     * Resolve the custom response class
     *
     * @return class-string<\Saloon\Contracts\PendingRequest>|null
     */
    public function resolvePendingRequestClass(): ?string
    {
        // We are making the assumption that any alternate PendingRequest classes will exist in the same directory,
        // and namespace as the Connector / Request, and that directory is in the App folder.

        $pendingRequestClass = ( str_starts_with(static::class, 'App\\') )
            ? preg_replace('/(?:Connector|Request)$/', 'PendingRequest', static::class)
            : null;

        return $this->pendingRequest ?? $pendingRequestClass ?? null;
    }
}
