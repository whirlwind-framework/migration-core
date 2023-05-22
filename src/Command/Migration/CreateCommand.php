<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Command\Migration;

use Whirlwind\MigrationCore\Config\Config;
use Whirlwind\App\Console\Command;

class CreateCommand extends Command
{
    public function __construct(
        protected Config $config,
        $stdin = null,
        $stdout = null,
        $stderr = null
    ) {
        parent::__construct($stdin, $stdout, $stderr);
    }

    /**
     * @param array $params
     * @return int
     * @throws \Throwable
     */
    public function run(array $params = []): int
    {
        if (!isset($params[0]) || !\preg_match('/^[\w\\\\]+$/', $params[0])) {
            $this->error(
                'The migration name should contain letters, digits, underscore and/or backslash '
                . 'characters only.'
            );
            return 1;
        }
        $className = \sprintf('%s%s', \camelize($params[0]), \gmdate('YmdHis'));

        if (isset($params['path'])) {
            $path = $this->config->getMigrationPaths()->findByPath($params['path']);
            if (!$path) {
                $this->error(\sprintf('Path %s is not registered in migrationPaths config.', $params['path']));

                return 1;
            }
        } else {
            $path = $this->config->getMigrationPaths()->first();
        }
        $file = \sprintf(
            '%s%s%s.php',
            \rtrim($path->getPath(), DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            $className
        );
        if ($this->confirm("Create new migration '$file'?")) {
            $namespace = $path->getNamespace();
            $content = $this->generateMigrationContent(\compact('className', 'namespace'));
            if (\file_put_contents($file, $content, LOCK_EX) === false) {
                $this->error('Failed to create new migration.');

                return 1;
            }

            $this->success('New migration created successfully.');
        }

        return 0;
    }

    protected function generateMigrationContent(array $params): string
    {
        $level = \ob_get_level();
        \ob_start();
        \ob_implicit_flush();
        \extract($params);

        try {
            require $this->config->getTemplateFilePath();
            return \ob_get_clean();
        } catch (\Throwable $e) {
            while (\ob_get_level() > $level) {
                if (!@\ob_end_clean()) {
                    \ob_clean();
                }
            }
            throw $e;
        }
    }
}
