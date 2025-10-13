<?php

use IceTea\IceDOM\EchoNode;
use IceTea\IceDOM\SafeString;

describe('EchoNode', function () {

    describe('Constructor and Initialization', function () {
        it('can be created with no children', function () {
            $node = new EchoNode;
            expect($node->getChildren())->toBe([]);
        });

        it('can be created with initial children', function () {
            $children = ['child1', 'child2', 123];
            $node = new EchoNode($children);
            expect($node->getChildren())->toBe($children);
        });

        it('extends Node class', function () {
            $node = new EchoNode;
            expect($node)->toBeInstanceOf(\IceTea\IceDOM\Node::class);
        });

        it('inherits Node methods', function () {
            $node = new EchoNode;

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

    describe('__toString() Method', function () {
        it('returns empty string for no children', function () {
            $node = new EchoNode;
            expect((string) $node)->toBe('');
        });

        it('captures echo output from closure children', function () {
            $node = new EchoNode([
                function () {
                    echo 'Hello World';
                },
            ]);

            expect((string) $node)->toBe('Hello World');
        });

        it('captures print output from closure children', function () {
            $node = new EchoNode([
                function () {
                    echo 'Printed content';
                },
            ]);

            expect((string) $node)->toBe('Printed content');
        });

        it('captures both echo and return values from closures', function () {
            $node = new EchoNode([
                function () {
                    echo '<div>Echoed</div>';

                    return ' - returned';
                },
            ]);

            expect((string) $node)->toBe('<div>Echoed</div> - returned');
        });

        it('captures multiple closure outputs sequentially', function () {
            $node = new EchoNode([
                function () {
                    echo 'First ';
                },
                function () {
                    echo 'Second ';
                },
                function () {
                    echo 'Third';
                },
            ]);

            expect((string) $node)->toBe('First Second Third');
        });

        it('handles closures with no output', function () {
            $node = new EchoNode([
                function () {
                    // No output, just processing
                    $x = 1 + 1;
                },
            ]);

            expect((string) $node)->toBe('');
        });

        it('handles closures that return null', function () {
            $node = new EchoNode([
                function () {
                    echo 'Output ';

                    return null;
                },
            ]);

            expect((string) $node)->toBe('Output ');
        });

        it('handles closures that return different types', function () {
            $node = new EchoNode([
                function () {
                    echo 'Text ';

                    return 123;
                },
                function () {
                    echo ' - Number ';

                    return 45.67;
                },
            ]);

            expect((string) $node)->toBe('Text 123 - Number 45.67');
        });

        it('handles string children directly', function () {
            $node = new EchoNode(['raw string content']);
            expect((string) $node)->toBe('raw string content');
        });

        it('handles numeric children directly', function () {
            $node = new EchoNode([42, 3.14]);
            expect((string) $node)->toBe('423.14');
        });

        it('handles SafeString children without modification', function () {
            $safeString = new SafeString('<div>Safe HTML</div>');
            $node = new EchoNode([$safeString]);
            expect((string) $node)->toBe('<div>Safe HTML</div>');
        });

        it('handles Stringable objects', function () {
            $stringable = new class implements \Stringable
            {
                public function __toString()
                {
                    return '<span>Stringable content</span>';
                }
            };

            $node = new EchoNode([$stringable]);
            expect((string) $node)->toBe('<span>Stringable content</span>');
        });

        it('does not escape HTML in captured output', function () {
            $node = new EchoNode([
                function () {
                    echo '<script>alert("xss")</script>';
                },
            ]);

            expect((string) $node)->toBe('<script>alert("xss")</script>');
        });

        it('does not escape HTML in string children', function () {
            $node = new EchoNode(['<div>HTML content</div>']);
            expect((string) $node)->toBe('<div>HTML content</div>');
        });

        it('handles mixed content types', function () {
            $safeString = new SafeString('<b>bold</b>');
            $stringable = new class implements \Stringable
            {
                public function __toString()
                {
                    return '<i>italic</i>';
                }
            };

            $node = new EchoNode([
                'text',
                function () {
                    echo ' - closure - ';
                },
                $safeString,
                ' - ',
                123,
                ' - ',
                $stringable,
            ]);

            expect((string) $node)->toBe('text - closure - <b>bold</b> - 123 - <i>italic</i>');
        });

        it('handles null children gracefully', function () {
            $node = new EchoNode([
                'before',
                null,
                function () {
                    echo ' after';
                },
            ]);

            expect((string) $node)->toBe('before after');
        });

        it('handles empty array of children', function () {
            $node = new EchoNode([]);
            expect((string) $node)->toBe('');
        });
    });

    describe('Inherited Node Functionality', function () {
        it('can add children using appendChild', function () {
            $node = new EchoNode;
            $node->appendChild(function () {
                echo 'Appended child';
            });

            expect((string) $node)->toBe('Appended child');
        });

        it('can add multiple children using appendChildren', function () {
            $node = new EchoNode;
            $node->appendChildren([
                function () {
                    echo 'First';
                },
                function () {
                    echo 'Second';
                },
                function () {
                    echo 'Third';
                },
            ]);

            expect((string) $node)->toBe('FirstSecondThird');
        });

        it('can add children using invoke syntax', function () {
            $node = new EchoNode;
            $node(
                function () {
                    echo 'Invoked1';
                },
                function () {
                    echo 'Invoked2';
                }
            );

            expect((string) $node)->toBe('Invoked1Invoked2');
        });

        it('can clear children', function () {
            $node = new EchoNode([
                function () {
                    echo 'Will be cleared';
                },
            ]);

            $node->clearChildren();
            expect((string) $node)->toBe('');
        });

        it('maintains parent-child relationships', function () {
            $parent = new EchoNode;
            $child = new EchoNode([
                function () {
                    echo 'Child content';
                },
            ]);

            $parent->appendChild($child);

            expect($child->getParent())->toBe($parent);
            expect((string) $parent)->toBe('Child content');
        });

        it('can use use() method for configuration', function () {
            $node = new EchoNode;

            $node->use(function ($n) {
                $n->appendChild(function () {
                    echo 'Configured via use()';
                });
            });

            expect((string) $node)->toBe('Configured via use()');
        });

        it('can use childrenUse() method', function () {
            $parent = new EchoNode;
            $child1 = new EchoNode([
                function () {
                    echo 'Child1';
                },
            ]);
            $child2 = new EchoNode([
                function () {
                    echo 'Child2';
                },
            ]);

            $parent->appendChildren([$child1, $child2]);

            $callCount = 0;
            $parent->childrenUse(function ($child) use (&$callCount) {
                $callCount++;
                $child->appendChild(function () {
                    echo ' - modified';
                });
            });

            expect($callCount)->toBe(2);
            expect((string) $parent)->toBe('Child1 - modifiedChild2 - modified');
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles closures that throw exceptions', function () {
            $node = new EchoNode([
                function () {
                    echo 'Before exception';
                    throw new \Exception('Test exception');
                },
            ]);

            expect(fn () => (string) $node)->toThrow(\Exception::class);
            ob_get_clean();
        });

        it('handles closures that modify global state', function () {
            $node = new EchoNode([
                function () {
                    global $test_global;
                    $test_global = 'modified';
                    echo 'Global modified';
                },
            ]);

            $global_backup = $GLOBALS['test_global'] ?? null;

            try {
                expect((string) $node)->toBe('Global modified');
                expect($GLOBALS['test_global'] ?? null)->toBe('modified');
            } finally {
                if ($global_backup !== null) {
                    $GLOBALS['test_global'] = $global_backup;
                } else {
                    unset($GLOBALS['test_global']);
                }
            }
        });

        it('handles very large output', function () {
            $largeContent = str_repeat('A', 10000);
            $node = new EchoNode([
                function () use ($largeContent) {
                    echo $largeContent;
                },
            ]);

            expect((string) $node)->toBe($largeContent);
            expect(strlen((string) $node))->toBe(10000);
        });

        it('handles nested EchoNodes', function () {
            $innerNode = new EchoNode([
                function () {
                    echo 'Inner ';
                },
                function () {
                    echo 'content';
                },
            ]);

            $outerNode = new EchoNode([
                function () {
                    echo 'Outer ';
                },
                $innerNode,
                function () {
                    echo ' end';
                },
            ]);

            expect((string) $outerNode)->toBe('Outer Inner content end');
        });

        it('handles deeply nested structures', function () {
            $level3 = new EchoNode([function () {
                echo 'L3';
            }]);
            $level2 = new EchoNode([function () {
                echo 'L2 ';
            }, $level3]);
            $level1 = new EchoNode([function () {
                echo 'L1 ';
            }, $level2]);

            expect((string) $level1)->toBe('L1 L2 L3');
        });

        it('maintains insertion order', function () {
            $node = new EchoNode;

            $node->appendChild(function () {
                echo '1';
            });
            $node->appendChild(function () {
                echo '2';
            });
            $node->appendChildren([
                function () {
                    echo '3';
                },
                function () {
                    echo '4';
                },
            ]);

            expect((string) $node)->toBe('1234');
        });

        it('handles binary content', function () {
            $binaryContent = "\x00\x01\x02\xFF";
            $node = new EchoNode([
                function () use ($binaryContent) {
                    echo $binaryContent;
                },
            ]);

            expect((string) $node)->toBe($binaryContent);
        });

        it('handles UTF-8 content', function () {
            $utf8Content = '🎉 Hello 世界 🌍';
            $node = new EchoNode([
                function () use ($utf8Content) {
                    echo $utf8Content;
                },
            ]);

            expect((string) $node)->toBe($utf8Content);
        });

        it('handles multiple output types in single closure', function () {
            $node = new EchoNode([
                function () {
                    echo 'Echo ';
                    echo 'Print ';
                    $return = 'Return';

                    return $return;
                },
            ]);

            expect((string) $node)->toBe('Echo Print Return');
        });
    });

    describe('Integration with Other Components', function () {
        it('works with ArrayMapNode children', function () {
            $arrayMapNode = new \IceTea\IceDOM\ArrayMapNode(
                [],
                ['a', 'b', 'c'],
                function ($item) {
                    echo $item.'_mapped ';
                }
            );

            $node = new EchoNode([$arrayMapNode]);
            expect((string) $node)->toBe('a_mapped b_mapped c_mapped ');
        });

        it('works with HtmlNode children', function () {
            $htmlNode = new \IceTea\IceDOM\HtmlNode([
                function () {
                    echo 'Nested content';
                },
            ], 'div', ['class' => 'container']);

            $node = new EchoNode([$htmlNode]);
            $output = (string) $node;

            expect($output)->toContain('<div');
            expect($output)->toContain('class="container"');
            expect($output)->toContain('Nested content');
            expect($output)->toContain('</div>');
        });

        it('works with RawNode children', function () {
            $rawNode = new \IceTea\IceDOM\RawNode(['<raw>content</raw>']);
            $node = new EchoNode([$rawNode]);

            expect((string) $node)->toBe('<raw>content</raw>');
        });
    });

    describe('Performance Considerations', function () {
        it('handles many small outputs efficiently', function () {
            $children = [];
            for ($i = 0; $i < 1000; $i++) {
                $children[] = function () use ($i) {
                    echo $i.' ';
                };
            }

            $node = new EchoNode($children);
            $output = (string) $node;

            expect(strlen($output))->toBeGreaterThan(0);
            expect($output)->toContain('0 ');
            expect($output)->toContain('999 ');
        });

        it('cleans up output buffer properly', function () {
            $node = new EchoNode([
                function () {
                    echo 'Test output';
                },
            ]);

            // Get current output buffer level before
            $initialLevel = ob_get_level();

            $output = (string) $node;

            // Output buffer level should be the same after conversion
            expect(ob_get_level())->toBe($initialLevel);
            expect($output)->toBe('Test output');
        });
    });
});
