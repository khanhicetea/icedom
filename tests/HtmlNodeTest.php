<?php

use IceTea\IceDOM\HtmlNode;
use IceTea\IceDOM\SafeString;

beforeEach(function () {
    // Reset any global state before each test
});

describe('HtmlNode', function () {

    describe('Constructor and Initialization', function () {
        it('can not be created with default parameters', function () {
            $node = new HtmlNode;
            expect($node->getChildren())->toBe([]);
        })->throws(\Exception::class);

        it('can be created with tag name', function () {
            $node = new HtmlNode([], 'div');
            expect((string) $node)->toBe('<div></div>');
        });

        it('can be created with children', function () {
            $node = new HtmlNode(['Hello', ' World'], 'p');
            expect((string) $node)->toBe('<p>Hello World</p>');
        });

        it('can be created with attributes', function () {
            $node = new HtmlNode([], 'div', ['id' => 'test', 'class' => 'container']);
            expect((string) $node)->toBe('<div id="test" class="container"></div>');
        });

        it('can be created as void element', function () {
            $node = new HtmlNode([], 'br', [], true);
            expect((string) $node)->toBe('<br>');
        });

        it('recognizes void tags', function () {
            $voidTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
            foreach ($voidTags as $tag) {
                expect(HtmlNode::VOID_TAGS)->toContain($tag);
            }
        });

        it('recognizes boolean attributes', function () {
            $booleanAttrs = ['disabled', 'checked', 'readonly', 'required', 'autofocus'];
            foreach ($booleanAttrs as $attr) {
                expect(HtmlNode::BOOLEAN_ATTRS)->toContain($attr);
            }
        });
    });

    describe('Static tag() Method', function () {
        it('creates tag with string as inner text', function () {
            $node = HtmlNode::tag('div', 'Hello World', null);
            expect((string) $node)->toBe('<div Hello World></div>');
        });

        it('creates tag with array of children (list)', function () {
            $node = HtmlNode::tag('ul', ['item1', 'item2'], null);
            expect((string) $node)->toBe('<ul>item1item2</ul>');
        });

        it('creates tag with associative array as attributes', function () {
            $node = HtmlNode::tag('a', ['href' => '#link', 'title' => 'Link'], null);
            expect((string) $node)->toBe('<a href="#link" title="Link"></a>');
        });

        it('creates tag with attributes and children', function () {
            $node = HtmlNode::tag('div', ['class' => 'box'], ['Content']);
            expect((string) $node)->toBe('<div class="box">Content</div>');
        });

        it('creates tag with null firstArgument and children', function () {
            $node = HtmlNode::tag('p', null, ['Paragraph text']);
            expect((string) $node)->toBe('<p>Paragraph text</p>');
        });

        it('creates tag with node as firstArgument', function () {
            $childNode = new HtmlNode(['Child'], 'span');
            $node = HtmlNode::tag('div', $childNode, null);
            expect((string) $node)->toBe('<div><span>Child</span></div>');
        });

        it('handles attributes with index 0 as underscore', function () {
            $node = HtmlNode::tag('div', [0 => 'inner text', 'class' => 'box'], null);
            expect((string) $node)->toBe('<div class="box" inner text></div>');
        });

        it('creates void tag correctly', function () {
            $node = HtmlNode::tag('input', ['type' => 'text', 'name' => 'username'], null, true);
            expect((string) $node)->toBe('<input type="text" name="username">');
        });
    });

    describe('Child Management', function () {
        it('can append child to non-void element', function () {
            $node = new HtmlNode([], 'div');
            $node->appendChild('Hello');
            expect((string) $node)->toBe('<div>Hello</div>');
        });

        it('throws exception when appending child to void element', function () {
            $node = new HtmlNode([], 'br', [], true);
            expect(fn () => $node->appendChild('text'))
                ->toThrow(Exception::class, 'Void element <br> cannot have children.');
        });

        it('chains appendChild calls', function () {
            $node = new HtmlNode([], 'div');
            $result = $node->appendChild('Hello');
            expect($result)->toBe($node);
        });
    });

    describe('Attribute Management', function () {
        it('can set and get attributes', function () {
            $node = new HtmlNode([], 'div');
            $node->setAttribute('id', 'test');
            expect($node->getAttribute('id'))->toBe('test');
        });

        it('returns default when attribute does not exist', function () {
            $node = new HtmlNode([], 'div');
            expect($node->getAttribute('missing', 'default'))->toBe('default');
        });

        it('chains setAttribute calls', function () {
            $node = new HtmlNode([], 'div');
            $result = $node->setAttribute('id', 'test');
            expect($result)->toBe($node);
        });

        it('can set attribute to null', function () {
            $node = new HtmlNode([], 'div', ['id' => 'test']);
            $node->setAttribute('id', null);
            expect((string) $node)->toBe('<div></div>');
        });
    });

    describe('Magic Methods for Attributes', function () {
        it('can set attributes using method calls', function () {
            $node = new HtmlNode([], 'a');
            $node->href('#link')->title('My Link');
            expect((string) $node)->toBe('<a href="#link" title="My Link"></a>');
        });

        it('converts underscores to hyphens in method calls', function () {
            $node = new HtmlNode([], 'div');
            $node->data_id('123');
            expect((string) $node)->toBe('<div data-id="123"></div>');
        });

        it('can get attributes using property access', function () {
            $node = new HtmlNode([], 'a', ['href' => '#link']);
            expect($node->href)->toBe('#link');
        });

        it('converts underscores to hyphens in property access', function () {
            $node = new HtmlNode([], 'div', ['data-id' => '123']);
            expect($node->data_id)->toBe('123');
        });

        it('returns null for non-existent attributes', function () {
            $node = new HtmlNode([], 'div');
            expect($node->missing)->toBeNull();
        });
    });

    describe('id() Method', function () {
        it('sets id attribute', function () {
            $node = new HtmlNode([], 'div');
            $node->id('my-id');
            expect((string) $node)->toBe('<div id="my-id"></div>');
        });

        it('chains id calls', function () {
            $node = new HtmlNode([], 'div');
            $result = $node->id('test');
            expect($result)->toBe($node);
        });
    });

    describe('classes() Method', function () {
        it('sets single class name', function () {
            $node = new HtmlNode([], 'div');
            $node->classes('active');
            expect((string) $node)->toBe('<div class="active"></div>');
        });

        it('sets multiple class names', function () {
            $node = new HtmlNode([], 'div');
            $node->classes('active', 'primary');
            expect((string) $node)->toBe('<div class="active primary"></div>');
        });

        it('handles indexed array of class names', function () {
            $node = new HtmlNode([], 'div');
            $node->classes(['active', 'primary']);
            expect((string) $node)->toBe('<div class="active primary"></div>');
        });

        it('handles associative array with conditions', function () {
            $node = new HtmlNode([], 'div');
            $node->classes(['active' => true, 'disabled' => false, 'primary' => true]);
            expect((string) $node)->toContain('active')
                ->and((string) $node)->toContain('primary')
                ->and((string) $node)->not->toContain('disabled');
        });

        it('handles mixed arrays', function () {
            $node = new HtmlNode([], 'div');
            $node->classes(['active', 'primary' => true, 'disabled' => false]);
            expect((string) $node)->toContain('active')
                ->and((string) $node)->toContain('primary')
                ->and((string) $node)->not->toContain('disabled');
        });

        it('handles empty arguments', function () {
            $node = new HtmlNode([], 'div');
            $node->classes();
            expect($node->getAttribute('class'))->toBeNull();
        });

        it('chains classes calls', function () {
            $node = new HtmlNode([], 'div');
            $result = $node->classes('active');
            expect($result)->toBe($node);
        });

        it('handles null values in arguments', function () {
            $node = new HtmlNode([], 'div');
            $node->classes('active', null, 'primary');
            expect((string) $node)->toBe('<div class="active primary"></div>');
        });
    });

    describe('Attributes to String Conversion', function () {
        it('returns empty string for no attributes', function () {
            $node = new HtmlNode([], 'div');
            expect((string) $node)->toBe('<div></div>');
        });

        it('escapes attribute names and values', function () {
            $node = new HtmlNode([], 'div', ['data-value' => '<script>']);
            expect((string) $node)->toContain('&lt;script&gt;');
        });

        it('handles boolean attributes with true value', function () {
            $node = new HtmlNode([], 'input', ['disabled' => true]);
            expect((string) $node)->toBe('<input disabled></input>');
        });

        it('omits boolean attributes with false value', function () {
            $node = new HtmlNode([], 'input', ['disabled' => false]);
            expect((string) $node)->toBe('<input></input>');
        });

        it('handles boolean attributes with truthy values', function () {
            $node = new HtmlNode([], 'input', ['required' => 1]);
            expect((string) $node)->toBe('<input required></input>');
        });

        it('handles underscore attribute as raw text', function () {
            $node = new HtmlNode([], 'div', ['_' => 'custom-attr']);
            expect((string) $node)->toBe('<div custom-attr></div>');
        });

        it('evaluates closure attributes', function () {
            $node = new HtmlNode([], 'div', [
                'data-count' => fn ($n) => count($n->getChildren()),
            ]);
            $node->appendChild('child1')->appendChild('child2');
            expect((string) $node)->toContain('data-count="2"');
        });

        it('handles multiple attributes in order', function () {
            $node = new HtmlNode([], 'input', [
                'type' => 'text',
                'name' => 'username',
                'id' => 'user-input',
            ]);
            $html = (string) $node;
            expect($html)->toContain('type="text"')
                ->and($html)->toContain('name="username"')
                ->and($html)->toContain('id="user-input"');
        });
    });

    describe('HTML String Generation', function () {
        it('generates simple element', function () {
            $node = new HtmlNode(['Hello'], 'p');
            expect((string) $node)->toBe('<p>Hello</p>');
        });

        it('generates void element without closing tag', function () {
            $node = new HtmlNode([], 'br', [], true);
            expect((string) $node)->toBe('<br>');
        });

        it('generates nested elements', function () {
            $inner = new HtmlNode(['Inner'], 'span');
            $outer = new HtmlNode([$inner], 'div');
            expect((string) $outer)->toBe('<div><span>Inner</span></div>');
        });

        it('escapes text content', function () {
            $node = new HtmlNode(['<script>alert("xss")</script>'], 'div');
            expect((string) $node)->toBe('<div>&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;</div>');
        });

        it('handles SafeString without escaping', function () {
            $node = new HtmlNode([new SafeString('<b>bold</b>')], 'div');
            expect((string) $node)->toBe('<div><b>bold</b></div>');
        });

        it('handles numeric children', function () {
            $node = new HtmlNode([42, 3.14], 'div');
            expect((string) $node)->toBe('<div>423.14</div>');
        });

        it('evaluates closure children', function () {
            $node = new HtmlNode([
                fn ($n) => 'Dynamic: '.count($n->getChildren()),
            ], 'div');
            expect((string) $node)->toBe('<div>Dynamic: 1</div>');
        });

        it('generates complex nested structure', function () {
            $node = new HtmlNode([], 'div', ['class' => 'container']);
            $header = new HtmlNode(['Title'], 'h1', ['id' => 'title']);
            $paragraph = new HtmlNode(['Content'], 'p');
            $node->appendChild($header)->appendChild($paragraph);

            $html = (string) $node;
            expect($html)->toContain('<div class="container">')
                ->and($html)->toContain('<h1 id="title">Title</h1>')
                ->and($html)->toContain('<p>Content</p>')
                ->and($html)->toContain('</div>');
        });
    });

    describe('toArray() Method', function () {
        it('converts simple node to array', function () {
            $node = new HtmlNode(['Hello'], 'div', ['id' => 'test']);
            $array = $node->toArray();

            expect($array)->toHaveKey('tagName')
                ->and($array['tagName'])->toBe('div')
                ->and($array)->toHaveKey('attrs')
                ->and($array['attrs'])->toBe(['id' => 'test'])
                ->and($array)->toHaveKey('children')
                ->and($array['children'])->toBe(['Hello'])
                ->and($array)->toHaveKey('isVoid')
                ->and($array['isVoid'])->toBe(false);
        });

        it('evaluates closure attributes in array', function () {
            $node = new HtmlNode([], 'div', [
                'data-count' => fn () => 5,
            ]);
            $array = $node->toArray();

            expect($array['attrs']['data-count'])->toBe(5);
        });

        it('converts nested HtmlNode children recursively', function () {
            $child = new HtmlNode(['Inner'], 'span');
            $parent = new HtmlNode([$child], 'div');
            $array = $parent->toArray();

            expect($array['children'][0])->toBeArray()
                ->and($array['children'][0]['tagName'])->toBe('span')
                ->and($array['children'][0]['children'])->toBe(['Inner']);
        });

        it('preserves non-HtmlNode children', function () {
            $node = new HtmlNode(['text', 42], 'div');
            $array = $node->toArray();

            expect($array['children'])->toBe(['text', 42]);
        });

        it('includes isVoid flag', function () {
            $voidNode = new HtmlNode([], 'br', [], true);
            $array = $voidNode->toArray();

            expect($array['isVoid'])->toBe(true);
        });
    });

    describe('Method Chaining', function () {
        it('supports fluent interface', function () {
            $node = new HtmlNode([], 'div');
            $result = $node
                ->id('container')
                ->classes('active', 'primary')
                ->setAttribute('data-role', 'main')
                ->appendChild('Content');

            expect($result)->toBe($node);
            $html = (string) $node;
            expect($html)->toContain('id="container"')
                ->and($html)->toContain('class="active primary"')
                ->and($html)->toContain('data-role="main"')
                ->and($html)->toContain('Content');
        });
    });

    describe('Edge Cases', function () {
        it('handles empty tag name', function () {
            $node = new HtmlNode(['Content']);
            expect((string) $node)->toBe('<>Content</>');
        })->throws(\Exception::class);

        it('handles special characters in attributes', function () {
            $node = new HtmlNode([], 'div', [
                'data-json' => '{"key":"value"}',
            ]);
            expect((string) $node)->toContain('&quot;');
        });

        it('handles null children', function () {
            $node = new HtmlNode(['Hello', null, 'World'], 'div');
            expect((string) $node)->toBe('<div>HelloWorld</div>');
        });

        it('handles empty children array', function () {
            $node = new HtmlNode([], 'div');
            expect((string) $node)->toBe('<div></div>');
        });

        it('handles mixed content types', function () {
            $node = new HtmlNode([
                'text',
                42,
                new SafeString('<br>'),
                new HtmlNode(['inner'], 'span'),
            ], 'div');
            expect((string) $node)->toBe('<div>text42<br><span>inner</span></div>');
        });

        it('handles attribute with empty string value', function () {
            $node = new HtmlNode([], 'div', ['data-value' => '']);
            expect((string) $node)->toBe('<div data-value=""></div>');
        });

        it('handles attribute with zero value', function () {
            $node = new HtmlNode([], 'div', ['data-count' => 0]);
            expect((string) $node)->toBe('<div data-count="0"></div>');
        });
    });

    describe('Integration with Parent Node Class', function () {
        it('inherits parent-child relationship management', function () {
            $parent = new HtmlNode([], 'div');
            $child = new HtmlNode(['Child'], 'span');

            $parent->appendChild($child);

            expect($child->getParent())->toBe($parent);
        });

        it('supports __invoke syntax', function () {
            $node = new HtmlNode([], 'div');
            $node('child1', 'child2');

            expect($node->getChildren())->toBe(['child1', 'child2']);
        });

        it('supports use() method', function () {
            $node = new HtmlNode([], 'div');
            $node->use(function ($n) {
                $n->id('dynamic');
            });

            expect((string) $node)->toBe('<div id="dynamic"></div>');
        });

        it('supports clearChildren() method', function () {
            $node = new HtmlNode(['child1', 'child2'], 'div');
            $node->clearChildren();

            expect((string) $node)->toBe('<div></div>');
        });
    });

    describe('Real World Usage Examples', function () {
        it('creates a form input', function () {
            $input = new HtmlNode([], 'input', [
                'type' => 'email',
                'name' => 'user_email',
                'required' => true,
                'placeholder' => 'Enter your email',
            ], true);

            $html = (string) $input;
            expect($html)->toBe('<input type="email" name="user_email" required placeholder="Enter your email">');
        });

        it('creates a link with attributes', function () {
            $link = HtmlNode::tag('a', ['href' => 'https://example.com', 'target' => '_blank'], ['Visit Example']);

            expect((string) $link)->toBe('<a href="https://example.com" target="_blank">Visit Example</a>');
        });

        it('creates a card component', function () {
            $card = new HtmlNode([], 'div');
            $card->classes('card', 'shadow');
            $card->appendChild(new HtmlNode(['Card Title'], 'h3', ['class' => 'card-title']));
            $card->appendChild(new HtmlNode(['Card content goes here.'], 'p', ['class' => 'card-body']));

            $html = (string) $card;
            expect($html)->toContain('<div class="card shadow">')
                ->and($html)->toContain('<h3 class="card-title">Card Title</h3>')
                ->and($html)->toContain('<p class="card-body">Card content goes here.</p>');
        });

        it('creates a list with dynamic items', function () {
            $list = new HtmlNode([], 'ul', ['class' => 'items']);
            $items = ['Apple', 'Banana', 'Cherry'];

            foreach ($items as $item) {
                $list->appendChild(new HtmlNode([$item], 'li'));
            }

            $html = (string) $list;
            expect($html)->toContain('<li>Apple</li>')
                ->and($html)->toContain('<li>Banana</li>')
                ->and($html)->toContain('<li>Cherry</li>');
        });
    });
});
