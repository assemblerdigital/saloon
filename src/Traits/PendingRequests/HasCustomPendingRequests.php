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
        // and namespace, as the Connector class, but not in the base Saloon\Http namespace.

        $pendingRequestClass = ( ! str_starts_with(static::class, 'Saloon\\Http\\') && ! str_starts_with(static::class, 'Saloon\\Tests\\')  )
            ? preg_replace('/Connector$/', 'PendingRequest', static::class)
            : null;

        return $this->pendingRequest ?? $pendingRequestClass;
    }
}
