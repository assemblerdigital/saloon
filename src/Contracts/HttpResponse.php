<?php

namespace Saloon\Contracts;

use Throwable;

/**
 * This interface is kept separate from the main Response interface for the sake of keeping backwards compatibility with the constructor signature of the Http/Response class.
 */

interface HttpResponse extends Response
{

}
