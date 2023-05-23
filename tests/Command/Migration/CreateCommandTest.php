<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Test\Command\Migration;

use DG\BypassFinals;
use Whirlwind\MigrationCore\Command\Migration\CreateCommand;
use Whirlwind\MigrationCore\Config\Config;
use Whirlwind\MigrationCore\Config\MigrationPath;
use Whirlwind\MigrationCore\Config\MigrationPaths;
use PHPUnit\Framework\TestCase;

class CreateCommandTest extends TestCase
{
    private $stdin;
    private $stdout;
    private $stderr;
    private bool $needRestore = false;
    private static string $migrationFile = 'create_tests_table';
    private static string $migrationPath = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    private CreateCommand $command;

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

        $this->command = new CreateCommand(
            new Config(
                new MigrationPaths([
                    new MigrationPath(
                        self::$migrationPath,
                        'Whirlwind\MigrationCore\Test\Command\Migration\data'
                    )
                ]),
                'migrations',
                __DIR__ . '/../../../src/template/migration.php',
            ),
            $this->stdin,
            $this->stdout,
            $this->stderr
        );
    }

    /**
     * @param array $params
     * @param int $expected
     * @return void
     * @throws \Throwable
     * @dataProvider paramsDataProvider
     */
    public function testRun(array $params, int $expected)
    {
        \fwrite($this->stdin, 'y');
        $actual = $this->command->run($params);
        self::assertEquals($expected, $actual);
    }

    public static function paramsDataProvider(): array
    {
        return [
            [
                'params' => [
                    self::$migrationFile,
                ],
                'expected' => 0
            ],
            [
                'params' => [
                    self::$migrationFile,
                    'path' => self::$migrationPath,
                ],
                'expected' => 0
            ],
            [
                'params' => [],
                'expected' => 1,
            ],
            [
                'params' => [
                    self::$migrationFile,
                    'path' => 'unknown',
                ],
                'expected' => 1,
            ],
        ];
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        \fclose($this->stdin);
        \fclose($this->stdout);
        \fclose($this->stderr);

        $name = \camelize(self::$migrationFile);
        foreach (\scandir(self::$migrationPath) as $fileName) {
            if (\str_starts_with($fileName, $name)) {
                \unlink(self::$migrationPath . DIRECTORY_SEPARATOR . $fileName);
            }
        }

        \stream_wrapper_unregister('test');
        if ($this->needRestore) {
            \stream_wrapper_restore('test');
        }
    }
}
