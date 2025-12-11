<?php

namespace Saloon\SSH\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\PendingRequest;

class PasswordAuthenticator implements Authenticator
{
    /**
     * Constructor
     */
    public function __construct(
        public ?string $username,
        public string $password,
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->setUsername($this->username);
        $pendingRequest->setPassword($this->password);
    }

}
