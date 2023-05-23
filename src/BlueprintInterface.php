<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore;

use Whirlwind\Infrastructure\Persistence\ConnectionInterface;

interface BlueprintInterface
{
    public function build(ConnectionInterface $connection): void;
    public function create(callable $callback): void;
    public function drop(): void;
    public function dropIfExists(): void;
    public function createIfNotExists(callable $callback): void;
}
