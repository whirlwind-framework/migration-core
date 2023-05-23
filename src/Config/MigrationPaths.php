<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Config;

use Whirlwind\Domain\Collection\Collection;

/**
 * @method MigrationPath|false current()
 */
class MigrationPaths extends Collection
{
    public function __construct(array $items = [])
    {
        parent::__construct(MigrationPath::class, $items);
    }

    public function findByPath(string $path): ?MigrationPath
    {
        foreach ($this as $item) {
            if ($item->getPath() === $path) {
                return $item;
            }
        }

        return null;
    }
}
