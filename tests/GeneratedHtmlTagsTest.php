<?php

use IceTea\IceDOM\HtmlNode;
use IceTea\IceDOM\HtmlDocument;
use IceTea\IceDOM\SafeString;
use IceTea\IceDOM\RawNode;
use IceTea\IceDOM\SlotNode;
use IceTea\IceDOM\IfElseNode;
use IceTea\IceDOM\EchoNode;

describe('Generated HTML Tag Functions', function () {

    describe('Primitive Node Functions', function () {
        it('_raw creates RawNode with children', function () {
            $node = _raw('<b>bold</b>', '<i>italic</i>');
            expect($node)->toBeInstanceOf(RawNode::class);
            expect((string) $node)->toBe('<b>bold</b><i>italic</i>');
        });

        it('_safe creates SafeString', function () {
            $safe = _safe('<b>bold</b>');
            expect($safe)->toBeInstanceOf(SafeString::class);
            expect((string) $safe)->toBe('<b>bold</b>');
        });

        it('_slot creates SlotNode', function () {
            $slot = _slot();
            expect($slot)->toBeInstanceOf(SlotNode::class);
        });

        it('_if creates IfElseNode', function () {
            $ifNode = _if(true);
            expect($ifNode)->toBeInstanceOf(IfElseNode::class);
        });

        it('_echo creates EchoNode with children', function () {
            $echo = _echo('Hello', ' ', 'World');
            expect($echo)->toBeInstanceOf(EchoNode::class);
            expect((string) $echo)->toBe('Hello World');
        });

        it('_h creates custom HtmlNode', function () {
            $node = _h('custom');
            expect($node)->toBeInstanceOf(HtmlNode::class);
            expect((string) $node)->toBe('<custom></custom>');
        });

        it('_h creates custom HtmlNode with attributes', function () {
            $node = _h('custom', ['id' => 'test']);
            expect((string) $node)->toBe('<custom id="test"></custom>');
        });

        it('clsf formats class string with all arguments', function () {
            $result = clsf('btn-%s btn-%s', 'primary', 'large');
            expect($result)->toBe('btn-primary btn-large');
        });

        it('clsf returns empty string when all arguments are null', function () {
            $result = clsf('btn-%s btn-%s', null, null);
            expect($result)->toBe('');
        });

        it('clsf handles mixed null and non-null arguments', function () {
            $result = clsf('btn-%s btn-%s', 'primary', null);
            expect($result)->toBe('btn-primary btn-');
        });
    });

    describe('Common HTML Tag Functions', function () {

        describe('_div function', function () {
            it('creates empty div', function () {
                $div = _div();
                expect((string) $div)->toBe('<div></div>');
            });

            it('creates div with shorthand attributes', function () {
                $div = _div('class="container" id="main"');
                expect((string) $div)->toBe('<div class="container" id="main"></div>');
            });

            it('creates div with attributes array', function () {
                $div = _div(['class' => 'container', 'id' => 'main']);
                expect((string) $div)->toBe('<div class="container" id="main"></div>');
            });

            it('creates div with attributes and children using array', function () {
                $div = _div(['class' => 'box'], ['Content here']);
                expect((string) $div)->toBe('<div class="box">Content here</div>');
            });

            it('creates div with shorthand attributes and children using invoke', function () {
                $div = _div('class="box"')('Content here');
                expect((string) $div)->toBe('<div class="box">Content here</div>');
            });

            it('creates div with only children array', function () {
                $div = _div(null, ['Content here']);
                expect((string) $div)->toBe('<div>Content here</div>');
            });

            it('creates div with children using list array', function () {
                $div = _div(['Hello', ' World']);
                expect((string) $div)->toBe('<div>Hello World</div>');
            });
        });

        describe('_span function', function () {
            it('creates empty span', function () {
                $span = _span();
                expect((string) $span)->toBe('<span></span>');
            });

            it('creates span with children using list array', function () {
                $span = _span(['Text']);
                expect((string) $span)->toBe('<span>Text</span>');
            });

            it('creates span with attributes and children', function () {
                $span = _span(['class' => 'badge'], ['New']);
                expect((string) $span)->toBe('<span class="badge">New</span>');
            });

            it('creates span with shorthand attributes and invoke', function () {
                $span = _span('class="badge"')('New');
                expect((string) $span)->toBe('<span class="badge">New</span>');
            });
        });

        describe('_p function', function () {
            it('creates paragraph with children using list array', function () {
                $p = _p(['This is a paragraph.']);
                expect((string) $p)->toBe('<p>This is a paragraph.</p>');
            });

            it('creates paragraph with attributes and children', function () {
                $p = _p(['class' => 'lead'], ['Intro text']);
                expect((string) $p)->toBe('<p class="lead">Intro text</p>');
            });

            it('creates paragraph with shorthand and invoke', function () {
                $p = _p('class="lead"')('Intro text');
                expect((string) $p)->toBe('<p class="lead">Intro text</p>');
            });
        });

        describe('_a function', function () {
            it('creates link with href', function () {
                $link = _a(['href' => '#section']);
                expect((string) $link)->toBe('<a href="#section"></a>');
            });

            it('creates link with href and text', function () {
                $link = _a(['href' => 'https://example.com'], ['Visit Site']);
                expect((string) $link)->toBe('<a href="https://example.com">Visit Site</a>');
            });

            it('creates link with multiple attributes', function () {
                $link = _a(['href' => 'https://example.com', 'target' => '_blank', 'rel' => 'noopener']);
                $html = (string) $link;
                expect($html)->toContain('href="https://example.com"')
                    ->and($html)->toContain('target="_blank"')
                    ->and($html)->toContain('rel="noopener"');
            });
        });

        describe('_button function', function () {
            it('creates button with children using list array', function () {
                $btn = _button(['Click Me']);
                expect((string) $btn)->toBe('<button>Click Me</button>');
            });

            it('creates button with attributes and children', function () {
                $btn = _button(['type' => 'submit', 'class' => 'btn-primary'], ['Submit']);
                $html = (string) $btn;
                expect($html)->toContain('type="submit"')
                    ->and($html)->toContain('class="btn-primary"')
                    ->and($html)->toContain('Submit');
            });

            it('creates button with shorthand and invoke', function () {
                $btn = _button('type="submit" class="btn-primary"')('Submit');
                $html = (string) $btn;
                expect($html)->toContain('type="submit"')
                    ->and($html)->toContain('class="btn-primary"')
                    ->and($html)->toContain('Submit');
            });

            it('creates disabled button', function () {
                $btn = _button(['disabled' => true], ['Disabled']);
                expect((string) $btn)->toContain('disabled');
            });
        });

        describe('_input function (void element)', function () {
            it('creates input without closing tag', function () {
                $input = _input(['type' => 'text']);
                expect((string) $input)->toBe('<input type="text">');
            });

            it('creates input with multiple attributes', function () {
                $input = _input(['type' => 'email', 'name' => 'email', 'placeholder' => 'Enter email']);
                $html = (string) $input;
                expect($html)->toContain('type="email"')
                    ->and($html)->toContain('name="email"')
                    ->and($html)->toContain('placeholder="Enter email"')
                    ->and($html)->not->toContain('</input>');
            });

            it('creates checkbox input', function () {
                $input = _input(['type' => 'checkbox', 'name' => 'agree', 'checked' => true]);
                $html = (string) $input;
                expect($html)->toContain('checked')
                    ->and($html)->not->toContain('</input>');
            });
        });

        describe('_img function (void element)', function () {
            it('creates img without closing tag', function () {
                $img = _img(['src' => 'image.jpg', 'alt' => 'Description']);
                $html = (string) $img;
                expect($html)->toContain('src="image.jpg"')
                    ->and($html)->toContain('alt="Description"')
                    ->and($html)->not->toContain('</img>');
            });

            it('creates img with width and height', function () {
                $img = _img(['src' => 'photo.jpg', 'width' => '300', 'height' => '200']);
                $html = (string) $img;
                expect($html)->toContain('width="300"')
                    ->and($html)->toContain('height="200"');
            });
        });

        describe('_br function (void element)', function () {
            it('creates br without closing tag', function () {
                $br = _br();
                expect((string) $br)->toBe('<br>');
            });
        });

        describe('_hr function (void element)', function () {
            it('creates hr without closing tag', function () {
                $hr = _hr();
                expect((string) $hr)->toBe('<hr>');
            });

            it('creates hr with class', function () {
                $hr = _hr(['class' => 'my-divider']);
                expect((string) $hr)->toContain('class="my-divider"')
                    ->and((string) $hr)->not->toContain('</hr>');
            });
        });

        describe('Heading functions', function () {
            it('_h1 creates h1 element', function () {
                $h1 = _h1(['Main Title']);
                expect((string) $h1)->toBe('<h1>Main Title</h1>');
            });

            it('_h2 creates h2 element', function () {
                $h2 = _h2(['class' => 'subtitle'], ['Section Title']);
                expect((string) $h2)->toBe('<h2 class="subtitle">Section Title</h2>');
            });

            it('_h3 creates h3 element with shorthand', function () {
                $h3 = _h3('class="section-title"')('Subsection');
                expect((string) $h3)->toBe('<h3 class="section-title">Subsection</h3>');
            });

            it('_h4 creates h4 element', function () {
                $h4 = _h4(['Small Heading']);
                expect((string) $h4)->toBe('<h4>Small Heading</h4>');
            });

            it('_h5 creates h5 element', function () {
                $h5 = _h5(['Smaller Heading']);
                expect((string) $h5)->toBe('<h5>Smaller Heading</h5>');
            });

            it('_h6 creates h6 element', function () {
                $h6 = _h6(['Smallest Heading']);
                expect((string) $h6)->toBe('<h6>Smallest Heading</h6>');
            });
        });

        describe('List functions', function () {
            it('_ul creates unordered list', function () {
                $ul = _ul(['class' => 'menu']);
                expect((string) $ul)->toBe('<ul class="menu"></ul>');
            });

            it('_ol creates ordered list', function () {
                $ol = _ol(['class' => 'steps']);
                expect((string) $ol)->toBe('<ol class="steps"></ol>');
            });

            it('_li creates list item', function () {
                $li = _li(['Item 1']);
                expect((string) $li)->toBe('<li>Item 1</li>');
            });

            it('creates complete list structure', function () {
                $ul = _ul(null, [
                    _li(['First']),
                    _li(['Second']),
                    _li(['Third']),
                ]);
                $html = (string) $ul;
                expect($html)->toContain('<ul>')
                    ->and($html)->toContain('<li>First</li>')
                    ->and($html)->toContain('<li>Second</li>')
                    ->and($html)->toContain('<li>Third</li>')
                    ->and($html)->toContain('</ul>');
            });
        });

        describe('Form functions', function () {
            it('_form creates form element', function () {
                $form = _form(['action' => '/submit', 'method' => 'post']);
                $html = (string) $form;
                expect($html)->toContain('action="/submit"')
                    ->and($html)->toContain('method="post"');
            });

            it('_label creates label element', function () {
                $label = _label(['for' => 'username'], ['Username:']);
                expect((string) $label)->toBe('<label for="username">Username:</label>');
            });

            it('_textarea creates textarea element', function () {
                $textarea = _textarea(['name' => 'message', 'rows' => '5']);
                $html = (string) $textarea;
                expect($html)->toContain('name="message"')
                    ->and($html)->toContain('rows="5"')
                    ->and($html)->toContain('</textarea>');
            });

            it('_select creates select element', function () {
                $select = _select(['name' => 'country'], [
                    _option(['value' => 'us'], ['United States']),
                    _option(['value' => 'uk'], ['United Kingdom']),
                ]);
                $html = (string) $select;
                expect($html)->toContain('<select')
                    ->and($html)->toContain('<option')
                    ->and($html)->toContain('</select>');
            });

            it('_option creates option element', function () {
                $option = _option(['value' => 'red'], ['Red']);
                expect((string) $option)->toBe('<option value="red">Red</option>');
            });

            it('_fieldset creates fieldset element', function () {
                $fieldset = _fieldset(['class' => 'group']);
                expect((string) $fieldset)->toContain('<fieldset');
            });

            it('_legend creates legend element', function () {
                $legend = _legend(['Personal Information']);
                expect((string) $legend)->toBe('<legend>Personal Information</legend>');
            });
        });

        describe('Table functions', function () {
            it('_table creates table element', function () {
                $table = _table(['class' => 'data-table']);
                expect((string) $table)->toBe('<table class="data-table"></table>');
            });

            it('_thead creates thead element', function () {
                $thead = _thead();
                expect((string) $thead)->toBe('<thead></thead>');
            });

            it('_tbody creates tbody element', function () {
                $tbody = _tbody();
                expect((string) $tbody)->toBe('<tbody></tbody>');
            });

            it('_tfoot creates tfoot element', function () {
                $tfoot = _tfoot();
                expect((string) $tfoot)->toBe('<tfoot></tfoot>');
            });

            it('_tr creates table row', function () {
                $tr = _tr();
                expect((string) $tr)->toBe('<tr></tr>');
            });

            it('_th creates table header cell', function () {
                $th = _th(['Name']);
                expect((string) $th)->toBe('<th>Name</th>');
            });

            it('_td creates table data cell', function () {
                $td = _td(['John Doe']);
                expect((string) $td)->toBe('<td>John Doe</td>');
            });
        });

        describe('Semantic HTML functions', function () {
            it('_header creates header element', function () {
                $header = _header(['class' => 'site-header']);
                expect((string) $header)->toBe('<header class="site-header"></header>');
            });

            it('_footer creates footer element', function () {
                $footer = _footer(['class' => 'site-footer']);
                expect((string) $footer)->toBe('<footer class="site-footer"></footer>');
            });

            it('_nav creates nav element', function () {
                $nav = _nav(['class' => 'main-nav']);
                expect((string) $nav)->toBe('<nav class="main-nav"></nav>');
            });

            it('_main creates main element', function () {
                $main = _main(['class' => 'content']);
                expect((string) $main)->toBe('<main class="content"></main>');
            });

            it('_section creates section element', function () {
                $section = _section(['class' => 'features']);
                expect((string) $section)->toBe('<section class="features"></section>');
            });

            it('_article creates article element', function () {
                $article = _article(['class' => 'post']);
                expect((string) $article)->toBe('<article class="post"></article>');
            });

            it('_aside creates aside element', function () {
                $aside = _aside(['class' => 'sidebar']);
                expect((string) $aside)->toBe('<aside class="sidebar"></aside>');
            });
        });

        describe('Text formatting functions', function () {
            it('_strong creates strong element', function () {
                $strong = _strong(['Important']);
                expect((string) $strong)->toBe('<strong>Important</strong>');
            });

            it('_em creates em element', function () {
                $em = _em(['Emphasized']);
                expect((string) $em)->toBe('<em>Emphasized</em>');
            });

            it('_b creates bold element', function () {
                $b = _b(['Bold text']);
                expect((string) $b)->toBe('<b>Bold text</b>');
            });

            it('_i creates italic element', function () {
                $i = _i(['Italic text']);
                expect((string) $i)->toBe('<i>Italic text</i>');
            });

            it('_u creates underline element', function () {
                $u = _u(['Underlined']);
                expect((string) $u)->toBe('<u>Underlined</u>');
            });

            it('_small creates small element', function () {
                $small = _small(['Fine print']);
                expect((string) $small)->toBe('<small>Fine print</small>');
            });

            it('_mark creates mark element', function () {
                $mark = _mark(['Highlighted']);
                expect((string) $mark)->toBe('<mark>Highlighted</mark>');
            });

            it('_code creates code element', function () {
                $code = _code(['console.log()']);
                expect((string) $code)->toBe('<code>console.log()</code>');
            });

            it('_pre creates pre element', function () {
                $pre = _pre(['Preformatted']);
                expect((string) $pre)->toBe('<pre>Preformatted</pre>');
            });
        });

        describe('Other common functions', function () {
            it('_script creates script element', function () {
                $script = _script(['src' => 'app.js']);
                expect((string) $script)->toContain('src="app.js"')
                    ->and((string) $script)->toContain('</script>');
            });

            it('_style creates style element', function () {
                $style = _style(['body { margin: 0; }']);
                expect((string) $style)->toBe('<style>body { margin: 0; }</style>');
            });

            it('_link creates link element (void)', function () {
                $link = _link(['rel' => 'stylesheet', 'href' => 'style.css']);
                $html = (string) $link;
                expect($html)->toContain('rel="stylesheet"')
                    ->and($html)->toContain('href="style.css"')
                    ->and($html)->not->toContain('</link>');
            });

            it('_meta creates meta element (void)', function () {
                $meta = _meta(['name' => 'viewport', 'content' => 'width=device-width']);
                $html = (string) $meta;
                expect($html)->toContain('name="viewport"')
                    ->and($html)->not->toContain('</meta>');
            });

            it('_title creates title element', function () {
                $title = _title(['Page Title']);
                expect((string) $title)->toBe('<title>Page Title</title>');
            });
        });

        describe('_html function', function () {
            it('creates HtmlDocument instance', function () {
                $html = _html();
                expect($html)->toBeInstanceOf(HtmlDocument::class);
            });

            it('creates html with lang attribute', function () {
                $html = _html(['lang' => 'en']);
                expect((string) $html)->toContain('lang="en"');
            });
        });
    });

    describe('SVG Tag Functions', function () {
        it('_svg creates svg element', function () {
            $svg = _svg(['width' => '100', 'height' => '100']);
            $html = (string) $svg;
            expect($html)->toContain('width="100"')
                ->and($html)->toContain('height="100"')
                ->and($html)->toContain('<svg');
        });

        it('_circle creates circle element', function () {
            $circle = _circle(['cx' => '50', 'cy' => '50', 'r' => '40']);
            $html = (string) $circle;
            expect($html)->toContain('cx="50"')
                ->and($html)->toContain('cy="50"')
                ->and($html)->toContain('r="40"');
        });

        it('_rect creates rect element', function () {
            $rect = _rect(['x' => '10', 'y' => '10', 'width' => '80', 'height' => '80']);
            $html = (string) $rect;
            expect($html)->toContain('x="10"')
                ->and($html)->toContain('width="80"');
        });

        it('_path creates path element', function () {
            $path = _path(['d' => 'M 10 10 L 90 90']);
            expect((string) $path)->toContain('d="M 10 10 L 90 90"');
        });

        it('_g creates g (group) element', function () {
            $g = _g(['class' => 'layer']);
            expect((string) $g)->toBe('<g class="layer"></g>');
        });
    });

    describe('Complex HTML Structure Examples', function () {
        it('creates a navigation menu', function () {
            $nav = _nav(['class' => 'main-nav'], [
                _ul(['class' => 'nav-list'], [
                    _li(null, [_a(['href' => '/'], ['Home'])]),
                    _li(null, [_a(['href' => '/about'], ['About'])]),
                    _li(null, [_a(['href' => '/contact'], ['Contact'])]),
                ]),
            ]);

            $html = (string) $nav;
            expect($html)->toContain('<nav class="main-nav">')
                ->and($html)->toContain('<ul class="nav-list">')
                ->and($html)->toContain('href="/"')
                ->and($html)->toContain('Home')
                ->and($html)->toContain('</nav>');
        });

        it('creates a form with inputs', function () {
            $form = _form(['action' => '/login', 'method' => 'post'], [
                _label(['for' => 'email'], ['Email:']),
                _input(['type' => 'email', 'id' => 'email', 'name' => 'email']),
                _label(['for' => 'password'], ['Password:']),
                _input(['type' => 'password', 'id' => 'password', 'name' => 'password']),
                _button(['type' => 'submit'], ['Login']),
            ]);

            $html = (string) $form;
            expect($html)->toContain('<form')
                ->and($html)->toContain('action="/login"')
                ->and($html)->toContain('type="email"')
                ->and($html)->toContain('type="password"')
                ->and($html)->toContain('Login')
                ->and($html)->toContain('</form>');
        });

        it('creates a card component', function () {
            $card = _div(['class' => 'card'], [
                _div(['class' => 'card-header'], [
                    _h3(['Card Title']),
                ]),
                _div(['class' => 'card-body'], [
                    _p(['Card content goes here.']),
                ]),
                _div(['class' => 'card-footer'], [
                    _button(['class' => 'btn'], ['Action']),
                ]),
            ]);

            $html = (string) $card;
            expect($html)->toContain('class="card"')
                ->and($html)->toContain('card-header')
                ->and($html)->toContain('card-body')
                ->and($html)->toContain('card-footer')
                ->and($html)->toContain('Card Title')
                ->and($html)->toContain('Card content');
        });

        it('creates a table with data', function () {
            $table = _table(['class' => 'data-table'], [
                _thead(null, [
                    _tr(null, [
                        _th(['Name']),
                        _th(['Age']),
                        _th(['City']),
                    ]),
                ]),
                _tbody(null, [
                    _tr(null, [
                        _td(['John Doe']),
                        _td(['30']),
                        _td(['New York']),
                    ]),
                    _tr(null, [
                        _td(['Jane Smith']),
                        _td(['25']),
                        _td(['Los Angeles']),
                    ]),
                ]),
            ]);

            $html = (string) $table;
            expect($html)->toContain('<table')
                ->and($html)->toContain('<thead>')
                ->and($html)->toContain('<tbody>')
                ->and($html)->toContain('John Doe')
                ->and($html)->toContain('Jane Smith')
                ->and($html)->toContain('</table>');
        });
    });
});

