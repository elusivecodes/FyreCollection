<?php
declare(strict_types=1);

namespace Fyre\Collection;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use Fyre\Utility\Traits\MacroTrait;
use Generator;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

use function array_key_exists;
use function array_pop;
use function array_reverse;
use function arsort;
use function asort;
use function count;
use function floor;
use function implode;
use function in_array;
use function is_array;
use function is_object;
use function iterator_count;
use function iterator_to_array;
use function json_encode;
use function method_exists;
use function property_exists;
use function shuffle;
use function str_repeat;
use function uasort;

use const JSON_PRETTY_PRINT;
use const SORT_LOCALE_STRING;
use const SORT_NATURAL;
use const SORT_NUMERIC;
use const SORT_REGULAR;
use const SORT_STRING;

/**
 * Collection
 */
class Collection implements Countable, IteratorAggregate, JsonSerializable
{
    use MacroTrait;

    public const SORT_LOCALE = SORT_LOCALE_STRING;

    public const SORT_NATURAL = SORT_NATURAL;

    public const SORT_NUMERIC = SORT_NUMERIC;

    public const SORT_REGULAR = SORT_REGULAR;

    public const SORT_STRING = SORT_STRING;

    protected array|Closure $source;

    /**
     * Create an empty collection.
     *
     * @return Collection The Collection.
     */
    public static function empty(): static
    {
        return new static([]);
    }

    /**
     * Create a collection for a range of numbers.
     *
     * @return Collection The Collection.
     */
    public static function range(int $from, int $to): static
    {
        return new static(function() use ($from, $to): Generator {
            if ($from <= $to) {
                while ($from <= $to) {
                    yield $from++;
                }
            } else {
                while ($from >= $to) {
                    yield $from++;
                }
            }
        });
    }

    /**
     * New Collection constructor.
     *
     * @param array|Closure|JsonSerializable|Traversable|null $source The source.
     */
    public function __construct(array|Closure|JsonSerializable|Traversable|null $source)
    {
        if ($source === null) {
            $this->source = [];
        } else if ($source instanceof Traversable) {
            $this->source = iterator_to_array($source);
        } else if ($source instanceof JsonSerializable) {
            $this->source = (array) $source->jsonSerialize();
        } else {
            $this->source = $source;
        }
    }

    /**
     * Convert the collection to a JSON encoded string.
     *
     * @return string The JSON encoded string.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Get the average value of a key.
     *
     * @param array|Closure|string|null $valuePath The key path of the value.
     * @return float|null The average value.
     */
    public function avg(array|Closure|string|null $valuePath = null): float|null
    {
        $valueCallback = static::valueExtractor($valuePath);

        [$sum, $count] = $this->reduce(function(array $result, mixed $item, int|string $key) use ($valueCallback): array {
            $value = $valueCallback($item, $key);

            if ($value !== null) {
                $result[0] += $value;
                $result[1]++;
            }

            return $result;
        }, [0, 0]);

        return $count ? ($sum / $count) : null;
    }

    /**
     * Cache the computed values via a new collection.
     *
     * @return Collection A new Collection.
     */
    public function cache()
    {
        $iterator = $this->getIterator();

        $iteratorIndex = 0;

        $cache = [];

        return new static(function() use ($iterator, &$iteratorIndex, &$cache): Generator {
            $index = 0;
            while (true) {
                if (array_key_exists($index, $cache)) {
                    [$key, $value] = $cache[$index];
                } else {
                    while ($iteratorIndex < $index) {
                        $iterator->next();
                        $iteratorIndex++;
                    }

                    if (!$iterator->valid()) {
                        break;
                    }

                    $key = $iterator->key();
                    $value = $iterator->current();

                    $cache[$index] = [$key, $value];
                }

                yield $key => $value;
                $index++;
            }
        });
    }

    /**
     * Split the collection into chunks.
     *
     * @param int $size The size of each chunk.
     * @param bool $preserveKeys Whether to preserve the array keys.
     * @return Collection A new Collection.
     */
    public function chunk(int $size, bool $preserveKeys = false): static
    {
        if ($size <= 0) {
            return static::empty();
        }

        return new static(function() use ($size, $preserveKeys): Generator {
            $results = [];

            foreach ($this as $key => $item) {
                if ($preserveKeys) {
                    $results[$key] = $item;
                } else {
                    $results[] = $item;
                }

                if (count($results) === $size) {
                    yield $results;
                    $results = [];
                }
            }

            if ($results !== []) {
                yield $results;
            }
        });
    }

