<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Core\AbstractConnector;
use Saloon\Traits\HandlesPsrRequest;
use Saloon\Traits\RequestProperties\HasRequestProperties;

abstract class Connector extends AbstractConnector
{
    use HasRequestProperties;
    use HandlesPsrRequest;

    /**
     * Define the base URL of the API.
     */
    abstract public function resolveBaseUrl(): string;
}
