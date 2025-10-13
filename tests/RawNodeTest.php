<?php

use IceTea\IceDOM\Node;
use IceTea\IceDOM\RawNode;
use IceTea\IceDOM\SafeString;

describe('RawNode', function () {

    describe('Constructor and Initialization', function () {
        it('can be created with no children', function () {
            $node = new RawNode;
            expect($node->getChildren())->toBe([]);
        });

        it('can be created with initial children', function () {
            $children = ['child1', 'child2', 123];
            $node = new RawNode($children);
            expect($node->getChildren())->toBe($children);
        });

        it('extends Node class', function () {
            $node = new RawNode;
            expect($node)->toBeInstanceOf(Node::class);
        });

        it('inherits Node methods', function () {
            $node = new RawNode;

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

    describe('__toString() Method - Basic Functionality', function () {
        it('returns empty string for no children', function () {
            $node = new RawNode;
            expect((string) $node)->toBe('');
        });

        it('concatenates string children without spaces', function () {
            $node = new RawNode(['Hello', 'World']);
            expect((string) $node)->toBe('HelloWorld');
        });

        it('concatenates multiple strings directly', function () {
            $node = new RawNode(['<div>', 'content', '</div>']);
            expect((string) $node)->toBe('<div>content</div>');
        });

        it('handles single string child', function () {
            $node = new RawNode(['single string']);
            expect((string) $node)->toBe('single string');
        });

        it('handles empty string child', function () {
            $node = new RawNode(['']);
            expect((string) $node)->toBe('');
        });

        it('handles multiple empty strings', function () {
            $node = new RawNode(['', '', '']);
            expect((string) $node)->toBe('');
        });
    });

    describe('__toString() Method - No HTML Escaping', function () {
        it('does not escape HTML tags', function () {
            $node = new RawNode(['<div>content</div>']);
            expect((string) $node)->toBe('<div>content</div>');
        });

        it('does not escape script tags', function () {
            $node = new RawNode(['<script>alert("xss")</script>']);
            expect((string) $node)->toBe('<script>alert("xss")</script>');
        });

        it('does not escape quotes', function () {
            $node = new RawNode(['He said "hello" and \'goodbye\'']);
            expect((string) $node)->toBe('He said "hello" and \'goodbye\'');
        });

        it('does not escape special HTML entities', function () {
            $node = new RawNode(['&lt;&gt;&amp;&quot;&#39;']);
            expect((string) $node)->toBe('&lt;&gt;&amp;&quot;&#39;');
        });

        it('outputs potentially dangerous content as-is', function () {
            $dangerous = '<img src=x onerror="alert(1)">';
            $node = new RawNode([$dangerous]);
            expect((string) $node)->toBe($dangerous);
        });
    });

    describe('__toString() Method - Numeric Children', function () {
        it('handles integer children', function () {
            $node = new RawNode([42]);
            expect((string) $node)->toBe('42');
        });

        it('handles float children', function () {
            $node = new RawNode([3.14]);
            expect((string) $node)->toBe('3.14');
        });

        it('handles zero', function () {
            $node = new RawNode([0]);
            expect((string) $node)->toBe('0');
        });

        it('handles negative numbers', function () {
            $node = new RawNode([-42, -3.14]);
            expect((string) $node)->toBe('-42-3.14');
        });

        it('concatenates numbers without spaces', function () {
            $node = new RawNode([1, 2, 3]);
            expect((string) $node)->toBe('123');
        });

        it('mixes strings and numbers', function () {
            $node = new RawNode(['Number: ', 42, ' Value: ', 3.14]);
            expect((string) $node)->toBe('Number: 42 Value: 3.14');
        });
    });

    describe('__toString() Method - SafeString Children', function () {
        it('handles SafeString children', function () {
            $safeString = new SafeString('<div>safe content</div>');
            $node = new RawNode([$safeString]);
            expect((string) $node)->toBe('<div>safe content</div>');
        });

        it('handles multiple SafeString children', function () {
            $safe1 = new SafeString('<div>');
            $safe2 = new SafeString('content');
            $safe3 = new SafeString('</div>');
            $node = new RawNode([$safe1, $safe2, $safe3]);
            expect((string) $node)->toBe('<div>content</div>');
        });

        it('mixes SafeString with regular strings', function () {
            $safeString = new SafeString('<b>bold</b>');
            $node = new RawNode(['Text: ', $safeString, ' End']);
            expect((string) $node)->toBe('Text: <b>bold</b> End');
        });
    });

    describe('__toString() Method - Stringable Objects', function () {
        it('handles Stringable objects', function () {
            $stringable = new class implements \Stringable
            {
                public function __toString()
                {
                    return '<span>stringable</span>';
                }
            };

            $node = new RawNode([$stringable]);
            expect((string) $node)->toBe('<span>stringable</span>');
        });

        it('handles multiple Stringable objects', function () {
            $stringable1 = new class implements \Stringable
            {
                public function __toString()
                {
                    return 'First';
                }
            };
            $stringable2 = new class implements \Stringable
            {
                public function __toString()
                {
                    return 'Second';
                }
            };

            $node = new RawNode([$stringable1, $stringable2]);
            expect((string) $node)->toBe('FirstSecond');
        });
    });

    describe('__toString() Method - Does NOT Process Special Types', function () {
        it('does not evaluate closures', function () {
            $closure = function () {
                return 'This should not be called';
            };

            $node = new RawNode([$closure]);

            // Closures cannot be converted to string, will throw an Error
            expect(fn () => (string) $node)->toThrow(\Error::class);
        });

        it('does not process nested Node instances', function () {
            $childNode = new class extends Node
            {
                public function __toString()
                {
                    return 'Child node content';
                }
            };

            $node = new RawNode([$childNode]);

            // Will call __toString on the child node due to implicit conversion
            expect((string) $node)->toBe('Child node content');
        });

        it('does not process nested RawNode instances', function () {
            $innerNode = new RawNode(['<inner>content</inner>']);
            $outerNode = new RawNode(['<outer>', $innerNode, '</outer>']);

            // Will call __toString on the inner node due to implicit conversion
            expect((string) $outerNode)->toBe('<outer><inner>content</inner></outer>');
        });
    });

    describe('Null Handling', function () {
        it('handles null children', function () {
            // Null is simply ignored during string conversion by implode
            $node = new RawNode(['before', null, 'after']);
            expect((string) $node)->toBe('beforeafter');
        });

        it('handles all null children', function () {
            $node = new RawNode([null, null, null]);
            expect((string) $node)->toBe('');
        });

        it('handles mixed null and string children', function () {
            $node = new RawNode([null, 'text', null, 'more', null]);
            expect((string) $node)->toBe('textmore');
        });
    });

    describe('Mixed Content Types', function () {
        it('handles mixed strings, numbers, and SafeStrings', function () {
            $safeString = new SafeString('<b>bold</b>');
            $node = new RawNode([
                '<div>',
                'Text: ',
                42,
                ' - ',
                $safeString,
                '</div>',
            ]);

            expect((string) $node)->toBe('<div>Text: 42 - <b>bold</b></div>');
        });

        it('handles complex mixed content', function () {
            $safe = new SafeString('<span>safe</span>');
            $stringable = new class implements \Stringable
            {
                public function __toString()
                {
                    return '[stringable]';
                }
            };

            $node = new RawNode([
                'Start ',
                123,
                ' - ',
                $safe,
                ' - ',
                $stringable,
                ' End',
            ]);

            expect((string) $node)->toBe('Start 123 - <span>safe</span> - [stringable] End');
        });
    });

    describe('Inherited Node Functionality', function () {
        it('can add children using appendChild', function () {
            $node = new RawNode;
            $node->appendChild('<div>');
            $node->appendChild('content');
            $node->appendChild('</div>');

            expect((string) $node)->toBe('<div>content</div>');
        });

        it('can add multiple children using appendChildren', function () {
            $node = new RawNode;
            $node->appendChildren(['<ul>', '<li>Item</li>', '</ul>']);

            expect((string) $node)->toBe('<ul><li>Item</li></ul>');
        });

        it('can add children using invoke syntax', function () {
            $node = new RawNode;
            $node('<div>', 'Hello', '</div>');

            expect((string) $node)->toBe('<div>Hello</div>');
        });

        it('can clear children', function () {
            $node = new RawNode(['<div>content</div>']);
            $node->clearChildren();

            expect((string) $node)->toBe('');
        });

        it('maintains parent-child relationships', function () {
            $parent = new RawNode;
            $child = new RawNode(['child content']);

            $parent->appendChild($child);

            expect($child->getParent())->toBe($parent);
            expect((string) $parent)->toBe('child content');
        });

        it('can use use() method for configuration', function () {
            $node = new RawNode;

            $node->use(function ($n) {
                $n->appendChild('<configured>');
                $n->appendChild('content');
                $n->appendChild('</configured>');
            });

            expect((string) $node)->toBe('<configured>content</configured>');
        });

        it('can use childrenUse() method', function () {
            $parent = new RawNode;
            $child1 = new RawNode(['Child1']);
            $child2 = new RawNode(['Child2']);

            $parent->appendChildren([$child1, $child2]);

            $callCount = 0;
            $parent->childrenUse(function ($child) use (&$callCount) {
                $callCount++;
                $child->appendChild('-modified');
            });

            expect($callCount)->toBe(2);
            expect((string) $parent)->toBe('Child1-modifiedChild2-modified');
        });
    });

    describe('Edge Cases', function () {
        it('handles empty array of children', function () {
            $node = new RawNode([]);
            expect((string) $node)->toBe('');
        });

        it('handles very long strings', function () {
            $longString = str_repeat('A', 10000);
            $node = new RawNode([$longString]);

            expect((string) $node)->toBe($longString);
            expect(strlen((string) $node))->toBe(10000);
        });

        it('handles binary content', function () {
            $binaryContent = "\x00\x01\x02\xFF";
            $node = new RawNode([$binaryContent]);

            expect((string) $node)->toBe($binaryContent);
        });

        it('handles UTF-8 content', function () {
            $utf8Content = '🎉 Hello 世界 🌍';
            $node = new RawNode([$utf8Content]);

            expect((string) $node)->toBe($utf8Content);
        });

        it('handles whitespace-only content', function () {
            $node = new RawNode([' ', "\t", "\n", "\r"]);
            expect((string) $node)->toBe(" \t\n\r");
        });

        it('handles deeply nested RawNode structures', function () {
            $level3 = new RawNode(['L3']);
            $level2 = new RawNode(['L2-', $level3]);
            $level1 = new RawNode(['L1-', $level2]);

            expect((string) $level1)->toBe('L1-L2-L3');
        });

        it('maintains insertion order', function () {
            $node = new RawNode;

            $node->appendChild('1');
            $node->appendChild('2');
            $node->appendChildren(['3', '4']);

            expect((string) $node)->toBe('1234');
        });

        it('handles boolean values as children', function () {
            // PHP will convert true to '1' and false to ''
            $node = new RawNode([true, false, true]);
            expect((string) $node)->toBe('11');
        });
    });

    describe('Comparison with Base Node Class', function () {
        it('does not add spaces between children unlike base Node', function () {
            $rawNode = new RawNode(['Hello', 'World']);
            $baseNode = new class (['Hello', 'World']) extends Node
            {
                public function __toString()
                {
                    return $this->childrenToString();
                }
            };

            expect((string) $rawNode)->toBe('HelloWorld');
            expect((string) $baseNode)->toBe('HelloWorld'); // Base node uses childrenToString with no separator
        });

        it('does not escape HTML unlike base Node', function () {
            $rawNode = new RawNode(['<script>alert("xss")</script>']);
            $baseNode = new class (['<script>alert("xss")</script>']) extends Node
            {
                public function __toString()
                {
                    return $this->childrenToString();
                }
            };

            expect((string) $rawNode)->toBe('<script>alert("xss")</script>');
            expect((string) $baseNode)->toBe('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
        });
    });

    describe('Real-World Usage Scenarios', function () {
        it('can build HTML structure from fragments', function () {
            $node = new RawNode([
                '<html>',
                '<head><title>Test</title></head>',
                '<body>',
                '<h1>Hello</h1>',
                '</body>',
                '</html>',
            ]);

            $expected = '<html><head><title>Test</title></head><body><h1>Hello</h1></body></html>';
            expect((string) $node)->toBe($expected);
        });

        it('can build complex nested structures', function () {
            $header = new RawNode(['<header>', '<h1>Title</h1>', '</header>']);
            $content = new RawNode(['<main>', '<p>Content</p>', '</main>']);
            $footer = new RawNode(['<footer>', '<p>Footer</p>', '</footer>']);

            $page = new RawNode([$header, $content, $footer]);

            $expected = '<header><h1>Title</h1></header><main><p>Content</p></main><footer><p>Footer</p></footer>';
            expect((string) $page)->toBe($expected);
        });

        it('can mix raw HTML with dynamic content', function () {
            $title = 'Dynamic Title';
            $count = 42;

            $node = new RawNode([
                '<div class="container">',
                '<h1>',
                $title,
                '</h1>',
                '<p>Count: ',
                $count,
                '</p>',
                '</div>',
            ]);

            $expected = '<div class="container"><h1>Dynamic Title</h1><p>Count: 42</p></div>';
            expect((string) $node)->toBe($expected);
        });

        it('can be used for pre-escaped or trusted content', function () {
            // Simulating content that's already been sanitized
            $trustedHtml = '<div class="safe">This is <b>safe</b> HTML</div>';
            $node = new RawNode([$trustedHtml]);

            expect((string) $node)->toBe($trustedHtml);
        });
    });

    describe('Performance Considerations', function () {
        it('handles many children efficiently', function () {
            $children = [];
            for ($i = 0; $i < 1000; $i++) {
                $children[] = (string) $i;
            }

            $node = new RawNode($children);
            $output = (string) $node;

            expect(strlen($output))->toBeGreaterThan(0);
            expect($output)->toStartWith('0');
            expect($output)->toEndWith('999');
        });

        it('concatenates without intermediate conversions', function () {
            // RawNode uses implode directly which is efficient
            $start = microtime(true);
            $node = new RawNode(array_fill(0, 1000, 'content'));
            $output = (string) $node;
            $duration = microtime(true) - $start;

            expect($duration)->toBeLessThan(0.1); // Should be very fast
            expect(strlen($output))->toBe(7000); // 'content' = 7 chars * 1000
        });
    });

    describe('Integration with Other Components', function () {
        it('works with ArrayMap children', function () {
            $arrayMap = new \IceTea\IceDOM\ArrayMap(
                ['a', 'b', 'c'],
                function ($item) {
                    return $item;
                }
            );

            $node = new RawNode([$arrayMap]);
            expect((string) $node)->toBe('abc');
        });

        it('works with nested different node types', function () {
            $htmlNode = new \IceTea\IceDOM\HtmlNode(
                ['content'],
                'div',
                ['class' => 'test']
            );
            $rawNode = new RawNode(['<wrapper>', $htmlNode, '</wrapper>']);

            $output = (string) $rawNode;
            expect($output)->toContain('<wrapper>');
            expect($output)->toContain('<div');
            expect($output)->toContain('class="test"');
            expect($output)->toContain('content');
            expect($output)->toContain('</div>');
            expect($output)->toContain('</wrapper>');
        });
    });
});

