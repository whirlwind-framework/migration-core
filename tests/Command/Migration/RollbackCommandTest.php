<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Test\Command\Migration;

use DG\BypassFinals;
use PHPUnit\Framework\MockObject\MockObject;
use Whirlwind\MigrationCore\Command\Migration\RollbackCommand;
use PHPUnit\Framework\TestCase;
use Whirlwind\MigrationCore\MigrationCreator;
use Whirlwind\MigrationCore\MigrationService;
use Whirlwind\MigrationCore\Test\Command\Migration\data\TestMigration210416005625;
use Whirlwind\MigrationCore\Test\Command\Migration\data\TestMigration210417005625;

class RollbackCommandTest extends TestCase
{
    private $stdin;
    private $stdout;
    private $stderr;
    private bool $needRestore = false;
    private MockObject $service;
    private MockObject $creator;
    private RollbackCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();

        if ($this->needRestore = \in_array('test', \stream_get_wrappers())) {
            \stream_wrapper_unregister('test');
        }
        \stream_wrapper_register('test', DummyStream::class);

        $this->service = $this->createMock(MigrationService::class);
        $this->creator = $this->createMock(MigrationCreator::class);
        $this->stdin = \fopen('test://stdin', 'r');
        $this->stdout = \fopen('test://stdout', 'w');
        $this->stderr = \fopen('test://stderr', 'w');

        $this->command = new RollbackCommand(
            $this->service,
            $this->creator,
            $this->stdin,
            $this->stdout,
            $this->stderr
        );
    }

    public function testRun()
    {
        $migrationMap = [
            'TestMigration210417005625' => [
                'name' => 'TestMigration210417005625',
                'fullName' => TestMigration210417005625::class,
                'path' => \sprintf(
                    '%s%2$sdata%2$s%2$sTestMigration210417005625.php',
                    __DIR__,
                    DIRECTORY_SEPARATOR
                ),
                'createdAt' => 210416005625
            ],
        ];

        $this->service->expects(self::once())
            ->method('getMigrationsForRollback')
            ->with(self::identicalTo(1))
            ->willReturn($migrationMap);

        \fwrite($this->stdin, 'y');

        $migration = $this->createMock(TestMigration210417005625::class);
        $this->creator->expects(self::once())
            ->method('create')
            ->with(self::identicalTo($migrationMap['TestMigration210417005625']['fullName']))
            ->willReturn($migration);

        $migration->expects(self::once())
            ->method('down');

        $this->service->expects(self::once())
            ->method('deleteMigration');

        $actual = $this->command->run();
        self::assertEquals(0, $actual);
    }

    public function testRollbackAll()
    {
        $params = ['all' => true];

        $migrationMap = [
            'TestMigration210417005625' => [
                'name' => 'TestMigration210417005625',
                'fullName' => TestMigration210417005625::class,
                'path' => \sprintf(
                    '%s%2$sdata%2$s%2$sTestMigration210417005625.php',
                    __DIR__,
                    DIRECTORY_SEPARATOR
                ),
                'createdAt' => 210416005625
            ],
            'TestMigration210416005625' => [
                'name' => 'TestMigration210416005625',
                'fullName' => TestMigration210416005625::class,
                'path' => \sprintf(
                    '%s%2$sdata%2$s%2$sTestMigration210417005625.php',
                    __DIR__,
                    DIRECTORY_SEPARATOR
                ),
                'createdAt' => 210416005324
            ],
        ];

        $this->service->expects(self::once())
            ->method('getMigrationsForRollback')
            ->with(self::identicalTo(0))
            ->willReturn($migrationMap);

        \fwrite($this->stdin, 'y');

        $migration1 = $this->createMock(TestMigration210417005625::class);
        $migration2 = $this->createMock(TestMigration210416005625::class);
        $this->creator->expects(self::exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($migration1, $migration2);

        $migration1->expects(self::once())
            ->method('down');

        $migration2->expects(self::once())
            ->method('down');

        $this->service->expects(self::exactly(2))
            ->method('deleteMigration');

        $actual = $this->command->run($params);
        self::assertEquals(0, $actual);
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
