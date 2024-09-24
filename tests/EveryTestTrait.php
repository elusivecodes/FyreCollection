<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait EveryTestTrait
{
    public function testEvery(): void
    {
        $this->assertTrue(
            Collection::range(1, 10)
                ->every(fn(int $value, int $key): bool => $value <= 10)
        );
    }

    public function testEveryEmpty(): void
    {
        $this->assertTrue(
            Collection::empty()
                ->every(fn(): bool => false)
        );
    }

    public function testEveryFalse(): void
    {
        $this->assertFalse(
            Collection::range(1, 10)
                ->every(fn(int $value, int $key): bool => $value < 5)
        );
    }
}
