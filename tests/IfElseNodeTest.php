<?php

use IceTea\IceDOM\IfElseNode;
use IceTea\IceDOM\Node;
use IceTea\IceDOM\SafeString;
use IceTea\IceDOM\SlotNode;

describe('IfElseNode', function () {

    describe('Constructor and Initialization', function () {
        it('can be created with no parameters', function () {
            $node = new IfElseNode;
            expect($node->getChildren())->toBe([]);
        });

        it('can be created with initial children', function () {
            $children = ['child1', 'child2'];
            $node = new IfElseNode($children);
            // Children should be in a 2D structure: [0 => ['child1', 'child2']]
            expect($node->getChildren())->toBe([0 => $children]);
        });

        it('can be created with children and else children', function () {
            $children = ['if content'];
            $elseChildren = ['else content'];
            $node = new IfElseNode($children, $elseChildren);

            expect($node->getChildren())->toHaveCount(1);
        });

        it('can be created with all parameters', function () {
            $children = ['if content'];
            $elseChildren = ['else content'];
            $condition = true;
            $node = new IfElseNode($children, $elseChildren, $condition);

            expect($node->getChildren())->toHaveCount(1);
        });

        it('extends Node class', function () {
            $node = new IfElseNode;
            expect($node)->toBeInstanceOf(Node::class);
        });
    });

    describe('Basic If-Else Functionality', function () {
        it('renders if block when condition is true', function () {
            $node = new IfElseNode(['if content'], ['else content'], true);

            expect((string) $node)->toBe('if content');
        });

        it('renders else block when condition is false', function () {
            $node = new IfElseNode(['if content'], ['else content'], false);

            expect((string) $node)->toBe('else content');
        });

        it('renders if block when condition evaluates to truthy', function () {
            $node = new IfElseNode(['if content'], ['else content'], 'non-empty string');

            expect((string) $node)->toBe('if content');
        });

        it('renders else block when condition evaluates to falsy', function () {
            $node = new IfElseNode(['if content'], ['else content'], 0);

            expect((string) $node)->toBe('else content');
        });

        it('renders if block when condition is closure returning true', function () {
            $condition = function () {
                return true;
            };
            $node = new IfElseNode(['if content'], ['else content'], $condition);

            expect((string) $node)->toBe('if content');
        });

        it('renders else block when condition is closure returning false', function () {
            $condition = function () {
                return false;
            };
            $node = new IfElseNode(['if content'], ['else content'], $condition);

            expect((string) $node)->toBe('else content');
        });

        it('handles empty if block', function () {
            $node = new IfElseNode([], ['else content'], true);

            expect((string) $node)->toBe('');
        });

        it('handles empty else block', function () {
            $node = new IfElseNode(['if content'], [], false);

            expect((string) $node)->toBe('');
        });
    });

    describe('ElseIf Functionality', function () {
        it('renders first condition when true', function () {
            $node = new IfElseNode(['first'], [], true);
            $node->elseif(true)('second');
            $node->else('else');

            expect((string) $node)->toBe('first');
        });

        it('renders second condition when first is false and second is true', function () {
            $node = new IfElseNode(['first'], [], false);
            $node->elseif(true)('second');
            $node->else('else');

            expect((string) $node)->toBe('second');
        });

        it('renders else when all conditions are false', function () {
            $node = new IfElseNode(['first'], [], false);
            $node->elseif(false)('second');
            $node->else('else content');

            expect((string) $node)->toBe('else content');
        });

        it('supports multiple elseif blocks', function () {
            $node = new IfElseNode(['first'], [], false);
            $node->elseif(false)('second');
            $node->elseif(true)('third');
            $node->elseif(false)('fourth');
            $node->else('else');

            expect((string) $node)->toBe('third');
        });

        it('evaluates conditions in order and stops at first true', function () {
            $callCount = 0;

            $condition1 = function () use (&$callCount) {
                $callCount++;

                return false;
            };

            $condition2 = function () use (&$callCount) {
                $callCount++;

                return true;
            };

            $condition3 = function () use (&$callCount) {
                $callCount++;

                return true;
            };

            $node = new IfElseNode(['first'], [], $condition1);
            $node->elseif($condition2)('second');
            $node->elseif($condition3)('third');
            $node->else('else');

            $result = (string) $node;

            expect($callCount)->toBe(2); // Only first two conditions evaluated
            expect($result)->toBe('second');
        });

        it('returns self for method chaining', function () {
            $node = new IfElseNode;
            $result = $node->elseif(true);

            expect($result)->toBe($node);
        });
    });

    describe('__invoke() Method for Adding Children', function () {
        it('adds children to current condition block using invoke', function () {
            $node = new IfElseNode([], [], true);
            $node('content1', 'content2');

            expect((string) $node)->toBe('content1content2');
        });

        it('adds children to correct elseif block', function () {
            $node = new IfElseNode([], [], false);
            $node('first block');

            $node->elseif(true);
            $node('second block');

            expect((string) $node)->toBe('second block');
        });

        it('throws exception when adding children after else children', function () {
            $node = new IfElseNode(['initial'], ['else content'], true);

            expect(fn () => $node('new content'))->toThrow(Exception::class);
        });

        it('exception message is clear', function () {
            $node = new IfElseNode(['initial'], ['else content'], true);

            try {
                $node('new content');
                expect(true)->toBeFalse(); // Should not reach here
            } catch (Exception $e) {
                expect($e->getMessage())->toBe("Please don't add children after else children!");
            }
        });

        it('returns self for method chaining', function () {
            $node = new IfElseNode([], [], true);
            $result = $node('content');

            expect($result)->toBe($node);
        });

        it('can be called multiple times on same condition', function () {
            $node = new IfElseNode([], [], true);
            $node('content1');
            $node('content2');
            $node('content3');

            expect((string) $node)->toBe('content1content2content3');
        });
    });

    describe('else() Method', function () {
        it('adds children to else block', function () {
            $node = new IfElseNode([], [], false);
            $node->else('else1', 'else2');

            expect((string) $node)->toBe('else1else2');
        });

        it('returns self for method chaining', function () {
            $node = new IfElseNode;
            $result = $node->else('content');

            expect($result)->toBe($node);
        });

        it('can be called multiple times', function () {
            $node = new IfElseNode([], [], false);
            $node->else('else1');
            $node->else('else2');

            expect((string) $node)->toBe('else1else2');
        });

        it('sets parent for Node children', function () {
            $childNode = new SlotNode(['child']);
            $node = new IfElseNode([], [], false);
            $node->else($childNode);

            expect($childNode->getParent())->toBe($node);
        });
    });

    describe('Parent-Child Relationships', function () {
        it('sets parent for Node children in if block', function () {
            $childNode = new SlotNode(['child']);
            $node = new IfElseNode([$childNode], [], true);

            expect($childNode->getParent())->toBe($node);
        });

        it('sets parent for Node children in else block', function () {
            $childNode = new SlotNode(['child']);
            $node = new IfElseNode([], [$childNode], false);

            expect($childNode->getParent())->toBe($node);
        });

        it('sets parent for Node children added via invoke', function () {
            $childNode = new SlotNode(['child']);
            $node = new IfElseNode([], [], true);
            $node($childNode);

            expect($childNode->getParent())->toBe($node);
        });

        it('parent is null by default for child nodes before adding', function () {
            $childNode = new SlotNode(['child']);
            expect($childNode->getParent())->toBeNull();
        });
    });

    describe('Condition Evaluation with Closures', function () {
        it('evaluates closure with node as parameter', function () {
            $receivedNode = null;
            $condition = function ($node) use (&$receivedNode) {
                $receivedNode = $node;

                return true;
            };

            $node = new IfElseNode(['content'], [], $condition);
            (string) $node;

            expect($receivedNode)->toBe($node);
        });

        it('uses closure return value for condition', function () {
            $value = 42;
            $condition = function () use ($value) {
                return $value > 40;
            };

            $node = new IfElseNode(['if'], ['else'], $condition);

            expect((string) $node)->toBe('if');
        });

        it('handles complex condition logic', function () {
            $userData = ['age' => 25, 'verified' => true];
            $condition = function () use ($userData) {
                return $userData['age'] >= 18 && $userData['verified'];
            };

            $node = new IfElseNode(['adult verified'], ['not allowed'], $condition);

            expect((string) $node)->toBe('adult verified');
        });
    });

    describe('Fluent Interface and Method Chaining', function () {
        it('supports full chaining pattern', function () {
            $node = (new IfElseNode([], [], false))
                ->elseif(false)('second')
                ->elseif(true)('third')
                ->else('final');

            expect((string) $node)->toBe('third');
        });

        it('can build complex conditions fluently', function () {
            $node = new IfElseNode;
            $node->pushCondition(false);
            $node('first content');
            $node->elseif(true);
            $node('second content');
            $node->else('else content');

            expect((string) $node)->toBe('second content');
        });
    });

    describe('Content Rendering and Escaping', function () {
        it('escapes HTML in if block', function () {
            $node = new IfElseNode(['<script>alert("xss")</script>'], [], true);

            expect((string) $node)->toBe('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
        });

        it('escapes HTML in else block', function () {
            $node = new IfElseNode([], ['<script>alert("xss")</script>'], false);

            expect((string) $node)->toBe('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
        });

        it('handles SafeString without escaping in if block', function () {
            $safe = new SafeString('<div>Safe HTML</div>');
            $node = new IfElseNode([$safe], [], true);

            expect((string) $node)->toBe('<div>Safe HTML</div>');
        });

        it('handles SafeString without escaping in else block', function () {
            $safe = new SafeString('<div>Safe HTML</div>');
            $node = new IfElseNode([], [$safe], false);

            expect((string) $node)->toBe('<div>Safe HTML</div>');
        });

        it('handles numeric content', function () {
            $node = new IfElseNode([42, 3.14], [], true);

            expect((string) $node)->toBe('423.14');
        });

        it('handles mixed content types', function () {
            $safe = new SafeString('<b>bold</b>');
            $node = new IfElseNode(['text', 42, $safe], [], true);

            expect((string) $node)->toBe('text42<b>bold</b>');
        });

        it('handles nested nodes', function () {
            $inner = new SlotNode(['inner']);
            $node = new IfElseNode(['outer ', $inner, ' end'], [], true);

            expect((string) $node)->toBe('outer inner end');
        });

        it('handles closures in content', function () {
            $closure = function () {
                return 'from closure';
            };
            $node = new IfElseNode([$closure], [], true);

            expect((string) $node)->toBe('from closure');
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles null condition', function () {
            $node = new IfElseNode(['if'], ['else'], null);

            expect((string) $node)->toBe('else');
        });

        it('handles empty string condition', function () {
            $node = new IfElseNode(['if'], ['else'], '');

            expect((string) $node)->toBe('else');
        });

        it('handles zero as condition', function () {
            $node = new IfElseNode(['if'], ['else'], 0);

            expect((string) $node)->toBe('else');
        });

        it('handles array as truthy condition', function () {
            $node = new IfElseNode(['if'], ['else'], [1, 2, 3]);

            expect((string) $node)->toBe('if');
        });

        it('handles empty array as falsy condition', function () {
            $node = new IfElseNode(['if'], ['else'], []);

            expect((string) $node)->toBe('else');
        });

        it('handles closure returning null', function () {
            $condition = function () {
                return null;
            };
            $node = new IfElseNode(['if'], ['else'], $condition);

            expect((string) $node)->toBe('else');
        });

        it('handles deeply nested IfElseNodes', function () {
            $level3 = new IfElseNode(['L3-if'], ['L3-else'], true);
            $level2 = new IfElseNode(['L2 ', $level3], ['L2-else'], true);
            $level1 = new IfElseNode(['L1 ', $level2], ['L1-else'], true);

            expect((string) $level1)->toBe('L1 L2 L3-if');
        });

        it('handles many elseif blocks', function () {
            $node = new IfElseNode(['0'], [], false);

            for ($i = 1; $i < 10; $i++) {
                $node->elseif(false)((string) $i);
            }
            $node->elseif(true)('winner');
            $node->else('else');

            expect((string) $node)->toBe('winner');
        });

        it('handles null in children arrays', function () {
            $node = new IfElseNode(['before', null, 'after'], [], true);

            expect((string) $node)->toBe('beforeafter');
        });

        it('handles UTF-8 content', function () {
            $content = '🎉 Hello 世界 🌍';
            $node = new IfElseNode([$content], [], true);

            expect((string) $node)->toBe($content);
        });
    });

    describe('Integration with Other Components', function () {
        it('works with SlotNode in if block', function () {
            $slot = new SlotNode(['slot content']);
            $node = new IfElseNode([$slot], [], true);

            expect((string) $node)->toBe('slot content');
        });

        it('works with SlotNode with function', function () {
            $slot = new SlotNode([], function () {
                return 'dynamic content';
            });
            $node = new IfElseNode([$slot], [], true);

            expect((string) $node)->toBe('dynamic content');
        });

        it('works with HtmlNode in conditions', function () {
            $html = new \IceTea\IceDOM\HtmlNode(['content'], 'div', ['class' => 'test']);
            $node = new IfElseNode([$html], [], true);
            $output = (string) $node;

            expect($output)->toContain('<div');
            expect($output)->toContain('class="test"');
            expect($output)->toContain('content');
            expect($output)->toContain('</div>');
        });

        it('works with RawNode', function () {
            $raw = new \IceTea\IceDOM\RawNode(['<raw>html</raw>']);
            $node = new IfElseNode([$raw], [], true);

            expect((string) $node)->toBe('<raw>html</raw>');
        });

        it('works with EchoNode', function () {
            $echo = new \IceTea\IceDOM\EchoNode([
                function () {
                    echo 'echoed';
                },
            ]);
            $node = new IfElseNode([$echo], [], true);

            expect((string) $node)->toBe('echoed');
        });

        it('works with ArrayMap', function () {
            $arrayMap = new \IceTea\IceDOM\ArrayMap(
                ['a', 'b', 'c'],
                function ($item) {
                    return $item.' ';
                }
            );
            $node = new IfElseNode([$arrayMap], [], true);

            expect((string) $node)->toBe('a b c ');
        });
    });

    describe('Real-world Use Cases', function () {
        it('implements user authentication check', function () {
            $isLoggedIn = true;
            $user = ['name' => 'John'];

            $node = new IfElseNode([], [], $isLoggedIn);
            $node("Welcome, {$user['name']}!");
            $node->else('Please log in.');

            expect((string) $node)->toBe('Welcome, John!');
        });

        it('implements user role based rendering', function () {
            $userRole = 'admin';

            $node = new IfElseNode([], [], $userRole === 'admin');
            $node('Admin Panel');
            $node->elseif($userRole === 'moderator')('Moderator Panel');
            $node->else('User Panel');

            expect((string) $node)->toBe('Admin Panel');
        });

        it('implements feature flag pattern', function () {
            $features = ['beta_feature' => true, 'new_ui' => false];

            $node = new IfElseNode([], [], $features['beta_feature']);
            $node('Beta Feature Enabled');
            $node->else('Standard Feature');

            expect((string) $node)->toBe('Beta Feature Enabled');
        });

        it('implements content based on data validation', function () {
            $email = 'test@example.com';
            $isValidEmail = filter_var($email, FILTER_VALIDATE_EMAIL);

            $node = new IfElseNode([], [], $isValidEmail);
            $node('Email: ', $email);
            $node->else('Invalid email address');

            expect((string) $node)->toContain('Email: test@example.com');
        });

        it('implements age-based content rendering', function () {
            $age = 25;

            $node = new IfElseNode([], [], $age >= 18);
            $node('Adult Content');
            $node->else('Minor Content');

            expect((string) $node)->toBe('Adult Content');
        });

        it('implements status-based UI rendering', function () {
            $status = 'pending';

            $node = new IfElseNode([], [], $status === 'completed');
            $node('✓ Completed');
            $node->elseif($status === 'pending')('⏳ Pending');
            $node->elseif($status === 'failed')('✗ Failed');
            $node->else('Unknown Status');

            expect((string) $node)->toBe('⏳ Pending');
        });

        it('implements permission-based rendering', function () {
            $permissions = ['create', 'read', 'update'];
            $canDelete = in_array('delete', $permissions);

            $node = new IfElseNode([], [], $canDelete);
            $node(new SafeString('<button>Delete</button>'));
            $node->else(new SafeString('<span class="disabled">Delete</span>'));

            expect((string) $node)->toBe('<span class="disabled">Delete</span>');
        });

        it('implements multi-condition business logic', function () {
            $order = ['total' => 150, 'isPremium' => true, 'country' => 'US'];

            $freeShipping = $order['isPremium'] || ($order['total'] > 100 && $order['country'] === 'US');

            $node = new IfElseNode([], [], $freeShipping);
            $node('FREE SHIPPING');
            $node->else('Shipping: $10');

            expect((string) $node)->toBe('FREE SHIPPING');
        });
    });

    describe('Lazy Evaluation', function () {
        it('only evaluates condition once when rendering', function () {
            $evalCount = 0;
            $condition = function () use (&$evalCount) {
                $evalCount++;

                return true;
            };

            $node = new IfElseNode(['content'], [], $condition);
            (string) $node;

            expect($evalCount)->toBe(1);
        });

        it('re-evaluates condition on each render', function () {
            $evalCount = 0;
            $condition = function () use (&$evalCount) {
                $evalCount++;

                return true;
            };

            $node = new IfElseNode(['content'], [], $condition);
            (string) $node;
            (string) $node;

            expect($evalCount)->toBe(2);
        });

        it('does not evaluate later conditions if earlier one matches', function () {
            $eval1 = false;
            $eval2 = false;
            $eval3 = false;

            $cond1 = function () use (&$eval1) {
                $eval1 = true;

                return false;
            };

            $cond2 = function () use (&$eval2) {
                $eval2 = true;

                return true;
            };

            $cond3 = function () use (&$eval3) {
                $eval3 = true;

                return true;
            };

            $node = new IfElseNode([], [], $cond1);
            $node->elseif($cond2)('second');
            $node->elseif($cond3)('third');

            (string) $node;

            expect($eval1)->toBeTrue();
            expect($eval2)->toBeTrue();
            expect($eval3)->toBeFalse(); // Should not be evaluated
        });

        it('does not evaluate else content if condition matches', function () {
            $elseCalled = false;
            $elseContent = function () use (&$elseCalled) {
                $elseCalled = true;

                return 'else';
            };

            $node = new IfElseNode(['if content'], [$elseContent], true);
            (string) $node;

            expect($elseCalled)->toBeFalse();
        });
    });

    describe('pushCondition() Method', function () {
        it('adds condition to conditions array', function () {
            $node = new IfElseNode([], [], false);
            $node->pushCondition(true);
            $node('new content');

            expect((string) $node)->toBe('new content');
        });

        it('increments condition index', function () {
            $node = new IfElseNode([], [], false);
            $node('first');

            $node->pushCondition(true);
            $node('second');

            expect((string) $node)->toBe('second');
        });
    });
});
