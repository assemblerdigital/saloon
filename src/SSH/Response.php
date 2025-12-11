<?php

namespace Saloon\SSH;

use \Illuminate\Contracts\Process\ProcessResult;
use Saloon\Contracts\PendingRequest;
use Saloon\Core\AbstractResponse;
use Symfony\Component\Process\Process;
use Throwable;


// Spatie Shell returns a ProcessResult out of Laravel's Process component, so the Response class is probably going to be identical?
// I'm just going to extend it for now and we'll see if it "just works".
class Response extends \Saloon\Shell\Response
{

}
