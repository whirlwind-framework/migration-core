<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore;

use Whirlwind\MigrationCore\Config\Config;
use Whirlwind\MigrationCore\Domain\MigrationRepositoryInterface;

class MigrationService
{
    public function __construct(
        protected Config $config,
        protected MigrationRepositoryInterface $repository
    ) {
    }

    /**
     * @param int $limit
     * @return array
     * @throws MigrationException
     */
    public function getMigrationHistory(int $limit = 0): array
    {
        $histories = $this->repository->findMigrations(
            [],
            $limit,
            ['createdAt' => SORT_DESC, 'name' => SORT_DESC]
        );

        $historyMap = [];
        foreach ($histories as $history) {
            $historyMap[$history->getName()] = $this->resolveName($history->getName()) + [
                'createdAt' => $history->getCreatedAt(),
            ];
        }

        return $historyMap;
    }

    protected function resolveName(string $name): array
    {
        foreach ($this->config->getMigrationPaths() as $migrationPath) {
            $filePath = \sprintf(
                '%s%s%s.php',
                \rtrim($migrationPath->getPath(), DIRECTORY_SEPARATOR),
                DIRECTORY_SEPARATOR,
                $name
            );

            if (\file_exists($filePath)) {
                return [
                    'name' => $name,
                    'fullName' =>\sprintf(
                        '%s\\%s',
                        \rtrim($migrationPath->getNamespace(), '\\'),
                        $name
                    ),
                    'path' => $filePath,
                ];
            }
        }

        throw new MigrationException(\sprintf('No migration path found for migration `%s`', $name));
    }

    public function getPendingMigrations(int $limit = 0): array
    {
        $applied = $this->getMigrationHistory();

        $migrationMap = [];
        foreach ($this->config->getMigrationPaths() as $migrationPath) {
            $handle = \opendir($migrationPath->getPath());
            while (($file = \readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $path = \sprintf(
                    '%s%s%s',
                    \rtrim($migrationPath->getPath(), DIRECTORY_SEPARATOR),
                    DIRECTORY_SEPARATOR,
                    $file
                );

                if (\is_file($path) && \preg_match('/^(.*?(\d{14,})).php$/', $file, $matches)
                    && !\array_key_exists($matches[1], $applied)
                ) {
                    $migrationMap[$matches[1]] = [
                        'name' => $matches[1],
                        'fullName' => \sprintf('%s\\%s', \trim($migrationPath->getNamespace()), $matches[1]),
                        'path' => $path,
                        'createdAt' => (int)$matches[2],
                    ];
                }
            }
            \closedir($handle);
        }

        \usort($migrationMap, function (array $a, array $b): int {
            if ($a['createdAt'] == $b['createdAt']) {
                return 0;
            }

            return $a['createdAt'] < $b['createdAt'] ? -1 : 1;
        });

        if ($limit > 0) {
            $migrationMap = \array_slice(\array_values($migrationMap), 0, $limit);
        }

        return $migrationMap;
    }

    /**
     * @param string $name
     * @param int $createdAt
     * @return Domain\Migration
     */
    public function addMigration(string $name, int $createdAt): \Whirlwind\MigrationCore\Domain\Migration
    {
        $entity = new \Whirlwind\MigrationCore\Domain\Migration(
            $name,
            $createdAt
        );

        $this->repository->insert($entity);

        return $entity;
    }

    public function getMigrationsForRollback(int $limit = 0): array
    {
        return $this->getMigrationHistory($limit);
    }

    public function deleteMigration(string $name): void
    {
        $this->repository->deleteByName($name);
    }
}
