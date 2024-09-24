<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait IsEmptyTestTrait
{
    public function testIsEmpty(): void
    {
        $this->assertTrue(
            Collection::empty()->isEmpty()
        );
    }

    public function testIsEmptyNotEmpty(): void
    {
        $this->assertFalse(
            Collection::range(1, 10)->isEmpty()
        );
    }
}
