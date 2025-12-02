<?php

namespace Saloon\Contracts;

use Saloon\Http\Faking\MockClient;

interface PendingRequest
{
    public function setFakeResponse(FakeResponse $fakeResponse): static;

    //public function getPendingRequest(): PendingRequest;

    public function getMockClient(): ?MockClient;
}
