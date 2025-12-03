<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

trait HasCustomResponses
{
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
        // We are making the assumption that any alternate PendingRequest classes will exist in the same directory,
        // and namespace as the Connector / Request, and that directory is in the App folder.

        $responseClass = ( str_starts_with(static::class, 'App\\') )
            ? preg_replace('/(?:Connector|Request)$/', 'Response', static::class)
            : null;

        return $this->response ?? ($responseClass && class_exists($responseClass) ? $responseClass : null);
    }
}
