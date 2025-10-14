<?php

use IceTea\IceDOM\Node;
use IceTea\IceDOM\SafeString;
use IceTea\IceDOM\SlotNode;

describe('SlotNode', function () {

    describe('Constructor and Initialization', function () {
        it('can be created with no children and no slot function', function () {
            $node = new SlotNode;
            expect($node->getChildren())->toBe([]);
        });

        it('can be created with initial children only', function () {
            $children = ['child1', 'child2', 123];
            $node = new SlotNode($children);
            expect($node->getChildren())->toBe($children);
        });

        it('can be created with slot function only', function () {
            $slotFunction = function () {
                return 'slot content';
            };
            $node = new SlotNode([], $slotFunction);
            expect($node->getChildren())->toBe([]);
        });

        it('can be created with both children and slot function', function () {
            $children = ['child1', 'child2'];
            $slotFunction = function () {
                return 'slot content';
            };
            $node = new SlotNode($children, $slotFunction);
            expect($node->getChildren())->toBe($children);
        });

        it('extends Node class', function () {
            $node = new SlotNode;
            expect($node)->toBeInstanceOf(Node::class);
        });

        it('inherits Node methods', function () {
            $node = new SlotNode;

            expect(method_exists($node, 'appendChild'))->toBeTrue();
            expect(method_exists($node, 'appendChildren'))->toBeTrue();
            expect(method_exists($node, 'setParent'))->toBeTrue();
            expect(method_exists($node, 'getParent'))->toBeTrue();
            expect(method_exists($node, 'use'))->toBeTrue();
            expect(method_exists($node, 'childrenUse'))->toBeTrue();
            expect(method_exists($node, 'clearChildren'))->toBeTrue();
            expect(method_exists($node, 'getChildren'))->toBeTrue();
            expect(method_exists($node, 'map'))->toBeTrue();
        });
    });

    describe('__toString() with Slot Function', function () {
        it('calls slot function and returns result', function () {
            $slotFunction = function () {
                return 'slot result';
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('slot result');
        });

        it('converts slot function result to string', function () {
            $slotFunction = function () {
                return 123;
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('123');
        });

        it('handles slot function returning SafeString', function () {
            $slotFunction = function () {
                return new SafeString('<div>Safe HTML</div>');
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('<div>Safe HTML</div>');
        });

        it('handles slot function returning Node', function () {
            $slotFunction = function () {
                return new SlotNode(['nested content']);
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('nested content');
        });

        it('handles slot function returning Stringable', function () {
            $stringable = new class implements \Stringable
            {
                public function __toString()
                {
                    return 'stringable result';
                }
            };

            $slotFunction = function () use ($stringable) {
                return $stringable;
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('stringable result');
        });

        it('handles slot function returning null', function () {
            $slotFunction = function () {
                return null;
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('');
        });

        it('handles slot function returning empty string', function () {
            $slotFunction = function () {
                return '';
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('');
        });

        it('ignores children when slot function is provided', function () {
            $slotFunction = function () {
                return 'from slot function';
            };
            $node = new SlotNode(['child1', 'child2'], $slotFunction);

            expect((string) $node)->toBe('from slot function');
        });

        it('handles slot function with complex return value', function () {
            $slotFunction = function () {
                return new SafeString('<ul><li>Item 1</li><li>Item 2</li></ul>');
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('<ul><li>Item 1</li><li>Item 2</li></ul>');
        });

        it('handles slot function that accesses external variables', function () {
            $externalValue = 'external';
            $slotFunction = function () use ($externalValue) {
                return "Value: $externalValue";
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('Value: external');
        });

        it('handles slot function returning float', function () {
            $slotFunction = function () {
                return 3.14159;
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('3.14159');
        });

        it('handles slot function returning boolean', function () {
            $slotFunction = function () {
                return true;
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('1');
        });

        it('slot function can be called multiple times', function () {
            $callCount = 0;
            $slotFunction = function () use (&$callCount) {
                $callCount++;

                return "Call #{$callCount}";
            };
            $node = new SlotNode([], $slotFunction);

            $result1 = (string) $node;
            $result2 = (string) $node;

            expect($result1)->toBe('Call #1');
            expect($result2)->toBe('Call #2');
            expect($callCount)->toBe(2);
        });
    });

    describe('__toString() without Slot Function', function () {
        it('renders children when no slot function provided', function () {
            $node = new SlotNode(['child1', ' ', 'child2']);

            expect((string) $node)->toBe('child1 child2');
        });

        it('returns empty string when no children and no slot function', function () {
            $node = new SlotNode;

            expect((string) $node)->toBe('');
        });

        it('escapes HTML in children', function () {
            $node = new SlotNode(['<script>alert("xss")</script>']);

            expect((string) $node)->toBe('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
        });

        it('handles SafeString children without escaping', function () {
            $safeString = new SafeString('<div>Safe HTML</div>');
            $node = new SlotNode([$safeString]);

            expect((string) $node)->toBe('<div>Safe HTML</div>');
        });

        it('handles numeric children', function () {
            $node = new SlotNode([42, 3.14]);

            expect((string) $node)->toBe('423.14');
        });

        it('handles nested SlotNodes', function () {
            $innerNode = new SlotNode(['inner content']);
            $outerNode = new SlotNode(['outer ', $innerNode, ' end']);

            expect((string) $outerNode)->toBe('outer inner content end');
        });

        it('handles mixed content types', function () {
            $safeString = new SafeString('<b>bold</b>');
            $node = new SlotNode([
                'text',
                42,
                $safeString,
            ]);

            expect((string) $node)->toBe('text42<b>bold</b>');
        });

        it('handles closure children', function () {
            $node = new SlotNode([
                function () {
                    return 'from closure';
                },
            ]);

            expect((string) $node)->toBe('from closure');
        });

        it('handles null children gracefully', function () {
            $node = new SlotNode(['before', null, 'after']);

            expect((string) $node)->toBe('beforeafter');
        });
    });

    describe('Slot Function vs Children Priority', function () {
        it('prioritizes slot function over children', function () {
            $slotFunction = function () {
                return 'slot wins';
            };
            $node = new SlotNode(['these', 'are', 'ignored'], $slotFunction);

            expect((string) $node)->toBe('slot wins');
        });

        it('uses children when slot function is null', function () {
            $node = new SlotNode(['child content'], null);

            expect((string) $node)->toBe('child content');
        });

        it('can dynamically switch between slot function and children', function () {
            // Create a mutable slot node by extending it
            $children = ['child content'];
            $slotFunction = function () {
                return 'slot content';
            };

            $nodeWithSlot = new SlotNode($children, $slotFunction);
            $nodeWithoutSlot = new SlotNode($children, null);

            expect((string) $nodeWithSlot)->toBe('slot content');
            expect((string) $nodeWithoutSlot)->toBe('child content');
        });
    });

    describe('Inherited Node Functionality', function () {
        it('can add children using appendChild', function () {
            $node = new SlotNode;
            $node->appendChild('child content');

            expect((string) $node)->toBe('child content');
        });

        it('can add multiple children using appendChildren', function () {
            $node = new SlotNode;
            $node->appendChildren(['child1', ' ', 'child2']);

            expect((string) $node)->toBe('child1 child2');
        });

        it('can add children using invoke syntax', function () {
            $node = new SlotNode;
            $node('child1', 'child2');

            expect($node->getChildren())->toHaveCount(2);
        });

        it('can clear children', function () {
            $node = new SlotNode(['child1', 'child2']);

            $node->clearChildren();

            expect($node->getChildren())->toBe([]);
            expect((string) $node)->toBe('');
        });

        it('maintains parent-child relationships', function () {
            $parent = new SlotNode;
            $child = new SlotNode(['child content']);

            $parent->appendChild($child);

            expect($child->getParent())->toBe($parent);
            expect((string) $parent)->toBe('child content');
        });

        it('can use use() method for configuration', function () {
            $node = new SlotNode;

            $node->use(function ($n) {
                $n->appendChild('configured content');
            });

            expect((string) $node)->toBe('configured content');
        });

        it('can use childrenUse() method', function () {
            $parent = new SlotNode;
            $child1 = new SlotNode(['Child1']);
            $child2 = new SlotNode(['Child2']);

            $parent->appendChildren([$child1, $child2]);

            $callCount = 0;
            $parent->childrenUse(function ($child) use (&$callCount) {
                $callCount++;
                $child->appendChild(' - modified');
            });

            expect($callCount)->toBe(2);
            expect((string) $parent)->toBe('Child1 - modifiedChild2 - modified');
        });

        it('can use map() method', function () {
            $node = new SlotNode;
            $array = ['item1', 'item2', 'item3'];

            $node->map($array);

            $children = $node->getChildren();
            expect($children)->toHaveCount(1);
            expect($children[0])->toBeInstanceOf(\IceTea\IceDOM\ArrayMap::class);
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles slot function that throws exception', function () {
            $slotFunction = function () {
                throw new \Exception('Test exception');
            };
            $node = new SlotNode([], $slotFunction);

            expect(fn () => (string) $node)->toThrow(\Exception::class);
        });

        it('handles very large content from slot function', function () {
            $largeContent = str_repeat('A', 10000);
            $slotFunction = function () use ($largeContent) {
                return $largeContent;
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe($largeContent);
            expect(strlen((string) $node))->toBe(10000);
        });

        it('handles deeply nested SlotNodes', function () {
            $level3 = new SlotNode(['L3']);
            $level2 = new SlotNode(['L2 ', $level3]);
            $level1 = new SlotNode(['L1 ', $level2]);

            expect((string) $level1)->toBe('L1 L2 L3');
        });

        it('handles deeply nested SlotNodes with slot functions', function () {
            $level3 = new SlotNode([], function () {
                return 'L3';
            });
            $level2 = new SlotNode([], function () use ($level3) {
                return 'L2 '.$level3;
            });
            $level1 = new SlotNode([], function () use ($level2) {
                return 'L1 '.$level2;
            });

            expect((string) $level1)->toBe('L1 L2 L3');
        });

        it('handles UTF-8 content from slot function', function () {
            $utf8Content = '🎉 Hello 世界 🌍';
            $slotFunction = function () use ($utf8Content) {
                return $utf8Content;
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe($utf8Content);
        });

        it('handles UTF-8 content in children', function () {
            $utf8Content = '🎉 Hello 世界 🌍';
            $node = new SlotNode([$utf8Content]);

            expect((string) $node)->toBe($utf8Content);
        });

        it('handles binary content from slot function', function () {
            $binaryContent = "\x00\x01\x02\xFF";
            $slotFunction = function () use ($binaryContent) {
                return $binaryContent;
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe($binaryContent);
        });

        it('maintains insertion order of children', function () {
            $node = new SlotNode;

            $node->appendChild('first');
            $node->appendChild('second');
            $node->appendChildren(['third', 'fourth']);

            expect($node->getChildren())->toBe(['first', 'second', 'third', 'fourth']);
        });
    });

    describe('Integration with Other Components', function () {
        it('works with ArrayMap in children', function () {
            $arrayMap = new \IceTea\IceDOM\ArrayMap(
                ['a', 'b', 'c'],
                function ($item) {
                    return $item.'_mapped ';
                }
            );

            $node = new SlotNode([$arrayMap]);
            expect((string) $node)->toBe('a_mapped b_mapped c_mapped ');
        });

        it('works with ArrayMap in slot function', function () {
            $slotFunction = function () {
                $arrayMap = new \IceTea\IceDOM\ArrayMap(
                    ['x', 'y', 'z'],
                    function ($item) {
                        return $item.'! ';
                    }
                );

                return $arrayMap;
            };

            $node = new SlotNode([], $slotFunction);
            expect((string) $node)->toBe('x! y! z! ');
        });

        it('works with HtmlNode in children', function () {
            $htmlNode = new \IceTea\IceDOM\HtmlNode(['content'], 'div', ['class' => 'container']);
            $node = new SlotNode([$htmlNode]);
            $output = (string) $node;

            expect($output)->toContain('<div');
            expect($output)->toContain('class="container"');
            expect($output)->toContain('content');
            expect($output)->toContain('</div>');
        });

        it('works with HtmlNode in slot function', function () {
            $slotFunction = function () {
                return new \IceTea\IceDOM\HtmlNode(['slot content'], 'span', ['id' => 'test']);
            };
            $node = new SlotNode([], $slotFunction);
            $output = (string) $node;

            expect($output)->toContain('<span');
            expect($output)->toContain('id="test"');
            expect($output)->toContain('slot content');
            expect($output)->toContain('</span>');
        });

        it('works with RawNode in children', function () {
            $rawNode = new \IceTea\IceDOM\RawNode(['<raw>content</raw>']);
            $node = new SlotNode([$rawNode]);

            expect((string) $node)->toBe('<raw>content</raw>');
        });

        it('works with RawNode in slot function', function () {
            $slotFunction = function () {
                return new \IceTea\IceDOM\RawNode(['<raw>from slot</raw>']);
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('<raw>from slot</raw>');
        });

        it('works with EchoNode in children', function () {
            $echoNode = new \IceTea\IceDOM\EchoNode([
                function () {
                    echo 'echoed content';
                },
            ]);
            $node = new SlotNode([$echoNode]);

            expect((string) $node)->toBe('echoed content');
        });

        it('works with EchoNode in slot function', function () {
            $slotFunction = function () {
                return new \IceTea\IceDOM\EchoNode([
                    function () {
                        echo 'slot echo';
                    },
                ]);
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('slot echo');
        });
    });

    describe('Real-world Use Cases', function () {
        it('can implement conditional rendering with slot function', function () {
            $showContent = true;
            $slotFunction = function () use ($showContent) {
                return $showContent ? 'Content shown' : 'Content hidden';
            };
            $node = new SlotNode(['fallback'], $slotFunction);

            expect((string) $node)->toBe('Content shown');
        });

        it('can implement dynamic content loading', function () {
            $data = ['name' => 'John', 'age' => 30];
            $slotFunction = function () use ($data) {
                return "Name: {$data['name']}, Age: {$data['age']}";
            };
            $node = new SlotNode([], $slotFunction);

            expect((string) $node)->toBe('Name: John, Age: 30');
        });

        it('can implement template slots with default content', function () {
            // Slot with custom content
            $customSlot = new SlotNode(['default'], function () {
                return 'custom content';
            });

            // Slot with default content (no function)
            $defaultSlot = new SlotNode(['default content'], null);

            expect((string) $customSlot)->toBe('custom content');
            expect((string) $defaultSlot)->toBe('default content');
        });

        it('can implement lazy evaluation for performance', function () {
            $expensiveComputationCalled = false;
            $slotFunction = function () use (&$expensiveComputationCalled) {
                $expensiveComputationCalled = true;

                return 'expensive result';
            };

            $node = new SlotNode([], $slotFunction);

            // Not called until toString
            expect($expensiveComputationCalled)->toBeFalse();

            $result = (string) $node;

            expect($expensiveComputationCalled)->toBeTrue();
            expect($result)->toBe('expensive result');
        });

        it('can implement component composition', function () {
            $header = new SlotNode([], function () {
                return new \IceTea\IceDOM\HtmlNode(['Header'], 'h1');
            });

            $content = new SlotNode([], function () {
                return new \IceTea\IceDOM\HtmlNode(['Main content'], 'p');
            });

            $footer = new SlotNode([], function () {
                return new \IceTea\IceDOM\HtmlNode(['Footer'], 'footer');
            });

            $page = new SlotNode([$header, $content, $footer]);
            $output = (string) $page;

            expect($output)->toContain('<h1>Header</h1>');
            expect($output)->toContain('<p>Main content</p>');
            expect($output)->toContain('<footer>Footer</footer>');
        });
    });
});
