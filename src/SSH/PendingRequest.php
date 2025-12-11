<?php

namespace Saloon\SSH;

use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Core\AbstractPendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Traits\Commands\HasCommand;

class PendingRequest extends AbstractPendingRequest
{
    use HasCommand;

    protected ?string $host;
    protected ?int $port = null;
    protected ?string $username = null;
    protected ?string $privateKeyPath = null;
    protected ?string $password = null;

    public function bootstrap(Connector $connector, Request $request, ?MockClient $mockClient = null): void
    {
        $this->host = $connector->resolveHostname();
        $this->port = $connector->getPort();

        $this->command  = $request->resolveCommand();
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     */
    public function setHost(?string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int|null $port
     */
    public function setPort(?int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string|null
     */
    public function getPrivateKeyPath(): ?string
    {
        return $this->privateKeyPath;
    }

    /**
     * @param string|null $privateKeyPath
     */
    public function setPrivateKeyPath(?string $privateKeyPath): void
    {
        // TODO: Validate that the path exists?
        $this->privateKeyPath = $privateKeyPath;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

}
