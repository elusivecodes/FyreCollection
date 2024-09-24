<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait FindTestTrait
{
    public function testFind(): void
    {
        $this->assertSame(
            5,
            Collection::range(1, 10)
                ->find(fn(int $value, int $key): bool => $value > 4)
        );
    }

    public function testFindInvalid(): void
    {
        $this->assertNull(
            Collection::range(1, 10)
                ->find(fn(int $value, int $key): bool => $value > 10)
        );
    }
}
