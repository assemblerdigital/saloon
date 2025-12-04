<?php

namespace Saloon\Shell;

use Illuminate\Contracts\Process\ProcessResult;
use Saloon\Contracts\PendingRequest;
use Saloon\Core\AbstractResponse;
use Throwable;


class Response extends AbstractResponse
{
    // Return code groupings based on descriptions from Symfony\Component\Process\Process::exitCodes.
    // This is entirely unscientific and not based on any formal standard.
    public const CLIENT_ERROR_CODES = [
        1, 2, 126, 127, 128, 130,
    ];

    public const SERVER_ERROR_CODES = [
        131, 132, 133, 134, 135, 136, 137, 139,
        141, 142, 143, 145, 146, 147, 148,
        149, 150, 151, 152, 153, 154, 155, 159,
    ];

    protected readonly ProcessResult $processResponse;

    public function bootstrap(mixed $response, PendingRequest $pendingRequest, ?Throwable $senderException = null, mixed ...$params): void
    {
        $this->processResponse = $response;
    }

    public static function fromProcessResult(ProcessResult $processResponse, PendingRequest $pendingRequest, ?Throwable $exception = null): static
    {
        return new static($processResponse, $pendingRequest, $exception);
    }

    public function status(): int
    {
        return $this->processResponse->exitCode();
    }

    public function body(): string
    {
        return $this->processResponse->output();
    }

    public function successful(): bool
    {
        return $this->processResponse->successful();
    }

    public function ok(): bool
    {
        return $this->processResponse->successful();
    }

    public function clientError(): bool
    {
        // If the exit code is in the client-like range, return true
        return in_array($this->status(), self::CLIENT_ERROR_CODES, true);
    }

    public function serverError(): bool
    {
        // If the exit code is in the server-like range, return true
        return in_array($this->status(), self::SERVER_ERROR_CODES, true);
    }
}
