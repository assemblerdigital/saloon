<?php

declare(strict_types=1);

namespace Saloon\Core;


use Saloon\Traits\Auth\AuthenticatesRequests; // Leaving this in the base class, might end up moving to child classes later
use Saloon\Traits\Bootable;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasDebugging;
use Saloon\Traits\HasMockClient;
use Saloon\Traits\Macroable;
use Saloon\Traits\Makeable;
use Saloon\Traits\ManagesExceptions;
use Saloon\Traits\PendingRequests\HasCustomPendingRequests;
use Saloon\Traits\Request\CreatesDtoFromResponse;
use Saloon\Traits\RequestProperties\HasTries;
use Saloon\Traits\Responses\HasCustomResponses;

abstract class AbstractRequest
{
    use CreatesDtoFromResponse;
    use HasCustomResponses;
    use ManagesExceptions;
    use HasMockClient;
    use Conditionable;
    use HasDebugging;
    use HasTries;
    use Bootable;
    use Makeable;
    use HasCustomPendingRequests;
    use Macroable;
    use AuthenticatesRequests;
}


