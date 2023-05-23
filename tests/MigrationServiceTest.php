<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Test;

use DG\BypassFinals;
use PHPUnit\Framework\MockObject\MockObject;
use Whirlwind\MigrationCore\Config\Config;
use Whirlwind\MigrationCore\Config\MigrationPath;
use Whirlwind\MigrationCore\Config\MigrationPaths;
use Whirlwind\MigrationCore\Domain\Migration;
use Whirlwind\MigrationCore\Domain\MigrationRepositoryInterface;
use Whirlwind\MigrationCore\MigrationService;
use PHPUnit\Framework\TestCase;
use Whirlwind\MigrationCore\Test\data\TestMigration111111111111;
use Whirlwind\MigrationCore\Test\data\TestMigration123;
use Whirlwind\MigrationCore\Test\Util\CollectionMockable;

class MigrationServiceTest extends TestCase
{
    use CollectionMockable;

    private MockObject $config;
    private MockObject $repository;
    private MigrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();

        $this->config = $this->createMock(Config::class);
        $this->repository = $this->createMock(MigrationRepositoryInterface::class);

        $this->service = new MigrationService(
            $this->config,
            $this->repository
        );
    }

    public function testGetMigrationsForRollback()
    {
        $limit = 1;

        $this->repository->expects(self::once())
            ->method('findMigrations')
            ->willReturn([$migration =$this->createMock(Migration::class)]);

        $migration->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('TestMigration123');

        $migrationPath = $this->createMock(MigrationPath::class);
        $migrationPaths = $this->createCollectionIteratorMock(
            MigrationPaths::class,
            [$migrationPath]
        );
        $this->config->expects(self::once())
            ->method('getMigrationPaths')
            ->willReturn($migrationPaths);

        $migrationPath->expects(self::once())
            ->method('getPath')
            ->willReturn(__DIR__ . '/data');

        $migrationPath->expects(self::once())
            ->method('getNamespace')
            ->willReturn('Whirlwind\\MigrationCore\\Test\\data');

        $migration->expects(self::once())
            ->method('getCreatedAt')
            ->willReturn(123);

        $expected = [
            'TestMigration123' => [
                'name' => 'TestMigration123',
                'fullName' => TestMigration123::class,
                'path' => __DIR__ . '/data/TestMigration123.php',
                'createdAt' => 123,
            ],
        ];

        self::assertSame($expected, $this->service->getMigrationsForRollback($limit));
    }

    public function testAddMigration()
    {
        $name = 'TestMigration123';
        $createdAt = 123;
        $this->repository->expects(self::once())
            ->method('insert')
            ->with(self::isInstanceOf(Migration::class));

        $actual = $this->service->addMigration($name, $createdAt);
        self::assertSame($name, $actual->getName());
        self::assertSame($createdAt, $actual->getCreatedAt());
    }

    public function testDeleteMigration()
    {
        $name = 'TestMigration123';
        $this->repository->expects(self::once())
            ->method('deleteByName')
            ->with(self::identicalTo($name));

        $this->service->deleteMigration($name);
    }

    public function testGetMigrationHistory()
    {
        $limit = 1;

        $this->repository->expects(self::once())
            ->method('findMigrations')
            ->willReturn([$migration =$this->createMock(Migration::class)]);

        $migration->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('TestMigration123');

        $migrationPath = $this->createMock(MigrationPath::class);
        $migrationPaths = $this->createCollectionIteratorMock(
            MigrationPaths::class,
            [$migrationPath]
        );
        $this->config->expects(self::once())
            ->method('getMigrationPaths')
            ->willReturn($migrationPaths);

        $migrationPath->expects(self::once())
            ->method('getPath')
            ->willReturn(__DIR__ . '/data');

        $migrationPath->expects(self::once())
            ->method('getNamespace')
            ->willReturn('Whirlwind\\MigrationCore\\Test\\data');

        $migration->expects(self::once())
            ->method('getCreatedAt')
            ->willReturn(123);

        $expected = [
            'TestMigration123' => [
                'name' => 'TestMigration123',
                'fullName' => TestMigration123::class,
                'path' => __DIR__ . '/data/TestMigration123.php',
                'createdAt' => 123,
            ],
        ];

        self::assertSame($expected, $this->service->getMigrationHistory($limit));
    }

    public function testGetPendingMigrations()
    {
        $limit = 1;

        $this->repository->expects(self::once())
            ->method('findMigrations')
            ->willReturn([$migration =$this->createMock(Migration::class)]);

        $migration->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('TestMigration123');

        $migrationPath = $this->createMock(MigrationPath::class);
        $migrationPaths = $this->createCollectionIteratorMock(
            MigrationPaths::class,
            [$migrationPath]
        );
        $this->config->expects(self::atLeastOnce())
            ->method('getMigrationPaths')
            ->willReturn($migrationPaths);

        $migrationPath->expects(self::atLeastOnce())
            ->method('getPath')
            ->willReturn(__DIR__ . '/data');

        $migrationPath->expects(self::atLeastOnce())
            ->method('getNamespace')
            ->willReturn('Whirlwind\\MigrationCore\\Test\\data');

        $migration->expects(self::once())
            ->method('getCreatedAt')
            ->willReturn(123);

        $expected = [
            [
                'name' => 'TestMigration111111111111',
                'fullName' => TestMigration111111111111::class,
                'path' => __DIR__ . '/data/TestMigration111111111111.php',
                'createdAt' => 111111111111,
            ],
        ];

        $actual = $this->service->getPendingMigrations();
        self::assertSame($expected, $actual);
    }
}
