<?php

namespace Saloon\Shell;

use Saloon\Core\AbstractConnector;
use Saloon\Shell\Senders\LaravelProcessSender;

abstract class Connector extends AbstractConnector
{
    protected string $defaultSender = LaravelProcessSender::class;

    protected ?string $defaultPendingRequest = PendingRequest::class;

    protected ?string $defaultResponse = Response::class;
}
