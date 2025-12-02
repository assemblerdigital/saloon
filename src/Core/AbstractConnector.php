<?php

declare(strict_types=1);

namespace Saloon\Core;

use Saloon\Contracts\Connector;
use Saloon\Traits\Bootable;
use Saloon\Traits\Makeable;
use Saloon\Traits\HasDebugging;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Saloon\Traits\ManagesExceptions;
use Saloon\Traits\Connector\SendsRequests;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Traits\PendingRequests\HasCustomPendingRequests;
use Saloon\Traits\RequestProperties\HasTries;
use Saloon\Traits\Responses\HasCustomResponses;
use Saloon\Traits\Request\CreatesDtoFromResponse;

abstract class AbstractConnector implements Connector
{
    use CreatesDtoFromResponse;
    use AuthenticatesRequests;
    use HasCustomResponses;
    use ManagesExceptions;
    use HasMockClient;
    use SendsRequests;
    use Conditionable;
    use Bootable;
    use Makeable;
    use HasTries;
    use HasDebugging;
    use HasCustomPendingRequests;
}
