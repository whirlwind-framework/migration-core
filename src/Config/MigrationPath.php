<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Config;

class MigrationPath
{
    public function __construct(
        protected string $path,
        protected string $namespace
    ) {
        $this->ensurePathExist();
    }

    private function ensurePathExist(): void
    {
        if (!\is_dir($this->path)) {
            throw new \InvalidArgumentException(
                \sprintf('Migration path `%s` is not exist.', $this->path)
            );
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