    /**
     * Collect the computed values into a new collection.
     *
     * @return Collection A new Collection.
     */
    public function collect(): static
    {
        return new static($this->toArray());
    }

    /**
     * Re-index the items in the collection by a given key, using a given value.
     *
     * @param array|Closure|string $keyPath The key path.
     * @param array|Closure|string $valuePath The key path of the value.
     * @return Collection A new Collection.
     */
    public function combine(array|Closure|string $keyPath, array|Closure|string|null $valuePath = null): static
    {
        $keyCallback = static::valueExtractor($keyPath);
        $valueCallback = static::valueExtractor($valuePath);

        return new static(function() use ($keyCallback, $valueCallback): mixed {
            foreach ($this as $key => $item) {
                $value = $valueCallback($item, $key);
                $key = $keyCallback($item, $key);

                if (is_object($key)) {
                    $key = (string) $key;
                }

                yield $key => $value;
            }
        });
    }

    /**
     * Count all items in the collection.
     *
     * @return int The number of items in the collection.
     */
    public function count(): int
    {
        if (is_array($this->source)) {
            return count($this->source);
        }

        return iterator_count($this->getIterator());
    }

    /**
     * Groups the items in the collection by a given key, and count the number of items in each.
     *
     * @param array|Closure|string $keyPath The key path.
     * @return Collection A new Collection.
     */
    public function countBy(array|Closure|string $keyPath): static
    {
        $keyCallback = static::valueExtractor($keyPath);

        return new static(function() use ($keyCallback): Generator {
            $results = [];

            foreach ($this as $key => $item) {
                $key = $keyCallback($item, $key);

                $results[$key] ??= 0;
                $results[$key]++;
            }

            yield from $results;
        });
    }

    /**
     * Flatten a multi-dimensional collection using "dot" notation.
     *
     * @param int|string|null $prefix The key prefix.
     * @return Collection A new Collection.
     */
    public function dot(int|string|null $prefix = null): static
    {
        return new static(function() use ($prefix): Generator {
            foreach ($this as $key => $item) {
                if ($prefix !== null) {
                    $key = $prefix.'.'.$key;
                }

                if (!is_array($item) && !($item instanceof Traversable)) {
                    yield $key => $item;
                } else {
                    yield from (new static($item))->dot($key);
                }
            }
        });
    }

    /**
     * Execute a callback on each item in the collection.
     *
     * @param Closure $callback The callback.
     * @return Collection The Collection.
     */
    public function each(Closure $callback): static
    {
        foreach ($this as $key => $item) {
            $callback($item, $key);
        }

        return $this;
    }

