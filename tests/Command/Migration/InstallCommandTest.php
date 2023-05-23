<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Test\Command\Migration;

use DG\BypassFinals;
use Whirlwind\MigrationCore\Builder;
use Whirlwind\MigrationCore\Command\Migration\InstallCommand;
use Whirlwind\MigrationCore\Config\Config;
use Whirlwind\MigrationCore\Config\MigrationPath;
use Whirlwind\MigrationCore\Config\MigrationPaths;
use Whirlwind\MigrationCore\Domain\Migration;
use Whirlwind\MigrationCore\Domain\MigrationRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Whirlwind\MigrationCore\MigrationCreator;
use Whirlwind\MigrationCore\MigrationException;
use Whirlwind\MigrationCore\MigrationService;
use Whirlwind\MigrationCore\Test\Command\Migration\data\TestMigration210416005625;

class InstallCommandTest extends TestCase
{
    private $stdin;
    private $stdout;
    private $stderr;
    private bool $needRestore = false;
    private MockObject $service;
    private MockObject $creator;
    private InstallCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();

        if ($this->needRestore = \in_array('test', \stream_get_wrappers())) {
            \stream_wrapper_unregister('test');
        }
        \stream_wrapper_register('test', DummyStream::class);

        $this->stdin = \fopen('test://stdin', 'r');
        $this->stdout = \fopen('test://stdout', 'w');
        $this->stderr = \fopen('test://stderr', 'w');
        $this->service = $this->createMock(MigrationService::class);
        $this->creator = $this->createMock(MigrationCreator::class);

        $this->command = new InstallCommand(
            $this->service,
            $this->creator,
            $this->stdin,
            $this->stdout,
            $this->stderr
        );
    }

    public function testRun()
    {
        $limit = 1;
        $migrationMap = [
            'TestMigration210416005625' => [
                'name' => 'TestMigration210416005625',
                'fullName' => TestMigration210416005625::class,
                'path' => \sprintf(
                    '%s%2$sdata%2$s%2$sTestMigration210416005625.php',
                    __DIR__,
                    DIRECTORY_SEPARATOR
                ),
                'createdAt' => 210416005625
            ],
        ];
        $this->service->expects(self::once())
            ->method('getPendingMigrations')
            ->with(self::equalTo($limit))
            ->willReturn($migrationMap);
        \fwrite($this->stdin, 'y');

        $migration = $this->createMock(TestMigration210416005625::class);
        $this->creator->expects(self::once())
            ->method('create')
            ->with(self::identicalTo($migrationMap['TestMigration210416005625']['fullName']))
            ->willReturn($migration);

        $migration->expects(self::once())
            ->method('up');

        $entity = $this->createMock(Migration::class);
        $this->service->expects(self::once())
            ->method('addMigration')
            ->with(
                self::identicalTo($migrationMap['TestMigration210416005625']['name']),
                self::identicalTo($migrationMap['TestMigration210416005625']['createdAt'])
            )
            ->willReturn($entity);
        $actual = $this->command->run([$limit]);
        self::assertEquals(0, $actual);
    }

    public function testMigrationFailed()
    {
        $limit = 1;
        $migrationMap = [
            'TestMigration210416005625' => [
                'name' => 'TestMigration210416005625',
                'fullName' => TestMigration210416005625::class,
                'path' => \sprintf(
                    '%s%2$sdata%2$s%2$sTestMigration210416005625.php',
                    __DIR__,
                    DIRECTORY_SEPARATOR
                ),
                'createdAt' => 210416005625
            ],
        ];
        $this->service->expects(self::once())
            ->method('getPendingMigrations')
            ->with(self::equalTo($limit))
            ->willReturn($migrationMap);
        \fwrite($this->stdin, 'y');

        $migration = $this->createMock(TestMigration210416005625::class);
        $this->creator->expects(self::once())
            ->method('create')
            ->with(self::identicalTo($migrationMap['TestMigration210416005625']['fullName']))
            ->willReturn($migration);

        $migration->expects(self::once())
            ->method('up')
            ->willThrowException($this->createMock(MigrationException::class));

        $actual = $this->command->run([$limit]);
        self::assertEquals(1, $actual);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \fclose($this->stdin);
        \fclose($this->stdout);
        \fclose($this->stderr);

        \stream_wrapper_unregister('test');
        if ($this->needRestore) {
            \stream_wrapper_restore('test');
        }
    }
}
