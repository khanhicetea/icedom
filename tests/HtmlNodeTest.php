<?php

use IceTea\IceDOM\HtmlNode;

describe('HtmlNode', function () {

    describe('Constructor and Initialization', function () {
        it('can be created with no parameters', function () {
            $node = new HtmlNode;
            expect($node->getChildren())->toBe([]);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBeNull();

            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe([]);

            $property = $reflection->getProperty('isVoid');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBeFalse();
        });

        it('can be created with children only', function () {
            $children = ['child1', 'child2'];
            $node = new HtmlNode($children);
            expect($node->getChildren())->toBe($children);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBeNull();

            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe([]);
        });

        it('can be created with all parameters', function () {
            $children = ['content'];
            $tagName = 'div';
            $attrs = ['class' => 'test', 'id' => 'example'];
            $isVoid = false;

            $node = new HtmlNode($children, $tagName, $attrs, $isVoid);

            expect($node->getChildren())->toBe($children);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe($tagName);

            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe($attrs);

            $property = $reflection->getProperty('isVoid');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe($isVoid);
        });

        it('can be created as void element', function () {
            $node = new HtmlNode([], 'br', [], true);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('isVoid');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBeTrue();

            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe('br');
        });
    });

    describe('Static tag() Factory Method', function () {
        it('creates node with string content as first argument', function () {
            $node = HtmlNode::tag('p', 'Hello world', null);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe('p');

            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe(['_' => 'Hello world']);

            expect($node->getChildren())->toBe([]);
        });

        it('creates node with string content and children', function () {
            $children = ['child1', 'child2'];
            $node = HtmlNode::tag('p', 'Hello', $children);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe('p');

            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe(['_' => 'Hello']);

            expect($node->getChildren())->toBe($children);
        });

        it('creates node with indexed array as children', function () {
            $childArray = ['item1', 'item2'];
            $node = HtmlNode::tag('ul', $childArray, null);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe('ul');

            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe([]);

            expect($node->getChildren())->toBe($childArray);
        });

        it('creates node with associative array as attributes', function () {
            $attrs = ['class' => 'test', 'id' => 'my-id'];
            $node = HtmlNode::tag('div', $attrs, null);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe('div');

            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe($attrs);

            expect($node->getChildren())->toBe([]);
        });

        it('creates node with associative array containing content', function () {
            $attrs = ['class' => 'test', '_' => 'content'];
            $node = HtmlNode::tag('div', $attrs, null);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe($attrs);
        });

        it('creates node with associative array having first element as content', function () {
            $attrs = ['content', 'class' => 'test'];
            $node = HtmlNode::tag('div', $attrs, null);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe(['_' => 'content', 'class' => 'test']);
        });

        it('creates node with null first argument and children array', function () {
            $children = ['child1', 'child2'];
            $node = HtmlNode::tag('span', null, $children);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe('span');

            $property = $reflection->getProperty('attrs');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe([]);

            expect($node->getChildren())->toBe($children);
        });

        it('creates node with single child as first argument', function () {
            $child = new HtmlNode([], 'strong');
            $node = HtmlNode::tag('p', $child, null);
            expect($node->getChildren())->toBe([$child]);
        });

        it('creates void element correctly', function () {
            $node = HtmlNode::tag('br', null, null, true);

            $reflection = new ReflectionClass($node);
            $property = $reflection->getProperty('isVoid');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBeTrue();

            $property = $reflection->getProperty('tagName');
            $property->setAccessible(true);
            expect($property->getValue($node))->toBe('br');
        });
    });

    describe('Constants', function () {
        it('has correct BOOLEAN_ATTRS constant', function () {
            $expected = ['allowfullscreen', 'async', 'autofocus', 'autoplay', 'checked', 'controls', 'default', 'defer', 'disabled', 'formnovalidate', 'hidden', 'ismap', 'itemscope', 'loop', 'multiple', 'muted', 'nomodule', 'novalidate', 'open', 'readonly', 'required', 'reversed', 'selected'];
            expect(HtmlNode::BOOLEAN_ATTRS)->toBe($expected);
        });

        it('has correct VOID_TAGS constant', function () {
            $expected = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
            expect(HtmlNode::VOID_TAGS)->toBe($expected);
        });
    });

    describe('appendChild() Method', function () {
        it('can append child to regular element', function () {
            $node = new HtmlNode([], 'div');
            $child = 'content';

            $result = $node->appendChild($child);

            expect($node->getChildren())->toContain($child);
            expect($result)->toBe($node); // Method chaining
        });

        it('throws exception when appending child to void element', function () {
            $node = new HtmlNode([], 'br', [], true);

            expect(fn () => $node->appendChild('content'))->toThrow('Void element <br> cannot have children.');
        });

        it('throws exception with correct tag name', function () {
            $node = new HtmlNode([], 'img', [], true);

            expect(fn () => $node->appendChild('src'))->toThrow('Void element <img> cannot have children.');
        });
    });

    describe('attributesToString() Method', function () {
        it('returns empty string for no attributes', function () {
            $node = new HtmlNode([], 'div', []);

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe('');
        });

        it('handles special underscore attribute', function () {
            $node = new HtmlNode([], 'div', ['_' => 'special']);

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe(' special');
        });

        it('handles boolean true attributes', function () {
            $node = new HtmlNode([], 'input', ['disabled' => true]);

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe(' disabled');
        });

        it('handles boolean false attributes for boolean attrs', function () {
            $node = new HtmlNode([], 'input', ['disabled' => false]);

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe('');
        });

        it('handles regular string attributes', function () {
            $node = new HtmlNode([], 'div', ['class' => 'test-class']);

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe(' class="test-class"');
        });

        it('escapes attribute values', function () {
            $node = new HtmlNode([], 'div', ['title' => 'Quote "test" & <tag>']);

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe(' title="Quote &quot;test&quot; &amp; &lt;tag&gt;"');
        });

        it('escapes attribute keys', function () {
            $node = new HtmlNode([], 'div', ['data-attr"bad' => 'value']);

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe(' data-attr&quot;bad="value"');
        });

        it('handles closure attributes', function () {
            $node = new HtmlNode([], 'div', ['data-value' => function ($n) {
                return 'dynamic';
            }]);

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe(' data-value="dynamic"');
        });

        it('handles closure with node context', function () {
            $node = new HtmlNode([], 'div', ['data-count' => function ($n) {
                return count($n->getChildren());
            }]);
            $node->appendChild('test');

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe(' data-count="1"');
        });

        it('handles mixed attributes', function () {
            $node = new HtmlNode([], 'div', [
                'id' => 'test',
                'disabled' => true,
                '_' => 'content',
                'data-value' => 'value',
            ]);

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('attributesToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);
            expect($result)->toBe(' id="test" disabled content data-value="value"');
        });
    });

    describe('setAttribute() and getAttribute() Methods', function () {
        it('can set and get attribute', function () {
            $node = new HtmlNode([], 'div');

            $result = $node->setAttribute('href', '#link');

            expect($node->getAttribute('href'))->toBe('#link');
            expect($result)->toBe($node); // Method chaining
        });

        it('gets attribute with default value', function () {
            $node = new HtmlNode([], 'div');

            expect($node->getAttribute('nonexistent', 'default'))->toBe('default');
        });

        it('gets null for nonexistent attribute without default', function () {
            $node = new HtmlNode([], 'div');

            expect($node->getAttribute('nonexistent'))->toBeNull();
        });

        it('overwrites existing attribute', function () {
            $node = new HtmlNode([], 'div', ['class' => 'old']);

            $node->setAttribute('class', 'new');

            expect($node->getAttribute('class'))->toBe('new');
        });
    });
});
