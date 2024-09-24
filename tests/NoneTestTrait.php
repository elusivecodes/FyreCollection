<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait NoneTestTrait
{
    public function testNone(): void
    {
        $this->assertTrue(
            Collection::range(1, 10)
                ->none(fn(int $value, int $key): bool => $value >= 11)
        );
    }

    public function testNoneEmpty(): void
    {
        $this->assertTrue(
            Collection::empty()
                ->none(fn(): bool => false)
        );
    }

    public function testNoneFalse(): void
    {
        $this->assertFalse(
            Collection::range(1, 10)
                ->none(fn(int $value, int $key): bool => $value < 5)
        );
    }
}
