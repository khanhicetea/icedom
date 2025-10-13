<?php

use IceTea\IceDOM\ArrayMapNode;
use IceTea\IceDOM\RawNode;

it('can be instantiated with empty parameters', function () {
    $node = new ArrayMapNode;

    expect($node)->toBeInstanceOf(ArrayMapNode::class);
    expect($node->__toString())->toBe('');
});

it('can be instantiated with array of simple values', function () {
    $items = ['foo', 'bar', 'baz'];
    $node = new ArrayMapNode([], $items);

    $result = $node->__toString();
    expect($result)->toBe('foobarbaz');
});

it('can be instantiated with array of Node objects', function () {
    $items = [
        new RawNode(['Hello']),
        new RawNode([' ']),
        new RawNode(['World']),
    ];
    $node = new ArrayMapNode([], $items);

    $result = $node->__toString();
    expect($result)->toBe('Hello World');
});

it('non-empty init children', function () {
    $items = [
        new RawNode(['Hello']),
        new RawNode([' ']),
        new RawNode(['World']),
    ];
    new ArrayMapNode(['Hi, ', 'Chao, '], $items);
})->throws(Exception::class);

it('can use a mapping function to transform array items', function () {
    $items = [1, 2, 3];
    $mapFunction = function ($value, $key) {
        return new RawNode(["Item $value"]);
    };

    $node = new ArrayMapNode([], $items, $mapFunction);
    $result = $node->__toString();

    expect($result)->toBe('Item 1Item 2Item 3');
});

it('handles mixed array values with mapping function', function () {
    $items = ['text', 123, true];
    $mapFunction = function ($value, $key) {
        return new RawNode([gettype($value).': '.$value]);
    };

    $node = new ArrayMapNode([], $items, $mapFunction);
    $result = $node->__toString();

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
    $node = new ArrayMapNode([], $items, $mapFunction);
    $parent->appendChild($node);
    $node->__toString();

    foreach ($children as $child) {
        expect($child->getParent())->toBe($parent);
    }
});

it('resets children array after rendering', function () {
    $items = ['first', 'second'];
    $node = new ArrayMapNode([], $items);

    $node->__toString();

    expect($node->getChildren())->toHaveCount(0);
});

it('works with associative arrays', function () {
    $items = ['name' => 'John', 'age' => 30];
    $mapFunction = function ($value, $key) {
        return new RawNode(["$key: $value "]);
    };

    $node = new ArrayMapNode([], $items, $mapFunction);
    $result = $node->__toString();

    expect($result)->toBe('name: John age: 30 ');
});

it('works with iterable objects', function () {
    $items = new ArrayIterator(['a', 'b', 'c']);
    $node = new ArrayMapNode([], $items);

    $result = $node->__toString();
    expect($result)->toBe('abc');
});

it('throws exception when trying to append child directly', function () {
    $node = new ArrayMapNode;

    expect(fn () => $node->appendChild(new RawNode(['test'])))
        ->toThrow(Exception::class, 'Can not append child directly into ArrayMapNode !');
});

it('handles null array gracefully', function () {
    $node = new ArrayMapNode;

    $result = $node->__toString();
    expect($result)->toBe('');
});

it('handles empty array', function () {
    $node = new ArrayMapNode([], []);

    $result = $node->__toString();
    expect($result)->toBe('');
});

it('supports closure as mapping function', function () {
    $items = [1, 2, 3];
    $mapFunction = fn ($value) => new RawNode(["[$value]"]);

    $node = new ArrayMapNode([], $items, $mapFunction);
    $result = $node->__toString();

    expect($result)->toBe('[1][2][3]');
});

it('can be rendered multiple times with same result', function () {
    $items = ['test'];
    $mapFunction = fn ($value) => new RawNode([$value]);

    $node = new ArrayMapNode([], $items, $mapFunction);

    $firstRender = $node->__toString();
    $secondRender = $node->__toString();

    expect($firstRender)->toBe($secondRender);
    expect($firstRender)->toBe('test');
});

it('handles mapping function returning strings', function () {
    $items = ['a', 'b', 'c'];
    $mapFunction = fn ($value) => strtoupper($value);

    $node = new ArrayMapNode([], $items, $mapFunction);
    $result = $node->__toString();

    expect($result)->toBe('ABC');
});

it('preserves order of array items', function () {
    $items = ['first', 'second', 'third'];
    $mapFunction = fn ($value) => new RawNode([$value.',']);

    $node = new ArrayMapNode([], $items, $mapFunction);
    $result = $node->__toString();

    expect($result)->toBe('first,second,third,');
});
