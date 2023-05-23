<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Domain;

class Migration
{
    public function __construct(
        private string $name,
        private int $createdAt
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }
}
