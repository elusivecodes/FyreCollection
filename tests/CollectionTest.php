<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Collection\Collection;
use Generator;
use PHPUnit\Framework\TestCase;

use function count;
use function json_encode;

use const JSON_PRETTY_PRINT;

final class CollectionTest extends TestCase
{
    use AvgTestTrait;
    use CacheTestTrait;
    use ChunkTestTrait;
    use CollectTestTrait;
    use CombineTestTrait;
    use CountByTestTrait;
    use CountTestTrait;
    use DotTestTrait;
    use EachTestTrait;
    use EveryTestTrait;
    use ExceptTestTrait;
    use ExtractTestTrait;
    use FilterTestTrait;
    use FindLastTestTrait;
    use FindTestTrait;
    use FirstTestTrait;
    use FlattenTestTrait;
    use FlipTestTrait;
    use GroupByTestTrait;
    use IncludesTestTrait;
    use IndexByTestTrait;
    use IndexOfTestTrait;
    use IsEmptyTestTrait;
    use JoinTestTrait;
    use JsonSerializeTestTrait;
    use KeysTestTrait;
    use LastIndexOfTestTrait;
    use LastTestTrait;
    use ListNestedTestTrait;
    use MapTestTrait;
    use MaxTestTrait;
    use MedianTestTrait;
    use MergeTestTrait;
    use MinTestTrait;
    use NestTestTrait;
    use NoneTestTrait;
    use OnlyTestTrait;
    use PrintNestedTestTrait;
    use RandomValueTestTrait;
    use ReduceTestTrait;
    use RejectTestTrait;
    use ReverseTestTrait;
    use ShuffleTestTrait;
    use SkipTestTrait;
    use SkipUntilTestTrait;
    use SkipWhileTestTrait;
    use SomeTestTrait;
    use SortByTestTrait;
    use SortTestTrait;
    use SumOfTestTrait;
    use TakeTestTrait;
    use TakeUntilTestTrait;
    use TakeWhileTestTrait;
    use UniqueTestTrait;
    use ValuesTestTrait;
    use ZipTestTrait;

    public function testCountable(): void
    {
        $collection = new Collection([1, 2, 3, 4]);

        $this->assertSame(
            4,
            count($collection)
        );
    }

    public function testIterable(): void
    {
        $collection = new Collection(['a' => 1, 'b' => ['c' => 2]]);

        $test = [];
        foreach ($collection as $key => $value) {
            $test[$key] = $value;
        }

        $this->assertSame(
            ['a' => 1, 'b' => ['c' => 2]],
            $test
        );
    }

    public function testIterableGenerator(): void
    {
        $collection = new Collection(function(): Generator {
            yield 'a' => 1;
            yield 'b' => ['c' => 2];
        });

        $test = [];
        foreach ($collection as $key => $value) {
            $test[$key] = $value;
        }

        $this->assertSame(
            ['a' => 1, 'b' => ['c' => 2]],
            $test
        );
    }

    public function testJsonSerializable(): void
    {
        $collection = new Collection(['a' => 1, 'b' => ['c' => 2]]);

        $this->assertSame(
            '{"a":1,"b":{"c":2}}',
            json_encode($collection)
        );
    }

    public function testJsonSerializableDeep(): void
    {
        $collection = new Collection(['a' => 1, 'b' => new Collection(['c' => 2])]);

        $this->assertSame(
            '{"a":1,"b":{"c":2}}',
            json_encode($collection)
        );
    }

    public function testToArrayArray(): void
    {
        $collection = new Collection(['a' => 1, 'b' => ['c' => 2]]);

        $this->assertSame(
            ['a' => 1, 'b' => ['c' => 2]],
            $collection->toArray()
        );
    }

    public function testToArrayGenerator(): void
    {
        $collection = new Collection(function(): Generator {
            yield 'a' => 1;
            yield 'b' => ['c' => 2];
        });

        $this->assertSame(
            ['a' => 1, 'b' => ['c' => 2]],
            $collection->toArray()
        );
    }

    public function testToJson(): void
    {
        $collection = new Collection(['a' => 1, 'b' => ['c' => 2]]);

        $this->assertSame(
            json_encode($collection->toArray(), JSON_PRETTY_PRINT),
            $collection->toJson()
        );
    }

    public function testToJsonDeep(): void
    {
        $collection = new Collection(['a' => 1, 'b' => new Collection(['c' => 2])]);

        $this->assertSame(
            json_encode($collection->toArray(), JSON_PRETTY_PRINT),
            $collection->toJson()
        );
    }

    public function testToListArray(): void
    {
        $collection = new Collection(['a' => 1, 'b' => ['c' => 2]]);

        $this->assertSame(
            [1, ['c' => 2]],
            $collection->toList()
        );
    }

    public function testToListGenerator(): void
    {
        $collection = new Collection(function(): Generator {
            yield 'a' => 1;
            yield 'b' => ['c' => 2];
        });

        $this->assertSame(
            [1, ['c' => 2]],
            $collection->toList()
        );
    }

    public function testToString(): void
    {
        $collection = new Collection(['a' => 1, 'b' => ['c' => 2]]);

        $this->assertSame(
            json_encode($collection->toArray(), JSON_PRETTY_PRINT),
            $collection->__toString()
        );
    }

    public function testToStringDeep(): void
    {
        $collection = new Collection(['a' => 1, 'b' => new Collection(['c' => 2])]);

        $this->assertSame(
            json_encode($collection->toArray(), JSON_PRETTY_PRINT),
            $collection->__toString()
        );
    }
}
