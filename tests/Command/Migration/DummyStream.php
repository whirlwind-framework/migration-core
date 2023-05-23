<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Test\Command\Migration;

class DummyStream
{
    public $context;
    private string $stream;
    private array $stdout = [];
    private array $stdin = [];
    private array $stderr = [];
    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $this->stream = \parse_url($path)['host'];
        return true;
    }

    public function stream_read(): string|false
    {
        return \array_shift($this->{$this->stream}) ?? false;
    }

    public function stream_eof(): bool
    {
        return empty($this->{$this->stream});
    }
    public function stream_write(string $data): void
    {
        $this->{$this->stream}[] = $data;
    }
}
