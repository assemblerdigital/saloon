<?php

namespace Saloon\Shell;

use Saloon\Core\AbstractConnector;
use Saloon\Shell\Senders\LaravelSender;

abstract class Connector extends AbstractConnector
{
    protected string $defaultSender = LaravelSender::class;

    protected ?string $pendingRequest = PendingRequest::class;

    protected ?string $response = Response::class;
}
