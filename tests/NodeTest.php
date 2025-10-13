<?php

use IceTea\IceDOM\ArrayMapNode;
use IceTea\IceDOM\Node;
use IceTea\IceDOM\SafeString;

// Create a concrete implementation of Node for testing
class TestNode extends Node
{
    public function __toString()
    {
        return $this->childrenToString();
    }
}

// Create another test class that extends Node with custom behavior
class CustomTestNode extends Node
{
    private string $prefix;

    public function __construct(string $prefix = '', array $children = [])
    {
        $this->prefix = $prefix;
        parent::__construct($children);
    }

    public function __toString()
    {
        return $this->prefix.$this->childrenToString();
    }
}

beforeEach(function () {
    // Reset any global state before each test
});

describe('Node', function () {

    describe('Constructor and Initialization', function () {
        it('can be created with no children', function () {
            $node = new TestNode;
            expect($node->getChildren())->toBe([]);
        });

        it('can be created with initial children', function () {
            $children = ['child1', 'child2', 123];
            $node = new TestNode($children);
            expect($node->getChildren())->toBe($children);
        });

        it('handles mixed content types in constructor', function () {
            $safeString = new SafeString('<safe>');
            $children = [
                'text',
                42,
                3.14,
                $safeString,
                new TestNode(['nested']),
            ];
            $node = new TestNode($children);
            expect($node->getChildren())->toHaveCount(5);
        });
    });

    describe('Parent-Child Relationships', function () {
        it('can set and get parent', function () {
            $parent = new TestNode;
            $child = new TestNode;

            $child->setParent($parent);

            expect($child->getParent())->toBe($parent);
        });

        it('parent is null by default', function () {
            $node = new TestNode;
            expect($node->getParent())->toBeNull();
        });

        it('child parent is set when appended', function () {
            $parent = new TestNode;
            $child = new TestNode;

            $parent->appendChild($child);

            expect($child->getParent())->toBe($parent);
        });

        it('multiple children have correct parent set', function () {
            $parent = new TestNode;
            $child1 = new TestNode;
            $child2 = new TestNode;

            $parent->appendChildren([$child1, $child2]);

            expect($child1->getParent())->toBe($parent);
            expect($child2->getParent())->toBe($parent);
        });
    });

    describe('Child Management', function () {
        it('can append single child', function () {
            $node = new TestNode;
            $child = 'test child';

            $node->appendChild($child);

            expect($node->getChildren())->toContain($child);
        });

        it('can append multiple children', function () {
            $node = new TestNode;
            $children = ['child1', 'child2', 'child3'];

            $node->appendChildren($children);

            expect($node->getChildren())->toBe($children);
        });

        it('ignores null children', function () {
            $node = new TestNode;

            $node->appendChild(null);
            $node->appendChildren(['valid', null, 'another']);

            expect($node->getChildren())->toBe(['valid', 'another']);
        });

        it('can clear all children', function () {
            $node = new TestNode(['child1', 'child2']);

            $result = $node->clearChildren();

            expect($node->getChildren())->toBe([]);
            expect($result)->toBe($node); // Method chaining
        });

        it('returns children copy', function () {
            $node = new TestNode(['child1', 'child2']);
            $children = $node->getChildren();

            // Modify returned array
            $children[] = 'new';

            // Original should be unchanged
            expect($node->getChildren())->toHaveCount(2);
        });
    });

    describe('Magic Method __invoke', function () {
        it('can add children using function call syntax', function () {
            $node = new TestNode;

            $result = $node('child1', 'child2', 123);

            expect($node->getChildren())->toBe(['child1', 'child2', 123]);
            expect($result)->toBe($node); // Method chaining
        });

        it('works with no arguments', function () {
            $node = new TestNode;

            $node();

            expect($node->getChildren())->toBe([]);
        });
    });

    describe('use() method', function () {
        it('applies closure to node', function () {
            $node = new CustomTestNode;
            $child = new CustomTestNode;

            $node->use(function ($n) use ($child) {
                $n->appendChildren([$child]);
            });

            expect($node->getChildren())->toBe([$child]);
        });

        it('returns self for chaining', function () {
            $node = new TestNode;

            $result = $node->use(function () {
                // No operation
            });

            expect($result)->toBe($node);
        });

        it('handles callable parameter', function () {
            $node = new TestNode;
            $node('hello');

            $callable = function ($n) {
                $n->appendChild(' world');
            };

            $node->use($callable);

            expect((string) $node)->toBe('hello world');
        });

        it('ignores null hook', function () {
            $node = new TestNode;

            $result = $node->use(null);

            expect($result)->toBe($node);
        });
    });

    describe('childrenUse() method', function () {
        it('applies hook to all node children', function () {
            $parent = new TestNode;
            $child1 = new CustomTestNode;
            $child2 = new CustomTestNode;

            $parent->appendChildren([$child1, $child2]);

            $parent->childrenUse(function (Node $c) {
                $c->appendChild('hello');
            });

            expect((string) $child1)->toBe('hello');
            expect((string) $child2)->toBe('hello');
        });

        it('only applies to Node children', function () {
            $parent = new TestNode;
            $childNode = new CustomTestNode;

            $parent->appendChildren(['string', $childNode, 123]);

            $callCount = 0;
            $parent->childrenUse(function ($child) use (&$callCount) {
                $callCount++;
            });

            expect($callCount)->toBe(1); // Only called for the Node child
        });

        it('returns self for chaining', function () {
            $parent = new TestNode;

            $result = $parent->childrenUse(function () {
                // No operation
            });

            expect($result)->toBe($parent);
        });
    });

    describe('map() method', function () {
        it('creates ArrayMapNode with iterable', function () {
            $node = new TestNode;
            $array = ['item1', 'item2', 'item3'];

            $node->map($array);

            $children = $node->getChildren();
            expect($children)->toHaveCount(1);
            expect($children[0])->toBeInstanceOf(ArrayMapNode::class);
        });

        it('creates ArrayMapNode with map function', function () {
            $node = new TestNode;
            $array = [1, 2, 3];
            $mapFunction = function ($item) {
                return "item_$item";
            };

            $node->map($array, $mapFunction);

            $children = $node->getChildren();
            expect($children[0])->toBeInstanceOf(ArrayMapNode::class);
        });
    });

    describe('String Conversion and Rendering', function () {
        it('converts empty children to empty string', function () {
            $node = new TestNode;

            expect((string) $node)->toBe('');
        });

        it('converts string children with HTML escaping', function () {
            $node = new TestNode(['<script>alert("xss")</script>']);

            expect((string) $node)->toBe('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
        });

        it('outputs numbers directly', function () {
            $node = new TestNode([42, 3.14]);

            expect((string) $node)->toBe('423.14');
        });

        it('outputs SafeString without escaping', function () {
            $safeString = new SafeString('<div>safe content</div>');
            $node = new TestNode([$safeString]);

            expect((string) $node)->toBe('<div>safe content</div>');
        });

        it('converts Stringable objects with escaping', function () {
            $stringable = new class
            {
                public function __toString()
                {
                    return '<span>stringable</span>';
                }
            };

            $node = new TestNode([$stringable]);

            expect((string) $node)->toBe('&lt;span&gt;stringable&lt;/span&gt;');
        });

        it('evaluates closures with node as parameter', function () {
            $node = new TestNode;

            $closure = function ($parent) {
                return 'Parent has '.count($parent->getChildren()).' children';
            };

            $node->appendChild($closure);

            expect((string) $node)->toBe('Parent has 1 children');
        });

        it('handles nested nodes', function () {
            $child = new TestNode(['nested content']);
            $parent = new TestNode(['outer ', $child, ' content']);

            expect((string) $parent)->toBe('outer nested content content');
        });

        it('joins multiple children with spaces', function () {
            $node = new TestNode(['first', ' second', ' third']);

            expect((string) $node)->toBe('first second third');
        });

        it('handles mixed content types correctly', function () {
            $safeString = new SafeString('<b>bold</b>');
            $node = new TestNode([
                'text',
                42,
                $safeString,
                function () {
                    return 'closure';
                },
            ]);

            expect((string) $node)->toBe('text42<b>bold</b>closure');
        });
    });

    describe('Protected Methods', function () {
        it('tryEvalClosure evaluates closures', function () {
            $node = new TestNode;

            // Use reflection to access protected method
            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('tryEvalClosure');
            $method->setAccessible(true);

            $closure = function ($parent) {
                return 'evaluated: '.get_class($parent);
            };

            $result = $method->invoke($node, $closure);

            expect($result)->toBe('evaluated: '.get_class($node));
        });

        it('tryEvalClosure returns non-closure values unchanged', function () {
            $node = new TestNode;

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('tryEvalClosure');
            $method->setAccessible(true);

            $value = 'test string';
            $result = $method->invoke($node, $value);

            expect($result)->toBe($value);
        });

        it('childrenToString handles empty children', function () {
            $node = new TestNode;

            $reflection = new ReflectionClass($node);
            $method = $reflection->getMethod('childrenToString');
            $method->setAccessible(true);

            $result = $method->invoke($node);

            expect($result)->toBe('');
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles null in children array', function () {
            $node = new TestNode(['valid', null, 'another']);

            expect((string) $node)->toBe('validanother');
        });

        it('handles closure that returns null', function () {
            $node = new TestNode([
                function () {
                    return null;
                },
            ]);

            expect((string) $node)->toBe('');
        });

        it('handles closure that returns different types', function () {
            $node = new TestNode([
                function () {
                    return 'string';
                },
                function () {
                    return 123;
                },
                function () {
                    return new SafeString('<safe>');
                },
            ]);

            expect((string) $node)->toBe('string123<safe>');
        });

        it('handles deeply nested structures', function () {
            $level3 = new TestNode(['level3']);
            $level2 = new TestNode(['level2 ', $level3]);
            $level1 = new TestNode(['level1 ', $level2]);

            expect((string) $level1)->toBe('level1 level2 level3');
        });

        it('maintains insertion order', function () {
            $node = new TestNode;

            $node->appendChild('first');
            $node->appendChild('second');
            $node->appendChildren(['third', 'fourth']);

            expect($node->getChildren())->toBe(['first', 'second', 'third', 'fourth']);
        });
    });

    describe('Constants', function () {
        it('has correct ENT_FLAGS constant', function () {
            $expectedFlags = ENT_QUOTES | ENT_HTML5;
            expect(TestNode::ENT_FLAGS)->toBe($expectedFlags);
        });
    });
});
