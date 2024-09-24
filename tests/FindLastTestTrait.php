<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait FindLastTestTrait
{
    public function testFindLast(): void
    {
        $this->assertSame(
            5,
            Collection::range(1, 10)
                ->findLast(fn(int $value, int $key): bool => $value < 6)
        );
    }

    public function testFindLastInvalid(): void
    {
        $this->assertNull(
            Collection::range(1, 10)
                ->findLast(fn(int $value, int $key): bool => $value < 0)
        );
    }
}
