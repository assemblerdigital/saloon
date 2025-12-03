<?php

namespace Saloon\Shell\Senders;

use Exception;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\Process;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Response;
use Saloon\Contracts\Sender;
use Saloon\Exceptions\Request\FatalRequestException;


class LaravelSender implements Sender
{
    public function send(PendingRequest $pendingRequest): Response
    {
        try {
            $result = Process::run($pendingRequest->getCommand())->throw();

            return $this->createResponse($result, $pendingRequest);

        } catch (ProcessFailedException|ProcessTimedOutException $e) {

            return $this->createResponse($e->result, $pendingRequest, $e);

        } catch (\Throwable $e) {

            throw new FatalRequestException($e, $pendingRequest);
        }
    }

    //TODO: Potentially implement async later - Laravel's Process supports it, but we'd have to build some kind of Promise wrapper around it, because Saloon uses Guzzle's Promises for HTTP requests.

    /**
     * Create a response.
     */
    protected function createResponse(ProcessResult $result, PendingRequest $pendingRequest, ?Exception $exception = null): Response
    {
        /** @var class-string<\Saloon\Shell\Response> $responseClass */
        $responseClass = $pendingRequest->getResponseClass();

        return $responseClass::fromProcessResult($result, $pendingRequest, $exception);
    }

}
