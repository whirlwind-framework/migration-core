<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore;

use Psr\Container\ContainerInterface;

class MigrationCreator
{
    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    public function create(string $migrationClass): Migration
    {
        if (!\is_subclass_of($migrationClass, Migration::class)) {
            throw new \InvalidArgumentException('Class is not a subclass of ' . Migration::class);
        }

        return $this->container->get($migrationClass);
    }
}
