<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Config;

class Config
{
    public function __construct(
        protected MigrationPaths $migrationPaths,
        protected string $templateFilePath = __DIR__ . '/../template/migration.php'
    ) {
        $this->ensureTemplateFileExist();
    }

    private function ensureTemplateFileExist(): void
    {
        if (!\is_file($this->templateFilePath)) {
            throw new \InvalidArgumentException(
                \sprintf('Template file `%s` is not exist.', $this->templateFilePath)
            );
        }
    }

    /**
     * @return MigrationPaths
     */
    public function getMigrationPaths(): MigrationPaths
    {
        return $this->migrationPaths;
    }

    /**
     * @return string
     */
    public function getTemplateFilePath(): string
    {
        return $this->templateFilePath;
    }
}
