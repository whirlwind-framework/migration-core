<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Test\Command\Migration;

use DG\BypassFinals;
use PHPUnit\Framework\MockObject\MockObject;
use Whirlwind\MigrationCore\Command\Migration\StatusCommand;
use PHPUnit\Framework\TestCase;
use Whirlwind\MigrationCore\MigrationService;
use Whirlwind\MigrationCore\Test\Command\Migration\data\TestMigration210416005625;
use Whirlwind\MigrationCore\Test\Command\Migration\data\TestMigration210417005625;

class StatusCommandTest extends TestCase
{
    private MockObject $service;
    private $stdout;
    private bool $needRestore = false;
    private StatusCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();

        if ($this->needRestore = \in_array('test', \stream_get_wrappers())) {
            \stream_wrapper_unregister('test');
        }
        \stream_wrapper_register('test', DummyStream::class);

        $this->service = $this->createMock(MigrationService::class);
        $this->stdout = \fopen('test://stdout', 'w');

        $this->command = new StatusCommand(
            $this->service,
            null,
            $this->stdout,
            null
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \fclose($this->stdout);

        \stream_wrapper_unregister('test');
        if ($this->needRestore) {
            \stream_wrapper_restore('test');
        }
    }

    public function testRun()
    {
        $history = [
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
            ->method('getMigrationHistory')
            ->with(self::identicalTo(10))
            ->willReturn($history);

        $actual = $this->command->run();
        self::assertEquals(0, $actual);
    }

    public function testStatusForLastMigration()
    {
        $history = [
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
            ->method('getMigrationHistory')
            ->with(self::identicalTo(1))
            ->willReturn($history);

        $actual = $this->command->run([1]);
        self::assertEquals(0, $actual);

        $expected = [
            '[0m[34mShowing the last 1 applied migration:[0m',
            '[0m	(8637-10-26 10:27:05) TestMigration210417005625[0m'
        ];

        $actual = [];
        while (!feof($this->stdout)) {
            $actual[] = trim(fgets($this->stdout, 4096));
        }
        self::assertEquals($expected, $actual);
    }
}
