<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait FirstTestTrait
{
    public function testFirst(): void
    {
        $this->assertSame(
            1,
            Collection::range(1, 10)->first()
        );
    }

    public function testFirstEmpty(): void
    {
        $this->assertNull(
            Collection::empty()->first()
        );
    }
}
