# FyreCollection

**FyreCollection** is a free, open-source immutable collection library for *PHP*.

It is a modern library, and features support for generators and lazy evaluation.


## Table Of Contents
- [Installation](#installation)
- [Collection Creation](#collection-creation)
- [Collection Iteration](#collection-iteration)
- [Collection Methods](#collection-methods)



## Installation

**Using Composer**

```
composer require fyre/collection
```

In PHP:

```php
use Fyre\Collection\Collection;
```


## Collection Creation

- `$source` can be either an array, a *Closure* that returns a *Generator*, or a *Traversable* or *JsonSerializable* object.

```php
$collection = new Collection($source);
```

**Empty**

Create an empty collection.

```php
$collection = Collection::empty();
```

**Range**

Create a collection for a range of numbers.

- `$from` is an integer representing the start of the range.
- `$to` is an integer representing the end of the range.

```php
$collection = Collection::range($from, $to);
```


## Collection Iteration

The *Collection* is an implementation of an *Iterator* and can be used in a `foreach` loop.

```php
foreach ($collection AS $key => $value) { }
```


## Collection Methods

**Avg**

Get the average value of a key.

- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value, and will default to *null*.

```php
$avg = $collection->avg($valuePath);
```

**Cache**

Cache the computed values via a new collection.

```php
$cache = $collection->cache();
```

**Chunk**

Split the collection into chunks.

- `$size` is a number representing the size of each chunk.
- `$preserveKey` is a boolean indicating whether to preserve the keys, and will default to *false*.

```php
$chunk = $collection->chunk($size, $preserveKeys);
```

**Collect**

Collect the computed values into a new collection.

```php
$collect = $collection->collect();
```

**Combine**

Re-index the items in the collection by a given key, using a given value.

- `$keyPath` is a string or array representing the path to the new key, or a *Closure* that receives the item and key as arguments, and should return the new key.
- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value.

```php
$combine = $collection->combine($keyPath, $valuePath);
```

**Count**

Count all items in the collection.

```php
$count = $collection->count();
```

**Count By**

Groups the items in the collection by a given key, and count the number of items in each.

- `$keyPath` is a string or array representing the path to the group key, or a *Closure* that receives the item and key as arguments, and should return the group key.

```php
$countBy = $collection->countBy($keyPath);
```

**Dot**

Flatten a multi-dimensional collection using "dot" notation.

```php
$dot = $collection->dot();
```

**Each**

Execute a callback on each item in the collection.

- `$callback` is a *Closure* that receives the item and key as arguments.

```php
$collection->each($callback);
```

**Every**

Determine whether every item in the collection passes a callback.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$every = $collection->every($callback);
```

**Except**

Return a collection without the specified keys.

- `$keys` is an array containing the keys to remove.

```php
$except = $collection->except($callback);
```

**Extract**

Extract values from the collection using "dot" notation.

- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value.

```php
$extract = $collection->extract($valuePath);
``` 

**Filter**

Filter items in the collection using a callback function.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$filter = $collection->filter($callback);
``` 

**Find**

Find the first value in the collection that passes a callback.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$find = $collection->find($callback);
``` 

**Find Last**

Find the last value in the collection that passes a callback.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$findLast = $collection->findLast($callback);
``` 

**First**

Get the first value in the collection.

```php
$first = $collection->first();
``` 

**Flatten**

Flatten a multi-dimensional collection into a single level.

- `$maxDepth` is a number representing the maximum depth to flatten, and will default to *PHP_INT_MAX*.

```php
$flatten = $collection->flattened($maxDepth);
``` 

**Flip**

Swap the keys and values of a collection.

```php
$flip = $collection->flip();
``` 

**Group By**

Group the items in the collection by a given key.

- `$keyPath` is a string or array representing the path to the new key, or a *Closure* that receives the item and key as arguments, and should return the new key.

```php
$groupBy = $collection->groupBy($keyPath);
```

**Includes**

Determine whether a given value exists in the collection.

- `$value` is the value to test for.

```php
$includes = $collection->includes($value);
``` 

**Index By**

Re-index the items in the collection by a given key.

- `$keyPath` is a string or array representing the path to the new key, or a *Closure* that receives the item and key as arguments, and should return the new key.

```php
$indexBy = $collection->indexBy($keyPath);
```

**Index Of**

Search the collection for a given value and return the first key.

- `$value` is the value to test for.

```php
$indexOf = $collection->indexOf($value);
``` 

**Is Empty**

Determine whether the collection is empty.

```php
$isEmpty = $collection->isEmpty();
```

**Join**

Join the items in the collection using a specified separator.

- `$glue` is a string representing the separator to join with.
- `$finalGlue` is a string representing the final separator to join with, and will default to *null*.

```php
$join = $collection->join($glue, $finalGlue);
``` 

**Keys**

Get the keys in the collection.

```php
$keys = $collection->keys();
``` 

**Last**

Get the last value in the collection.

```php
$last = $collection->last();
``` 

**Last Index Of**

Search the collection for a given value and return the last key.

- `$value` is the value to test for.

```php
$lastIndexOf = $collection->lastIndexOf($value);
``` 

**List Nested**

Flatten nested items into a list.

- `$order` is a string representing the order, and will default to *"desc"*.
- `$nestingKey` is a string representing the nesting key, and will default to *"children"*.

```php
$listNested = $collection->listNested($order, $nestingKey);
``` 

**Map**

Apply a callback to the items in the collection.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return the new value.

```php
$map = $collection->map($callback);
``` 

**Max**

Get the maximum value of a key.

- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value, and will default to *null*.

```php
$max = $collection->max($valuePath);
```

**Median**

Get the median value of a key.

- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value, and will default to *null*.

```php
$median = $collection->median($valuePath);
```

**Merge**

Merge one or more iterables into the collection.

All arguments supplied must be either an array or *Iterator*, and will be merged with the collection.

```php
$merge = $collection->merge(...$iterables);
```

**Min**

Get the minimum value of a key.

- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value, and will default to *null*.

```php
$min = $collection->min($valuePath);
```

**Nest**

Nest child items inside parent items.

- `$idPath` is a string or array representing the path to the ID, or a *Closure* that receives the item and key as arguments, and should return the ID, and will default to *"id"*.
- `$parentPath` is a string or array representing the path to the parent ID, or a *Closure* that receives the item and key as arguments, and should return the parent ID, and will default to *"id"*.
- `$nestingKey` is a string representing the nesting key, and will default to *"children"*.

```php
$nest = $collection->nest($idPath, $parentPath, $nestingKey);
```

**None**

Determine whether no items in the collection pass a callback.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$none = $collection->none($callback);
``` 

**Only**

Return a Collection with only the specified keys.

- `$keys` is an array containing the keys to include.

```php
$only = $collection->only($callback);
```

**Print Nested**

Format nested list items based on depth.

- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value, and will default to *"id"*.
- `$idPath` is a string or array representing the path to the ID, or a *Closure* that receives the item and key as arguments, and should return the ID, and will default to *"id"*.
- `$nestingKey` is a string representing the nesting key, and will default to *"children"*.

```php
$printNested = $collection->printNested($valuePath, $idPath, $prefix, $nestingKey);
``` 

**Random Value**

Pull a random item out of the collection.

```php
$randomValue = $collection->randomValue();
``` 

**Reduce**

Iteratively reduce the collection to a single value using a callback function.

- `$callback` is a *Closure* that receives the accumulated value, item and key as arguments, and should return the next value.
- `$initial` is the initial value to use, and will default to *null*.

```php
$reduce = $collection->reduce($callback, $initial);
``` 

**Reject**

Exclude items in the collection using a callback function.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$reject = $collection->reject($callback);
``` 

**Reverse**

Reverse the order of items in the collection.

```php
$reverse = $collection->reverse();
``` 

**Shuffle**

Randomize the order of items in the collection.

```php
$shuffle = $collection->shuffle();
``` 

**Skip**

Skip a number of items in the collection.

- `$length` is a number representing the number of items to skip.

```php
$skip = $collection->skip($length);
``` 

**Skip Until**

Skip items in the collection until a callback returns *true*.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$skipUntil = $collection->skipUntil($callback);
``` 

**Skip While**

Skip items in the collection until a callback returns *false*.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$skipWhile = $collection->skipWhile($callback);
``` 

**Some**

Determine whether some items in the collection pass a callback.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$some = $collection->some($callback);
``` 

**Sort**

Sort the collection using a callback.

- `$callback` is a *Closure* that receives 2 items to compare, and should return an integer to determine the sort order.

```php
$sort = $collection->sort($callback);
```

Alternatively, you can sort the collection items in ascending order.

- `$sort` is a number representing the sorting flag, and will default to *Collection::SORT_NATURAL*.

```php
$sort = $collection->sort($sort);
```

**Sort By**

Sort the collection by a given key.

- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value, and will default to *null*.
- `$sort` is a number representing the sorting flag, and will default to *Collection::SORT_NATURAL*.
- `$descending` is a boolean indicating whether to sort in descending order, and will default to *false*.

```php
$sortBy = $collection->sortBy($valuePath, $sort, $descending);
```

**Sum Of**

Get the total sum of a key.

- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value, and will default to *null*.

```php
$sumOf = $collection->sumOf($valuePath);
```

**Take**

Take a number of items in the collection.

- `$length` is a number representing the number of items to skip.

```php
$take = $collection->take($length);
``` 

**Take Until**

Take items in the collection until a callback returns *true*.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$takeUntil = $collection->takeUntil($callback);
``` 

**Take While**

Take items in the collection until a callback returns *false*.

- `$callback` is a *Closure* that receives the item and key as arguments, and should return a boolean.

```php
$takeWhile = $collection->takeWhile($callback);
``` 

**To Array**

Get the items in the collection as an array.

```php
$array = $collection->toArray();
```

**To Json**

```php
$json = $collection->toJson();
```

**To List**

Get the values in the collection as an array.

```php
$list = $collection->toList();
```

**Unique**

Get the unique items in the collection based on a given key.

- `$valuePath` is a string or array representing the path to the value, or a *Closure* that receives the item and key as arguments, and should return the value, and will default to *null*.
- `$strict` is a boolean indicating whether to perform strict equality checks, and will default to *false*.

```php
$unique = $collection->unique($valuePath, $strict);
```

**Values**

Get the values in the collection.

```php
$values = $collection->values();
```

**Zip**

Zip one or more iterables with the collection.

All arguments supplied must be either an array or *Iterator*, and will be zipped with the collection.

```php
$zip = $collection->zip(...$iterables);
```