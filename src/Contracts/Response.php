<?php

namespace Saloon\Contracts;

use Throwable;

interface Response
{
    public function __construct(PendingRequest $pendingRequest, ?Throwable $senderException = null, ?array $args = null);
}
