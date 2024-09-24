<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait TakeUntilTestTrait
{
    public function testTakeUntil(): void
    {
        $this->assertSame(
            [1, 2, 3],
            Collection::range(1, 10)
                ->takeUntil(fn(int $value, int $key): bool => $value > 3)
                ->toArray()
        );
    }
}
