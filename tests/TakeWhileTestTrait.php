<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait TakeWhileTestTrait
{
    public function testTakeWhile(): void
    {
        $this->assertSame(
            [1, 2, 3],
            Collection::range(1, 10)
                ->takeWhile(fn(int $value, int $key): bool => $value <= 3)
                ->toArray()
        );
    }
}
