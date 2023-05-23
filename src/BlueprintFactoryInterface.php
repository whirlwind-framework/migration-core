<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore;

interface BlueprintFactoryInterface
{
    public function create(string $collection): BlueprintInterface;
}
