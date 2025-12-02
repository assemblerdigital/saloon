<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface Sender
{
    /**
     * Send the request synchronously
     */
    public function send(PendingRequest $pendingRequest): Response;
}
