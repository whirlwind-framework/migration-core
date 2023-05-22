<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Command\Migration;

use Whirlwind\MigrationCore\Migration;
use Whirlwind\MigrationCore\MigrationCreator;
use Whirlwind\MigrationCore\MigrationException;
use Whirlwind\MigrationCore\MigrationService;
use Psr\Container\ContainerInterface;
use Whirlwind\App\Console\Command;

class RollbackCommand extends Command
{
    public function __construct(
        protected MigrationService $service,
        protected MigrationCreator $creator,
        $stdin = null,
        $stdout = null,
        $stderr = null
    ) {
        parent::__construct($stdin, $stdout, $stderr);
    }

    public function run(array $params = []): int
    {
        if (isset($params['all']) && $params['all']) {
            $limit = 0;
        } else {
            $limit = (int) ($params[0] ?? 1);
        }

        if ($limit < 0) {
            throw new \Exception('The limit must be greater than or equal 0.');
        }

        $migrations = $this->service->getMigrationsForRollback($limit);
        if (empty($migrations)) {
            $this->info('No migration has been done before.');

            return 0;
        }

        $n = \count($migrations);
        $this->info("Total $n " . ($n === 1 ? 'migration' : 'migrations') . ' to be reverted:');

        foreach ($migrations as $migration) {
            $this->output('\t' . $migration['name']);
        }

        $reverted = 0;
        if ($this->confirm('Revert the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateRollback($migration)) {
                    $this->error(
                        "$reverted from $n " . ($reverted === 1 ? 'migration was' : 'migrations were')
                        . ' reverted.'
                    );
                    $this->error('Migration failed. The rest of the migrations are canceled.');

                    return 1;
                }
                $reverted++;
            }
            $this->success("$n " . ($n === 1 ? 'migration was' : 'migrations were') . ' reverted.');
            $this->success('Migrated down successfully.');
        }

        return 0;
    }

    protected function migrateRollback(array $data): bool
    {
        $this->info("*** reverting {$data['name']}");
        $start = \microtime(true);
        $migration = $this->creator->create($data['fullName']);
        try {
            $migration->down();
            $this->service->deleteMigration($data['name']);
            $time = \microtime(true) - $start;
            $this->success("*** reverted {$data['name']} (time: " . \sprintf('%.3f', $time) . "s)");

            return true;
        } catch (MigrationException $e) {
            $time = \microtime(true) - $start;
            $this->error("*** failed to revert {$data['name']} (time: " . \sprintf('%.3f', $time) . "s)");
        }

        return false;
    }
}
