<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait RejectTestTrait
{
    public function testReject(): void
    {
        $this->assertSame(
            [
                0 => 1,
                2 => 3,
                4 => 5,
                6 => 7,
                8 => 9,
            ],
            Collection::range(1, 10)
                ->reject(fn(int $value, int $key): bool => $value % 2 === 0)
                ->toArray()
        );
    }
}
