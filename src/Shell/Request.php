<?php

namespace Saloon\Shell;

use Saloon\Core\AbstractRequest;
use Saloon\Traits\Commands\HasCommand;

abstract class Request extends AbstractRequest
{
    use HasCommand;
    abstract public function command(): string;

}
