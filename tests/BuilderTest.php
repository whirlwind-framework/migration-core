<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Test;

use DG\BypassFinals;
use PHPUnit\Framework\MockObject\MockObject;
use Whirlwind\Infrastructure\Persistence\ConnectionInterface;
use Whirlwind\MigrationCore\Blueprint;
use Whirlwind\MigrationCore\BlueprintFactoryInterface;
use Whirlwind\MigrationCore\Builder;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    private MockObject $connection;
    private MockObject $blueprintFactory;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();

        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->blueprintFactory = $this->createMock(BlueprintFactoryInterface::class);
        $this->builder = new Builder(
            $this->connection,
            $this->blueprintFactory
        );
    }

    public function testCreate()
    {
        $collection = 'orders';

        $blueprint = $this->createMock(Blueprint::class);
        $this->blueprintFactory->expects(self::once())
            ->method('create')
            ->with(self::identicalTo($collection))
            ->willReturn($blueprint);

        $callback = static function () {};
        $blueprint->expects(self::once())
            ->method('create')
            ->with(self::identicalTo($callback));

        $this->builder->create($collection, $callback);
    }

    public function testDrop()
    {
        $collection = 'orders';

        $blueprint = $this->createMock(Blueprint::class);
        $this->blueprintFactory->expects(self::once())
            ->method('create')
            ->with(self::identicalTo($collection))
            ->willReturn($blueprint);

        $blueprint->expects(self::once())
            ->method('drop');

        $this->builder->drop($collection);
    }

    public function testDropIfExists()
    {
        $collection = 'orders';

        $blueprint = $this->createMock(Blueprint::class);
        $this->blueprintFactory->expects(self::once())
            ->method('create')
            ->with(self::identicalTo($collection))
            ->willReturn($blueprint);

        $blueprint->expects(self::once())
            ->method('dropIfExists');

        $this->builder->dropIfExists($collection);
    }

    public function testModify()
    {
        $collection = 'orders';

        $blueprint = $this->createMock(Blueprint::class);
        $this->blueprintFactory->expects(self::once())
            ->method('create')
            ->with(self::identicalTo($collection))
            ->willReturn($blueprint);

        $callback = static function () {};

        $this->builder->modify($collection, $callback);
    }

    public function testCreateIfNotExists()
    {
        $collection = 'orders';

        $blueprint = $this->createMock(Blueprint::class);
        $this->blueprintFactory->expects(self::once())
            ->method('create')
            ->with(self::identicalTo($collection))
            ->willReturn($blueprint);

        $callback = static function () {};
        $blueprint->expects(self::once())
            ->method('createIfNotExists')
            ->with(self::identicalTo($callback));

        $this->builder->createIfNotExists($collection, $callback);
    }
}
