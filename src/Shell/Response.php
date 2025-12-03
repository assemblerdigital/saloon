<?php

namespace Saloon\Shell;

use Illuminate\Contracts\Process\ProcessResult;
use Saloon\Contracts\PendingRequest;
use Saloon\Core\AbstractResponse;
use Throwable;

class Response extends AbstractResponse
{
    protected readonly ProcessResult $result;

    public function bootstrap(PendingRequest $pendingRequest, ?Throwable $senderException = null, mixed ...$args): void
    {
        [$this->result] = $args;
    }

    public static function fromProcessResult(ProcessResult $result, PendingRequest $pendingRequest, ?Throwable $exception = null): static
    {
        return new static($pendingRequest, $exception, $result);
    }

    public function body(): string
    {
        return $this->result->output();
    }
}
