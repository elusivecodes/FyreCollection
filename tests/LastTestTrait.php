<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait LastTestTrait
{
    public function testLast(): void
    {
        $this->assertSame(
            10,
            Collection::range(1, 10)->last()
        );
    }

    public function testLastEmpty(): void
    {
        $this->assertNull(
            Collection::empty()->last()
        );
    }
}
