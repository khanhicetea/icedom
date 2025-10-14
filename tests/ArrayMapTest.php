<?php

use IceTea\IceDOM\ArrayMap;
use IceTea\IceDOM\RawNode;

describe('ArrayMap', function () {
    it('can be instantiated with empty parameters', function () {
        $map = new ArrayMap;

        expect($map)->toBeInstanceOf(ArrayMap::class);
        expect($map->__toString())->toBe('');
    });

    it('can be instantiated with array of simple values', function () {
        $items = ['foo', 'bar', 'baz'];
        $map = new ArrayMap($items);

        $result = $map->__toString();
        expect($result)->toBe('foobarbaz');
    });

    it('can be instantiated with array of Node objects', function () {
        $items = [
            new RawNode(['Hello']),
            new RawNode([' ']),
            new RawNode(['World']),
        ];
        $map = new ArrayMap($items);

        $result = $map->__toString();
        expect($result)->toBe('Hello World');
    });

    it('can use a mapping function to transform array items', function () {
        $items = [1, 2, 3];
        $mapFunction = function ($value, $key) {
            return new RawNode(["Item $value"]);
        };

        $map = new ArrayMap($items, $mapFunction);
        $result = $map->__toString();

        expect($result)->toBe('Item 1Item 2Item 3');
    });

    it('handles mixed array values with mapping function', function () {
        $items = ['text', 123, true];
        $mapFunction = function ($value, $key) {
            return new RawNode([gettype($value).': '.$value]);
        };

        $map = new ArrayMap($items, $mapFunction);
        $result = $map->__toString();

        expect($result)->toBe('string: textinteger: 123boolean: 1');
    });

    it('sets parent relationship for generated Node children', function () {
        $items = ['child1', 'child2'];
        $children = [];

        $mapFunction = function ($value) use (&$children) {
            $child = new RawNode([$value]);
            $children[] = $child;

            return $child;
        };

        $parent = new RawNode;
        $map = new ArrayMap($items, $mapFunction);
        $map->setParent($parent);
        $map->__toString();

        foreach ($children as $child) {
            expect($child->getParent())->toBe($parent);
        }
    });

    it('works with associative arrays', function () {
        $items = ['name' => 'John', 'age' => 30];
        $mapFunction = function ($value, $key) {
            return new RawNode(["$key: $value "]);
        };

        $map = new ArrayMap($items, $mapFunction);
        $result = $map->__toString();

        expect($result)->toBe('name: John age: 30 ');
    });

    it('works with iterable objects', function () {
        $items = new ArrayIterator(['a', 'b', 'c']);
        $map = new ArrayMap($items);

        $result = $map->__toString();
        expect($result)->toBe('abc');
    });

    it('handles null array gracefully', function () {
        $map = new ArrayMap;

        $result = $map->__toString();
        expect($result)->toBe('');
    });

    it('handles empty array', function () {
        $map = new ArrayMap([]);

        $result = $map->__toString();
        expect($result)->toBe('');
    });

    it('supports closure as mapping function', function () {
        $items = [1, 2, 3];
        $mapFunction = fn ($value) => new RawNode(["[$value]"]);

        $map = new ArrayMap($items, $mapFunction);
        $result = $map->__toString();

        expect($result)->toBe('[1][2][3]');
    });

    it('can be rendered multiple times with same result', function () {
        $items = ['test'];
        $mapFunction = fn ($value) => new RawNode([$value]);

        $map = new ArrayMap($items, $mapFunction);

        $firstRender = $map->__toString();
        $secondRender = $map->__toString();

        expect($firstRender)->toBe($secondRender);
        expect($firstRender)->toBe('test');
    });

    it('handles mapping function returning strings', function () {
        $items = ['a', 'b', 'c'];
        $mapFunction = fn ($value) => strtoupper($value);

        $map = new ArrayMap($items, $mapFunction);
        $result = $map->__toString();

        expect($result)->toBe('ABC');
    });

    it('preserves order of array items', function () {
        $items = ['first', 'second', 'third'];
        $mapFunction = fn ($value) => new RawNode([$value.',']);

        $map = new ArrayMap($items, $mapFunction);
        $result = $map->__toString();

        expect($result)->toBe('first,second,third,');
    });

    it('can be used directly as string in context', function () {
        $items = ['Hello', ' ', 'World'];
        $map = new ArrayMap($items);

        $result = "Greeting: $map";
        expect($result)->toBe('Greeting: Hello World');
    });

    it('properly escapes string values', function () {
        $items = ['<script>alert("xss")</script>'];
        $map = new ArrayMap($items);

        $result = $map->__toString();
        expect($result)->toBe('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
    });

    it('handles numeric values correctly', function () {
        $items = [1, 2.5, -3];
        $map = new ArrayMap($items);

        $result = $map->__toString();
        expect($result)->toBe('12.5-3');
    });

    it('can chain setParent method', function () {
        $items = ['test'];
        $parent = new RawNode;

        $map = new ArrayMap($items);
        $result = $map->setParent($parent)->__toString();

        expect($result)->toBe('test');
        expect($map->getParent())->toBe($parent);
    });

    it('works with Stringable objects', function () {
        $stringableObject = new class
        {
            public function __toString(): string
            {
                return 'Stringable content';
            }
        };

        $items = [$stringableObject];
        $map = new ArrayMap($items);

        $result = $map->__toString();
        expect($result)->toBe('Stringable content');
    });
});
