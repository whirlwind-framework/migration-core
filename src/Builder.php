<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore;

use Whirlwind\Infrastructure\Persistence\ConnectionInterface;

class Builder
{
    public function __construct(
        protected ConnectionInterface $connection,
        protected BlueprintFactoryInterface $blueprintFactory
    ) {
    }

    public function create(string $collection, callable $callback): void
    {
        $this->build(
            $this->blueprintFactory->create($collection),
            function (BlueprintInterface $b) use ($callback): void {
                $b->create($callback);
            }
        );
    }

    protected function build(BlueprintInterface $blueprint, callable $callback): void
    {
        $callback($blueprint);
        $blueprint->build($this->connection);
    }

    public function drop(string $collection): void
    {
        $this->build(
            $this->blueprintFactory->create($collection),
            fn(BlueprintInterface $b) => $b->drop()
        );
    }

    public function dropIfExists(string $collection): void
    {
        $this->build(
            $this->blueprintFactory->create($collection),
            fn(BlueprintInterface $b) => $b->dropIfExists()
        );
    }

    public function modify(string $collection, callable $callback): void
    {
        $this->build($this->blueprintFactory->create($collection), $callback);
    }

    public function createIfNotExists(string $collection, callable $callback): void
    {
        $this->build(
            $this->blueprintFactory->create($collection),
            function (BlueprintInterface $b) use($callback): void {
                $b->createIfNotExists($callback);
            }
        );
    }
}