    /**
     * Determine whether every item in the collection passes a callback.
     *
     * @param Closure $callback The callback.
     * @return bool TRUE if every item in the collection passes a callback, otherwise FALSE.
     */
    public function every(Closure $callback): bool
    {
        foreach ($this as $key => $item) {
            if (!$callback($item, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return a collection without the specified keys.
     *
     * @param array $keys The keys to exclude.
     * @return Collection A new Collection.
     */
    public function except(array $keys): static
    {
        return new static(function() use ($keys): Generator {
            foreach ($this as $key => $item) {
                if (in_array($key, $keys)) {
                    continue;
                }

                yield $key => $item;
            }
        });
    }

    /**
     * Extract values from the collection using "dot" notation.
     *
     * @param array|Closure|string $valuePath The key path of the value.
     * @return Collection A new Collection.
     */
    public function extract(array|Closure|string $valuePath): static
    {
        $valueCallback = static::valueExtractor($valuePath);

        return new static(function() use ($valueCallback): Generator {
            foreach ($this as $key => $item) {
                yield $valueCallback($item, $key);
            }
        });
    }

    /**
     * Filter items in the collection using a callback function.
     *
     * @param Closure $callback The callback.
     * @return Collection A new Collection.
     */
    public function filter(Closure $callback): static
    {
        return new static(function() use ($callback): Generator {
            foreach ($this as $key => $item) {
                if (!$callback($item, $key)) {
                    continue;
                }

                yield $key => $item;
            }
        });
    }

    /**
     * Find the first value in the collection that passes a callback.
     *
     * @param Closure $callback The callback.
     * @return mixed The first value in the collection that passes a callback.
     */
    public function find(Closure $callback): mixed
    {
        foreach ($this as $key => $item) {
            if ($callback($item, $key)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Find the last value in the collection that passes a callback.
     *
     * @param Closure $callback The callback.
     * @return mixed The last value in the collection that passes a callback.
     */
    public function findLast(Closure $callback): mixed
    {
        return $this->reverse()->find($callback);
    }

    /**
     * Get the first value in the collection.
     *
     * @return mixed The first value in the collection.
     */
    public function first(): mixed
    {
        foreach ($this as $item) {
            return $item;
        }

        return null;
    }

    /**
     * Flatten a multi-dimensional collection into a single level.
     *
     * @param int $maxDepth The maximum depth to flatten.
     * @return Collection A new Collection.
     */
    public function flatten(int $maxDepth = PHP_INT_MAX): static
    {
        return (new static(function() use ($maxDepth): Generator {
            foreach ($this as $item) {
                if (!is_array($item) && !($item instanceof Traversable)) {
                    yield $item;
                } else if ($maxDepth === 1) {
                    yield from $item;
                } else {
                    yield from (new static($item))->flatten($maxDepth - 1);
                }
            }
        }))->values();
    }

    /**
     * Swap the keys and values of a collection.
     *
     * @return Collection A new Collection.
     */
    public function flip(): static
    {
        return new static(function(): Generator {
            foreach ($this as $key => $item) {
                yield $item => $key;
            }
        });
    }

    /**
     * Get the collection Iterator.
     *
     * @return Iterator The collection Iterator.
     */
    public function getIterator(): Iterator
    {
        if (is_array($this->source)) {
            return new ArrayIterator($this->source);
        }

        $data = ($this->source)();

        if (is_array($data) || $data === null) {
            return new ArrayIterator($data ?? []);
        }

        if ($data instanceof Iterator) {
            return $data;
        }

        return new ArrayIterator([$data]);
    }

    /**
     * Group the items in the collection by a given key.
     *
     * @param array|Closure|string $keyPath The key path.
     * @return Collection A new Collection.
     */
    public function groupBy(array|Closure|string $keyPath): static
    {
        $keyCallback = static::valueExtractor($keyPath);

        return new static(function() use ($keyCallback): Generator {
            $results = [];

            foreach ($this as $key => $item) {
                $key = $keyCallback($item, $key);

                $results[$key] ??= [];
                $results[$key][] = $item;
            }

            yield from $results;
        });
    }

    /**
     * Determine whether a given value exists in the collection.
     *
     * @param mixed $value The value to check for.
     * @return bool Whether the given value exists in the collection.
     */
    public function includes(mixed $value): bool
    {
        return $this->some(fn(mixed $item): bool => $item === $value);
    }

    /**
     * Re-index the items in the collection by a given key.
     *
     * @param array|Closure|string $keyPath The key path.
     * @return Collection A new Collection.
     */
    public function indexBy(array|Closure|string $keyPath): static
    {
        $keyCallback = static::valueExtractor($keyPath);

        return new static(function() use ($keyCallback): mixed {
            foreach ($this as $key => $item) {
                $key = $keyCallback($item, $key);

                if (is_object($key)) {
                    $key = (string) $key;
                }

                yield $key => $item;
            }
        });
    }

    /**
     * Search the collection for a given value and return the first key.
     *
     * @param mixed $value The value to search for.
     * @return int|string|null The first key for the matching value, otherwise FALSE.
     */
    public function indexOf(mixed $value): int|string|null
    {
        foreach ($this as $key => $item) {
            if ($item === $value) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Determine whether the collection is empty.
     *
     * @return bool TRUE if the collection is empty, otherwise FALSE.
     */
    public function isEmpty(): bool
    {
        return !$this->getIterator()->valid();
    }

    /**
     * Join the items in the collection using a specified separator.
     *
     * @param string $glue The separator glue join with.
     * @param string|null $finalGlue The conjunction for the last value.
     * @return string The joined string.
     */
    public function join(string $glue, string|null $finalGlue = null): string
    {
        $values = $this->toList();

        if ($finalGlue === null) {
            return implode($glue, $values);
        }

        $count = count($values);

        if ($count === 0) {
            return '';
        }

        $finalValue = array_pop($values);

        if ($count === 1) {
            return $finalValue;
        }

        return implode($glue, $values).$finalGlue.$finalValue;
    }

    /**
     * Convert the collection to an array for JSON serializing.
     *
     * @return array The array for serializing.
     */
    public function jsonSerialize(): array
    {
        return array_map(function(mixed $item): mixed {
            if ($item instanceof JsonSerializable) {
                return $item->jsonSerialize();
            }

            if (is_object($item) && method_exists($item, 'toArray')) {
                return $item->toArray();
            }

            return $item;
        }, $this->toArray());
    }

    /**
     * Get the keys in the collection.
     *
     * @return Collection A new Collection.
     */
    public function keys(): static
    {
        return new static(function(): Generator {
            foreach ($this as $key => $item) {
                yield $key;
            }
        });
    }

    /**
     * Get the last value in the collection.
     *
     * @return mixed The last value in the collection.
     */
    public function last(): mixed
    {
        return $this->reverse()->first();
    }

    /**
     * Search the collection for a given value and return the last key.
     *
     * @param mixed $value The value to search for.
     * @return int|string|null The last key for the matching value, otherwise FALSE.
     */
    public function lastIndexOf(mixed $value): int|string|null
    {
        return $this->reverse()->indexOf($value);
    }

    /**
     * Flatten nested items into a list.
     *
     * @param string $order The method for traversing the tree.
     * @param string $nestingKey The key used for nesting children.
     * @return Collection A new Collection.
     */
    public function listNested(string $order = 'desc', string $nestingKey = 'children'): static
    {
        return new static(function() use ($order, $nestingKey): Generator {
            $getResults = function(array|Traversable $items, int $depth = 0) use ($order, $nestingKey, &$getResults): Generator {
                foreach ($items as $item) {
                    if ($order === 'desc' || ($order === 'leaves' && $depth > 0)) {
                        yield $item;
                    }

                    $children = $item[$nestingKey] ?? null;

                    if (is_array($children) || $children instanceof Traversable) {
                        $nestedItems = $getResults($children, $depth + 1);
                        foreach ($nestedItems as $nestedItem) {
                            yield $nestedItem;
                        }
                    }

                    if ($order === 'asc') {
                        yield $item;
                    }
                }
            };

            yield from $getResults($this);
        });
    }

    /**
     * Apply a callback to the items in the collection.
     *
     * @param Closure $callback The callback.
     * @return Collection A new collection.
     */
    public function map(Closure $callback): static
    {
        return new static(function() use ($callback): Generator {
            foreach ($this as $key => $item) {
                yield $key => $callback($item, $key);
            }
        });
    }

    /**
     * Get the maximum value of a key.
     *
     * @param array|Closure|string|null $valuePath The key path of the value.
     * @return float|null The maximum value.
     */
    public function max(array|Closure|string|null $valuePath = null): mixed
    {
        $valueCallback = static::valueExtractor($valuePath);

        return $this->reduce(
            function(mixed $acc, mixed $item, int|string $key) use ($valueCallback): mixed {
                $value = $valueCallback($item, $key);

                return $acc === null || $value > $acc ? $value : $acc;
            }
        );
    }

    /**
     * Get the median value of a key.
     *
     * @param array|Closure|string|null $valuePath The key path of the value.
     * @return float|null The median value.
     */
    public function median(array|Closure|string|null $valuePath = null): mixed
    {
        $values = $valuePath === null ?
            $this :
            $this->extract($valuePath);

        $values = $values->filter(fn(mixed $value): bool => $value !== null)
            ->sort()
            ->toList();

        $count = count($values);

        if ($count === 0) {
            return null;
        }

        $middle = floor($count / 2);

        if ($count % 2 !== 0) {
            return $values[$middle];
        }

        return ($values[$middle - 1] + $values[$middle]) / 2;
    }

    /**
     * Merge one or more iterables into the collection.
     *
     * @param array|Traversable ...$arrays The iterables to merge.
     * @return Collection A new Collection.
     */
    public function merge(array|Traversable ...$arrays): static
    {
        return new static(function() use ($arrays): Generator {
            foreach ($this as $item) {
                yield $item;
            }

            foreach ($arrays as $iterable) {
                foreach ($iterable as $item) {
                    yield $item;
                }
            }
        });
    }

    /**
     * Get the minimum value of a key.
     *
     * @param array|Closure|string|null $valuePath The key path of the value.
     * @return float|null The minimum value.
     */
    public function min(array|Closure|string|null $valuePath = null): mixed
    {
        $valueCallback = static::valueExtractor($valuePath);

        return $this->reduce(
            function(mixed $acc, mixed $item, int|string $key) use ($valueCallback): mixed {
                $value = $valueCallback($item, $key);

                return $acc === null || $value < $acc ? $value : $acc;
            }
        );
    }

    /**
     * Nest child items inside parent items.
     *
     * @param array|Closure|string $idPath The key path of the ID.
     * @param array|Closure|string $parentPath The key path of the parent ID.
     * @param string $nestingKey The key used for nesting children.
     * @return Collection A new Collection.
     */
    public function nest(array|Closure|string $idPath = 'id', array|Closure|string $parentPath = 'parent_id', string $nestingKey = 'children'): static
    {
        $idCallback = static::valueExtractor($idPath);
        $parentCallback = static::valueExtractor($parentPath);

        return new static(function() use ($idCallback, $parentCallback, $nestingKey): Generator {
            $items = $this->toArray();
            $parents = [];

            foreach ($items as $key => &$item) {
                $id = $idCallback($item, $key);

                $item[$nestingKey] = [];
                $parents[$id] = &$item;
            }

            $results = [];
            foreach ($items as $key => &$item) {
                $parentId = $parentCallback($item, $key);

                if ($parentId && array_key_exists($parentId, $parents)) {
                    $parents[$parentId][$nestingKey][] = &$item;
                } else {
                    $results[] = &$item;
                }
            }

            yield from $results;
        });
    }

    /**
     * Determine whether no items in the collection pass a callback.
     *
     * @param Closure $callback The callback.
     * @return bool TRUE if no items in the collection pass a callback, otherwise FALSE.
     */
    public function none(Closure $callback): bool
    {
        return $this->every(static::negate($callback));
    }

    /**
     * Return a Collection with only the specified keys.
     *
     * @param array $keys The keys to include.
     * @return Collection A new Collection.
     */
    public function only(array $keys): static
    {
        return new static(function() use ($keys): Generator {
            foreach ($this as $key => $item) {
                if (!in_array($key, $keys)) {
                    continue;
                }

                yield $key => $item;
            }
        });
    }

    /**
     * Format nested list items based on depth.
     *
     * @param array|Closure|string $valuePath The key path of the name.
     * @param array|Closure|string $keyPath The key path.
     * @param string $prefix The prefix used to indicate depth.
     * @param string $nestingKey The key used for nesting children.
     * @return Collection A new Collection.
     */
    public function printNested(array|Closure|string $valuePath, array|Closure|string $keyPath = 'id', string $prefix = '--', string $nestingKey = 'children'): static
    {
        $valueCallback = static::valueExtractor($valuePath);
        $keyCallback = static::valueExtractor($keyPath);

        return new static(function() use ($valueCallback, $keyCallback, $prefix, $nestingKey): Generator {
            $getResults = function(array|Traversable $items, int $depth = 0) use ($valueCallback, $keyCallback, $prefix, $nestingKey, &$getResults): Generator {
                foreach ($items as $key => $item) {
                    $value = $valueCallback($item, $key);
                    $key = $keyCallback($item, $key);

                    $value = (string) $value;

                    if ($depth > 0) {
                        $value = str_repeat($prefix, $depth).$value;
                    }

                    yield $key => $value;

                    $children = $item[$nestingKey] ?? null;

                    if (is_array($children) || $children instanceof Traversable) {
                        $nestedItems = $getResults($children, $depth + 1);
                        foreach ($nestedItems as $nestedKey => $nestedItem) {
                            yield $nestedKey => $nestedItem;
                        }
                    }
                }
            };

            yield from $getResults($this);
        });
    }

    /**
     * Pull a random item out of the collection.
     *
     * @return mixed The random item.
     */
    public function randomValue(): mixed
    {
        return $this->shuffle()->first();
    }

    /**
     * Iteratively reduce the collection to a single value using a callback function.
     *
     * @param Closure $callback The callback function to use.
     * @param mixed $initial The initial value.
     * @return mixed The final value.
     */
    public function reduce(Closure $callback, mixed $initial = null): mixed
    {
        $acc = $initial;
        foreach ($this as $key => $item) {
            $acc = $callback($acc, $item, $key);
        }

        return $acc;
    }

    /**
     * Exclude items in the collection using a callback function.
     *
     * @param Closure $callback The callback.
     * @return Collection A new Collection.
     */
    public function reject(Closure $callback): static
    {
        return $this->filter(static::negate($callback));
    }

    /**
     * Reverse the order of items in the collection.
     *
     * @return Collection A new Collection.
     */
    public function reverse(): static
    {
        return new static(array_reverse($this->toArray(), true));
    }

    /**
     * Randomize the order of items in the collection.
     *
     * @return Collection A new Collection.
     */
    public function shuffle(): static
    {
        $data = $this->toArray();
        shuffle($data);

        return new static($data);
    }

    /**
     * Skip a number of items in the collection.
     *
     * @param int $length The number of items to skip.
     * @return Collection A new Collection.
     */
    public function skip(int $length): static
    {
        return new static(function() use ($length): Generator {
            $iterator = $this->getIterator();

            while ($iterator->valid() && $length--) {
                $iterator->next();
            }

            while ($iterator->valid()) {
                yield $iterator->key() => $iterator->current();
                $iterator->next();
            }
        });
    }

    /**
     * Skip items in the collection until a callback returns TRUE.
     *
     * @param Closure $callback The callback.
     * @return Collection A new Collection.
     */
    public function skipUntil(Closure $callback): static
    {
        return new static(function() use ($callback): Generator {
            foreach ($this as $key => $item) {
                if (!$callback($item, $key)) {
                    continue;
                }

                yield $key => $item;
            }
        });
    }

    /**
     * Skip items in the collection until a callback returns FALSE.
     *
     * @param Closure $callback The callback.
     * @return Collection A new Collection.
     */
    public function skipWhile(Closure $callback): static
    {
        return $this->skipUntil(static::negate($callback));
    }

    /**
     * Determine whether some items in the collection pass a callback.
     *
     * @param Closure $callback The callback.
     * @return bool TRUE if some items in the collection pass a callback, otherwise FALSE.
     */
    public function some(Closure $callback): bool
    {
        return !$this->none($callback);
    }

    /**
     * Sort the collection using a callback.
     *
     * @param Closure|int $callback The callback or sort method.
     * @param bool $descending Whether to sort in descending order.
     * @return Collection A new Collection.
     */
    public function sort(Closure|int $callback = self::SORT_NATURAL, bool $descending = false): static
    {
        if (is_int($callback)) {
            return $this->sortBy(null, $callback, $descending);
        }

        $items = $this->toArray();

        uasort($items, $callback);

        return new static($items);
    }

    /**
     * Sort the collection by a given key.
     *
     * @param array|Closure|string|null $valuePath The key path of the value.
     * @param int $sort The sort method.
     * @param bool $descending Whether to sort in descending order.
     * @return Collection A new Collection.
     */
    public function sortBy(array|Closure|string|null $valuePath = null, int $sort = self::SORT_NATURAL, bool $descending = false): static
    {
        $valueCallback = static::valueExtractor($valuePath);

        $results = [];
        $items = $this->toArray();

        foreach ($items as $key => $item) {
            $results[$key] = $valueCallback($item, $key);
        }

        if ($descending) {
            arsort($results, $sort);
        } else {
            asort($results, $sort);
        }

        foreach ($results as $key => $value) {
            $results[$key] = $items[$key];
        }

        return new static($results);
    }

    /**
     * Get the total sum of a key.
     *
     * @param array|Closure|string|null $valuePath The key path of the value.
     * @return float|null The total sum.
     */
    public function sumOf(array|Closure|string|null $valuePath = null): mixed
    {
        $valueCallback = static::valueExtractor($valuePath);

        return $this->reduce(
            fn(mixed $acc, mixed $item, int|string $key): mixed => $acc + $valueCallback($item, $key),
            0
        );
    }

    /**
     * Take a number of items in the collection.
     *
     * @param int $length The number of items.
     * @return Collection A new Collection.
     */
    public function take(int $length): static
    {
        if ($length < 0) {
            return new static(array_slice($this->toArray(), $length, null, true));
        }

        return new static(function() use ($length): Generator {
            $iterator = $this->getIterator();

            while ($iterator->valid() && $length--) {
                yield $iterator->key() => $iterator->current();
                $iterator->next();
            }
        });
    }

    /**
     * Take items in the collection until a callback returns TRUE.
     *
     * @param Closure $callback The callback.
     * @return Collection A new Collection.
     */
    public function takeUntil(Closure $callback): static
    {
        return new static(function() use ($callback): Generator {
            foreach ($this as $key => $item) {
                if ($callback($item, $key)) {
                    break;
                }

                yield $key => $item;
            }
        });
    }

    /**
     * Take items in the collection until a callback returns FALSE.
     *
     * @param Closure $callback The callback.
     * @return Collection A new Collection.
     */
    public function takeWhile(Closure $callback): static
    {
        return $this->takeUntil(static::negate($callback));
    }

    /**
     * Get the items in the collection as an array.
     *
     * @return array The collection items.
     */
    public function toArray(): array
    {
        if (is_array($this->source)) {
            return $this->source;
        }

        return iterator_to_array($this->getIterator());
    }

    /**
     * Convert the collection to a JSON encoded string.
     *
     * @return string The JSON encoded string.
     */
    public function toJson(): string
    {
        return json_encode($this, JSON_PRETTY_PRINT) ?: '';
    }

    /**
     * Get the values in the collection as an array.
     *
     * @return array The collection values.
     */
    public function toList(): array
    {
        return $this->values()->toArray();
    }

    /**
     * Get the unique items in the collection based on a given key.
     *
     * @param array|Closure|string|null $valuePath The key path of the value.
     * @param bool $strict Whether to compare values strictly.
     * @return Collection A new Collection.
     */
    public function unique(array|Closure|string|null $valuePath = null, bool $strict = false): static
    {
        $valueCallback = static::valueExtractor($valuePath);

        return new static(function() use ($valueCallback, $strict): Generator {
            $exists = [];

            foreach ($this as $key => $item) {
                $value = $valueCallback($item, $key);

                if (in_array($value, $exists, $strict)) {
                    continue;
                }

                yield $key => $item;
                $exists[] = $value;
            }
        });
    }

    /**
     * Get the values in the collection.
     *
     * @return Collection A new Collection.
     */
    public function values(): static
    {
        return new static(function(): Generator {
            foreach ($this as $item) {
                yield $item;
            }
        });
    }

    /**
     * Zip one or more iterables with the collection.
     *
     * @param array|Traversable ...$iterables The iterables to merge.
     * @return Collection A new Collection.
     */
    public function zip(array|Traversable ...$iterables): static
    {
        $collections = [
            $this,
            ...array_map(
                fn(array|Traversable $iterable): Collection => new static($iterable),
                $iterables
            ),
        ];

        return new static(function() use ($collections): Generator {
            $iterators = array_map(
                fn(Collection $item): Iterator => $item->getIterator(),
                $collections
            );

            while (true) {
                $values = [];
                foreach ($iterators as $iterator) {
                    if (!$iterator->valid()) {
                        break 2;
                    }

                    $values[] = $iterator->current();
                    $iterator->next();
                }

                yield $values;
            }
        });
    }

    /**
     * Negate the result of a callback.
     *
     * @param Closure $callback The callback.
     * @return Closure A new callback.
     */
    protected static function negate(Closure $callback): Closure
    {
        return fn(...$args): bool => !$callback(...$args);
    }

    /**
     * Build a callback to extract a value from an item.
     *
     * @param array|Closure|string|null $path The path of the value.
     * @return Closure A closure to extract the value.
     */
    protected static function valueExtractor(array|Closure|string|null $path): Closure
    {
        if ($path === null) {
            return fn(mixed $value): mixed => $value;
        }

        if ($path instanceof Closure) {
            return $path;
        }

        return function(mixed $value) use ($path): mixed {
            $paths = is_array($path) ? $path : explode('.', $path);
            foreach ($paths as $path) {
                if ($path === null) {
                    return $value;
                }

                if (is_array($value) && array_key_exists($path, $value)) {
                    $value = $value[$path];
                } else if ($value instanceof ArrayAccess && $value->offsetExists($path)) {
                    $value = $value->offsetGet($path);
                } else if (is_object($value) && property_exists($value, $path)) {
                    $value = $value->$path;
                } else {
                    return null;
                }
            }

            return $value;
        };
    }
}
