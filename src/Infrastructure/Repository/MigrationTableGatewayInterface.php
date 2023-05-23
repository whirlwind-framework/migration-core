<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Infrastructure\Repository;

use Whirlwind\Infrastructure\Repository\TableGateway\TableGatewayInterface;

interface MigrationTableGatewayInterface extends TableGatewayInterface
{
    public function queryOrCreateCollection(array $conditions = [], int $limit = 0, array $order = []): array;
}
