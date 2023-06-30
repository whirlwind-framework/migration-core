<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Command\Migration;

use Whirlwind\MigrationCore\Migration;
use Whirlwind\MigrationCore\MigrationCreator;
use Whirlwind\MigrationCore\MigrationException;
use Whirlwind\MigrationCore\MigrationService;
use Psr\Container\ContainerInterface;
use Whirlwind\App\Console\Command;

class InstallCommand extends Command
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
        $limit = (int) ($params[0] ?? 0);
        $migrations = $this->service->getPendingMigrations($limit);

        if (empty($migrations)) {
            $this->info('No new migrations found. Your system is up-to-date.');

            return 0;
        }

        if ($limit < 0) {
            throw new \Exception('The limit must be greater than or equal 0.');
        }
        $n = \count($migrations);
        if ($limit === 0) {
            $this->info("Total $n new " . ($n === 1 ? 'migration' : 'migrations') . " to be applied:");
        } else {
            $this->info(
                "$n new " . ($n === 1 ? 'migration' : 'migrations')
                . " to be applied:"
            );
        }

        foreach ($migrations as $migration) {
            $this->output("\t" . $migration['name']);
        }
        $this->output('');

        if ($this->confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            $applied = 0;
            foreach ($migrations as $migration) {
                if (!$this->applyMigration($migration)) {
                    $this->error(
                        "$applied from $n " . ($applied === 1 ? 'migration was' : 'migrations were')
                        . ' applied.'
                    );
                    $this->error('Migration failed. The rest of the migrations are canceled.');

                    return 1;
                }
                $applied++;
            }
            $this->success("$n " . ($n === 1 ? 'migration was' : 'migrations were') . " applied.");
            $this->success('Migrated up successfully.');

        }

        return 0;
    }

    protected function applyMigration(array $data): bool
    {
        $this->info("*** applying {$data['name']}");
        $start = \microtime(true);
        $migration = $this->creator->create($data['fullName']);

        try {
            $migration->up();
            $this->service->addMigration($data['name'], (int) $data['createdAt']);
            $time = \microtime(true) - $start;
            $this->success("*** applied {$data['name']} (time: " . \sprintf('%.3f', $time) . "s)");

            return true;
        } catch (MigrationException $e) {
            $time = \microtime(true) - $start;
            $this->error("*** failed to apply {$data['name']} (time: " . \sprintf('%.3f', $time) . "s)");
        }

        return false;
    }
}
