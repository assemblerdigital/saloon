<?php

namespace Saloon\Shell\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\PendingRequest;

class PasswordAuthenticator implements Authenticator
{
    public function set(PendingRequest $pendingRequest): void
    {
        // TODO: illuminate/process doesn't have an authentication component, but perhaps we can emulate such a thing by taking a username and password and using them in a su - command or similar?
        // Leaving this unimplemented for now.
    }
}
