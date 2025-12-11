<?php

namespace Saloon\SSH\Senders;

use Exception;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Response;
use Saloon\Contracts\Sender;
use Saloon\Exceptions\Request\FatalRequestException;
use Spatie\Ssh\Ssh;


class SpatieSSHSender implements Sender
{
    public function send(PendingRequest $pendingRequest): Response
    {
        try {
            $client = Ssh::Create($pendingRequest->getUsername(), $pendingRequest->getHost());

            if ($pendingRequest->getPort()) {
                $client = $client->usePort($pendingRequest->getPort());
            }

            if ($pendingRequest->getPrivateKeyPath()) {
                $client = $client->usePrivateKey($pendingRequest->getPrivateKeyPath());
            } elseif ($pendingRequest->getPassword()) {
                $client = $client->usePassword($pendingRequest->getPassword());
            } else {
                throw new FatalRequestException(new Exception('No authentication method provided for SSH connection.'), $pendingRequest);
            }

            $result = $client->execute($pendingRequest->getCommand());

            return $this->createResponse(new \Illuminate\Process\ProcessResult($result), $pendingRequest);

        } catch (ProcessFailedException|ProcessTimedOutException $e) {
            // Given Spatie's SSH package is a pretty thin wrapper around Symfony Process, we can assume that it throws the same exceptions.
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
