<?php

declare(strict_types=1);

namespace Whirlwind\MigrationCore\Test\Domain;

use DG\BypassFinals;
use Whirlwind\MigrationCore\Domain\Migration;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{
    private string $name = 'test';
    private int $createdAt = 123;
    private Migration $entity;

    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();

        $this->entity = new Migration(
            $this->name,
            $this->createdAt
        );
    }

    public function testGetName()
    {
        self::assertSame($this->name, $this->entity->getName());
    }

    public function testGetCreatedAt()
    {
        self::assertSame($this->createdAt, $this->entity->getCreatedAt());
    }
}
