<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;

trait KeysTestTrait
{
    public function testKeys(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(
            ['a', 'b', 'c'],
            $collection->keys()->toArray()
        );
    }
}
