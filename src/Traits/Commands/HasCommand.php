<?php

namespace Saloon\Traits\Commands;

trait HasCommand
{
    /**
     * The shell command. If an array is provided, it will be passed to Process::pipe rather than Process::run
     *
     * @var array|string
     */
    protected array|string $command;

    public function getCommand(): array|string
    {
        return $this->command;
    }

    public function setCommand(array|string $command): static
    {
        $this->command = $command;

        return $this;
    }
}
