<?php

namespace Saloon\SSH\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\PendingRequest;

class KeyAuthenticator implements Authenticator
{
    /**
     * Constructor
     */
    public function __construct(
        public ?string $username,
        public string $sshKeyPath,
    ) { }

    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->setUsername($this->username);
        $pendingRequest->setPrivateKeyPath($this->sshKeyPath);
    }
}
