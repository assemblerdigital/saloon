<?php

declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Authenticator;

class HeaderAuthenticator implements Authenticator
{
    /**
     * Constructor
     */
    public function __construct(
        public string $accessToken,
        public string $headerName = 'Authorization',
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add($this->headerName, $this->accessToken);
    }
}
