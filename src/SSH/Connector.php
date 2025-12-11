<?php

namespace Saloon\SSH;

use Saloon\Core\AbstractConnector;
use Saloon\SSH\Senders\SpatieSSHSender;
use Saloon\Traits\Auth\RequiresAuth;

abstract class Connector extends AbstractConnector
{
    use RequiresAuth;
    protected string $defaultSender = SpatieSSHSender::class;

    protected ?string $pendingRequest = PendingRequest::class;

    protected ?string $response = Response::class;

    protected int $port = 22;
    abstract public function resolveHostname(): string;

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;
        return $this;
    }
}
