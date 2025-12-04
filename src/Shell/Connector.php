<?php

namespace Saloon\Shell;

use Saloon\Core\AbstractConnector;
use Saloon\Shell\Senders\LaravelProcessSender;

abstract class Connector extends AbstractConnector
{
    protected string $defaultSender = LaravelProcessSender::class;

    protected ?string $pendingRequest = PendingRequest::class;

    protected ?string $response = Response::class;
}
