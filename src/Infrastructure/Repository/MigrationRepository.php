<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Infrastructure\Repository;

use Whirlwind\MigrationCore\Domain\MigrationRepositoryInterface;
use Whirlwind\Domain\Repository\ResultFactoryInterface;
use Whirlwind\Infrastructure\Hydrator\Hydrator;
use Whirlwind\Infrastructure\Repository\Relation\RelationCollection;
use Whirlwind\Infrastructure\Repository\Repository;

/**
 * @property MigrationTableGatewayInterface $tableGateway
 */
class MigrationRepository extends Repository implements MigrationRepositoryInterface
{
    public function __construct(
        MigrationTableGatewayInterface $tableGateway,
        Hydrator $hydrator,
        string $modelClass,
        ResultFactoryInterface $resultFactory,
        RelationCollection $relationCollection = null
    ) {
        parent::__construct($tableGateway, $hydrator, $modelClass, $resultFactory, $relationCollection);
    }

    public function findMigrations(array $conditions, int $limit = 0, array $order = []): array
    {
        return \array_map(
            fn(array $n) => $this->hydrator->hydrate($this->modelClass, $n),
            $this->tableGateway->queryOrCreateCollection($conditions, $limit, $order)
        );
    }

    public function deleteByName(string $name): void
    {
        $this->deleteAll(['name' => $name]);
    }
}
