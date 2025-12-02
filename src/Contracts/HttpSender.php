<?php

namespace Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Data\FactoryCollection;

interface HttpSender extends Sender
{
    /**
     * Get the factory collection
     */
    public function getFactoryCollection(): FactoryCollection;

    /**
     * Send the request asynchronously
     */
    public function sendAsync(PendingRequest $pendingRequest): PromiseInterface;
}
