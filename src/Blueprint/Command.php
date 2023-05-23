<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Blueprint;

use Whirlwind\Infrastructure\Persistence\ConnectionInterface;

abstract class Command
{
    public function __construct(
        protected string $collection,
        array $args = []
    ) {
        foreach ($args as $property => $value) {
            $this->$property = $value;
        }
    }

    abstract public function apply(ConnectionInterface $connection);
}
