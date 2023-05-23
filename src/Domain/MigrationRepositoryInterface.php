<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Domain;

use Whirlwind\Domain\Repository\RepositoryInterface;

interface MigrationRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array $conditions
     * @param int $limit
     * @param array $order
     * @return Migration[]
     */
    public function findMigrations(array $conditions, int $limit = 0, array $order = []): array;

    /**
     * @param string $name
     * @return void
     */
    public function deleteByName(string $name): void;
}
