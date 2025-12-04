<?php

namespace Saloon\Shell;

use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Core\AbstractPendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Traits\Commands\HasCommand;

class PendingRequest extends AbstractPendingRequest
{
    use HasCommand;

    public function bootstrap(Connector $connector, Request $request, ?MockClient $mockClient = null): void
    {
        $this->command  = $request->getCommand();
    }

}
