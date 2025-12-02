<?php

namespace Saloon\Contracts;

interface PendingRequest
{
    public function setFakeResponse(FakeResponse $fakeResponse): static;
}
