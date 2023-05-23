<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore;

use Whirlwind\MigrationCore\Blueprint\Command;
use Whirlwind\Infrastructure\Persistence\ConnectionInterface;

abstract class Blueprint implements BlueprintInterface
{
    /**
     * @var Command[]
     */
    protected array $commands = [];

    public function __construct(
        protected string $collection
    ) {
    }

    public function addCommand(Command $command): void
    {
        $this->commands[] = $command;
    }

    public function prependCommand(Command $command): void
    {
        \array_unshift($this->commands, $command);
    }

    public function build(ConnectionInterface $connection): void
    {
        foreach ($this->commands as $command) {
            $command->apply($connection);
        }
    }
}
