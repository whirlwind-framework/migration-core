<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Test;

use DG\BypassFinals;
use Whirlwind\Infrastructure\Persistence\ConnectionInterface;
use Whirlwind\MigrationCore\Blueprint;
use PHPUnit\Framework\TestCase;

class BlueprintTest extends TestCase
{
    private string $collection = 'migrations';
    private Blueprint $blueprint;

    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();

        $this->blueprint = new class($this->collection) extends Blueprint {
            public function create(callable $callback): void
            {
            }

            public function drop(): void
            {
            }

            public function dropIfExists(): void
            {
            }

            public function createIfNotExists(callable $callback): void
            {
            }

            public function getCommands(): array
            {
                return $this->commands;
            }
        };
    }


    public function testAddCommand()
    {
        $command = $this->createMock(Blueprint\Command::class);
        $this->blueprint->addCommand($command);
        $expected = [$command];
        self::assertSame($expected, $this->blueprint->getCommands());
    }

    public function testPrependCommand()
    {
        $command = $this->createMock(Blueprint\Command::class);
        $this->blueprint->addCommand($command);

        $expected = $this->createMock(Blueprint\Command::class);
        $this->blueprint->prependCommand($expected);
        self::assertSame($expected, $this->blueprint->getCommands()[0]);
    }

    public function testBuild()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $command = $this->createMock(Blueprint\Command::class);
        $this->blueprint->addCommand($command);

        $command->expects(self::once())
            ->method('apply')
            ->with(self::identicalTo($connection));

        $this->blueprint->build($connection);
    }
}
