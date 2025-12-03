<?php

namespace Saloon\Traits\Commands;

trait HasCommand
{
    protected string $command;

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): static
    {
        $this->command = $command;

        return $this;
    }
}
