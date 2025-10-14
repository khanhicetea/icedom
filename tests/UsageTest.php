<?php

describe('Basic Patterns', function () {
    test('empty element', function () {
        $result = _div();
        expect((string) $result)->toBe('<div></div>');
    });

    test('with children', function () {
        $result = _div(['Hello World']);
        expect((string) $result)->toBe('<div>Hello World</div>');
    });

    test('with safe string', function () {
        $result = _div([_safe('Hello World')]);
        expect((string) $result)->toBe('<div>Hello World</div>');
    });

    test('with attributes and children', function () {
        $result = _div(['class' => 'box'], ['Hello']);
        expect((string) $result)->toBe('<div class="box">Hello</div>');
    });
});

describe('First Argument Patterns', function () {
    test('scalar string as raw multi attributes', function () {
        $result = _div('class="container" data-show="1"');
        expect((string) $result)->toBe('<div class="container" data-show="1"></div>');
    });

    test('list array becomes children', function () {
        $name = 'John';
        $result = _div(['hello', $name, ', how are you ?']);
        expect((string) $result)->toBe('<div>helloJohn, how are you ?</div>');
    });

    test('avoid null as first arg - use empty args', function () {
        $result = _div()('child1', 'child2', 'child3');
        expect((string) $result)->toBe('<div>child1child2child3</div>');
    });

    test('avoid null as first arg - use list array', function () {
        $result = _div(['child1', 'child2', 'child3']);
        expect((string) $result)->toBe('<div>child1child2child3</div>');
    });
});

describe('Invoke Syntax', function () {
    test('string shorthand for attributes', function () {
        $result = _div('class="box"')('Hello');
        expect((string) $result)->toBe('<div class="box">Hello</div>');
    });

    test('multiple children with invoke', function () {
        $result = _ul('class="menu"')(
            _li(['Item 1']),
            _li(['Item 2'])
        );
        expect((string) $result)->toBe('<ul class="menu"><li>Item 1</li><li>Item 2</li></ul>');
    });

    test('array initialization for children', function () {
        $result = _ul('class="menu"', [
            _li(['Item 1']),
            _li(['Item 2'])
        ]);
        expect((string) $result)->toBe('<ul class="menu"><li>Item 1</li><li>Item 2</li></ul>');
    });

    test('invoke with chaining methods', function () {
        $result = _ul('class="menu"')
            ->title('List title')
            ->data_status('collapsed');
        
        $result(
            _li(['Item 1']),
            _li(['Item 2'])
        );
        
        $output = (string) $result;
        expect($output)->toContain('class="menu"');
        expect($output)->toContain('title="List title"');
        expect($output)->toContain('data-status="collapsed"');
        expect($output)->toContain('<li>Item 1</li>');
        expect($output)->toContain('<li>Item 2</li>');
    });
});

describe('HTML Escaping', function () {
    test('automatic HTML escaping', function () {
        $userInput = '<script>alert("xss")</script>';
        $result = _div([$userInput]);
        expect((string) $result)->toBe('<div>&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;</div>');
    });

    test('safe string bypasses escaping', function () {
        $trustedHtml = '<strong>Important</strong>';
        $result = _div([_safe($trustedHtml)]);
        expect((string) $result)->toBe('<div><strong>Important</strong></div>');
    });
});

describe('HTML Elements', function () {
    test('lists with array syntax', function () {
        $result = _ul('class="menu"', [
            _li(['Home']),
            _li(['About']),
            _li(['Contact'])
        ]);
        
        $output = (string) $result;
        expect($output)->toContain('class="menu"');
        expect($output)->toContain('<li>Home</li>');
        expect($output)->toContain('<li>About</li>');
        expect($output)->toContain('<li>Contact</li>');
    });

    test('ordered list with invoke syntax', function () {
        $result = _ol('class="steps"')(
            _li(['First step']),
            _li(['Second step']),
            _li(['Third step'])
        );
        
        $output = (string) $result;
        expect($output)->toContain('class="steps"');
        expect($output)->toContain('<li>First step</li>');
    });

    test('form elements', function () {
        $result = _form(['action' => '/login', 'method' => 'post'], [
            _div(['class' => 'form-group'], [
                _label(['for' => 'email'], ['Email:']),
                _input(['type' => 'email', 'id' => 'email', 'name' => 'email', 'required' => true])
            ]),
            _button(['type' => 'submit', 'class' => 'btn-primary'], ['Login'])
        ]);
        
        $output = (string) $result;
        expect($output)->toContain('action="/login"');
        expect($output)->toContain('method="post"');
        expect($output)->toContain('type="email"');
        expect($output)->toContain('required');
    });

    test('table structure', function () {
        $result = _table('class="data-table"', [
            _thead([
                _tr([
                    _th(['Name']),
                    _th(['Age']),
                    _th(['Email'])
                ])
            ]),
            _tbody([
                _tr([
                    _td(['John']),
                    _td(['30']),
                    _td(['john@example.com'])
                ])
            ])
        ]);
        
        $output = (string) $result;
        expect($output)->toContain('<table class="data-table">');
        expect($output)->toContain('<thead>');
        expect($output)->toContain('<th>Name</th>');
        expect($output)->toContain('<td>John</td>');
    });

    test('void elements', function () {
        expect((string) _br())->toBe('<br>');
        expect((string) _hr())->toBe('<hr>');
        
        $img = _img(['src' => 'photo.jpg', 'alt' => 'Description']);
        expect((string) $img)->toBe('<img src="photo.jpg" alt="Description">');
    });

    test('semantic HTML', function () {
        $result = _header(['class' => 'site-header'], [
            _nav(['class' => 'main-nav'], ['Navigation'])
        ]);
        
        $output = (string) $result;
        expect($output)->toContain('<header class="site-header">');
        expect($output)->toContain('<nav class="main-nav">');
    });
});

describe('Working with Attributes', function () {
    test('setting attributes via array', function () {
        $div = _div(['id' => 'main', 'class' => 'container'], ['Content']);
        $output = (string) $div;
        
        expect($output)->toContain('id="main"');
        expect($output)->toContain('class="container"');
        expect($output)->toContain('Content');
    });

    test('setting attributes via setAttribute', function () {
        $div = _div(['Content']);
        $div->setAttribute('id', 'main');
        $div->setAttribute('class', 'container');
        
        $output = (string) $div;
        expect($output)->toContain('id="main"');
        expect($output)->toContain('class="container"');
    });

    test('setting attributes via magic methods', function () {
        $div = _div(['Content']);
        $div->id('main');
        $div->data_role('primary');
        $div->aria_label('Content');
        
        $output = (string) $div;
        expect($output)->toContain('id="main"');
        expect($output)->toContain('data-role="primary"');
        expect($output)->toContain('aria-label="Content"');
    });

    test('boolean attributes', function () {
        $checked = _input(['type' => 'checkbox', 'checked' => true]);
        expect((string) $checked)->toContain('checked');
        
        $unchecked = _input(['type' => 'checkbox', 'checked' => false]);
        expect((string) $unchecked)->not->toContain('checked');
        
        $disabled = _button(['disabled' => true], ['Save']);
        expect((string) $disabled)->toContain('disabled');
    });

    test('boolean attributes with magic methods', function () {
        $input = _input(['type' => 'checkbox']);
        $input->checked();
        $input->required();
        
        $output = (string) $input;
        expect($output)->toContain('checked');
        expect($output)->toContain('required');
    });

    test('CSS classes - single class', function () {
        $div = _div();
        $div->classes('active');
        
        expect((string) $div)->toContain('class="active"');
    });

    test('CSS classes - multiple classes', function () {
        $div = _div();
        $div->classes('btn', 'btn-primary', 'btn-lg');
        
        $output = (string) $div;
        expect($output)->toContain('btn');
        expect($output)->toContain('btn-primary');
        expect($output)->toContain('btn-lg');
    });

    test('CSS classes - conditional classes', function () {
        $div = _div();
        $div->classes([
            'active' => true,
            'disabled' => false,
            'primary' => true,
            'secondary' => false
        ]);
        
        $output = (string) $div;
        expect($output)->toContain('active');
        expect($output)->toContain('primary');
        expect($output)->not->toContain('disabled');
        expect($output)->not->toContain('secondary');
    });

    test('shorthand attributes with string', function () {
        $result = _div('data-custom="value" data-count="5"');
        $output = (string) $result;
        
        expect($output)->toContain('data-custom="value"');
        expect($output)->toContain('data-count="5"');
    });

    test('dynamic attributes with closures', function () {
        $result = _div([
            'class' => 'box',
            'data-count' => fn($node) => count($node->getChildren())
        ], [
            'Child 1',
            'Child 2',
            'Child 3'
        ]);
        
        $output = (string) $result;
        expect($output)->toContain('data-count="3"');
    });
});

describe('Dynamic Content', function () {
    test('map with simple list', function () {
        $fruits = ['Apple', 'Banana', 'Cherry'];
        
        $list = _ul('class="fruit-list"', array_map(fn($fruit) => 
            _li([$fruit]), $fruits
        ));
        
        $output = (string) $list;
        expect($output)->toContain('class="fruit-list"');
        expect($output)->toContain('<li>Apple</li>');
        expect($output)->toContain('<li>Banana</li>');
        expect($output)->toContain('<li>Cherry</li>');
    });

    test('map with index', function () {
        $fruits = ['Apple', 'Banana', 'Cherry'];
        
        $items = [];
        foreach ($fruits as $index => $fruit) {
            $items[] = _li([_strong([($index + 1) . '. ']), $fruit]);
        }
        $list = _ol($items);
        
        $output = (string) $list;
        expect($output)->toContain('<strong>1. </strong>Apple');
        expect($output)->toContain('<strong>2. </strong>Banana');
        expect($output)->toContain('<strong>3. </strong>Cherry');
    });

    test('map without mapping function', function () {
        $div = _div(['Item 1', 'Item 2', 'Item 3']);
        expect((string) $div)->toBe('<div>Item 1Item 2Item 3</div>');
    });

    test('complex mapping with users table', function () {
        $users = [
            ['name' => 'John', 'email' => 'john@example.com', 'role' => 'Admin'],
            ['name' => 'Jane', 'email' => 'jane@example.com', 'role' => 'User'],
        ];
        
        $table = _table('class="users-table"', [
            _thead([
                _tr([
                    _th(['Name']),
                    _th(['Email']),
                    _th(['Role'])
                ])
            ]),
            _tbody(array_map(fn($user) => 
                _tr([
                    _td([$user['name']]),
                    _td([$user['email']]),
                    _td([_span("class=\"badge badge-{$user['role']}\"", [$user['role']])])
                ]), $users
            ))
        ]);
        
        $output = (string) $table;
        expect($output)->toContain('<td>John</td>');
        expect($output)->toContain('<td>jane@example.com</td>');
        expect($output)->toContain('badge-Admin');
        expect($output)->toContain('badge-User');
    });

    test('mapping associative arrays', function () {
        $data = ['name' => 'John', 'age' => 30, 'city' => 'NYC'];
        
        $items = [];
        foreach ($data as $key => $value) {
            $items[] = _dt([ucfirst($key) . ':']);
            $items[] = _dd([$value]);
        }
        $dl = _dl($items);
        
        $output = (string) $dl;
        expect($output)->toContain('<dt>Name:</dt>');
        expect($output)->toContain('<dd>John</dd>');
        expect($output)->toContain('<dt>Age:</dt>');
        expect($output)->toContain('<dd>30</dd>');
    });

    test('dynamic children with closures', function () {
        $result = _div(['class' => 'card'], [
            'Item 1',
            'Item 2',
            fn($node) => 'Total items: ' . (count($node->getChildren()) - 1)
        ]);
        
        $output = (string) $result;
        expect($output)->toContain('Item 1');
        expect($output)->toContain('Item 2');
        expect($output)->toContain('Total items: 2');
    });
});

describe('Conditional Rendering', function () {
    test('basic if/else - true condition', function () {
        $result = _if(true)('Welcome back!')
            ->else('Please login');
        
        expect((string) $result)->toBe('Welcome back!');
    });

    test('basic if/else - false condition', function () {
        $result = _if(false)('Welcome back!')
            ->else('Please login');
        
        expect((string) $result)->toBe('Please login');
    });

    test('multiple conditions', function () {
        $role = 'moderator';
        
        $result = _if($role === 'admin')('Admin Dashboard')
            ->elseIf($role === 'moderator')('Moderator Panel')
            ->elseIf($role === 'user')('User Dashboard')
            ->else('Guest View');
        
        expect((string) $result)->toBe('Moderator Panel');
    });

    test('conditional with HTML elements', function () {
        $status = 'pending';
        
        $statusBadge = _if($status === 'completed')(_span(['class' => 'badge-success'], ['Completed']))
            ->elseIf($status === 'pending')(_span(['class' => 'badge-warning'], ['Pending']))
            ->elseIf($status === 'cancelled')(_span(['class' => 'badge-danger'], ['Cancelled']))
            ->else(_span(['class' => 'badge-secondary'], ['Unknown']));
        
        $output = (string) $statusBadge;
        expect($output)->toContain('badge-warning');
        expect($output)->toContain('Pending');
    });

    test('lazy evaluation with closures', function () {
        $isLoggedIn = true;
        $userName = 'Alice';
        
        $result = _if(fn() => $isLoggedIn)(fn() => "Welcome, {$userName}")
            ->else('Please login');
        
        expect((string) $result)->toBe('Welcome, Alice');
    });
});

describe('Component Patterns', function () {
    test('reusable button component', function () {
        $btn = function(string $text, string $variant = 'primary', array $attrs = []) {
            $defaultAttrs = [
                'type' => 'button',
                'class' => "btn btn-{$variant}"
            ];
            
            return _button(array_merge($defaultAttrs, $attrs), [$text]);
        };
        
        $button1 = $btn('Click Me');
        expect((string) $button1)->toContain('btn btn-primary');
        expect((string) $button1)->toContain('Click Me');
        
        $button2 = $btn('Delete', 'danger', ['onclick' => 'confirmDelete()']);
        expect((string) $button2)->toContain('btn btn-danger');
        expect((string) $button2)->toContain('onclick="confirmDelete()"');
    });

    test('card component', function () {
        $card = function(string $title, $content, $footer = null) {
            return _div('class="card"', [
                _div('class="card-header"', [
                    _h3('class="card-title"', [$title])
                ]),
                _div('class="card-body"', [$content]),
                $footer !== null ? _div('class="card-footer"', [$footer]) : null
            ]);
        };
        
        $myCard = $card('Welcome', _p(['This is a card component.']), 'Footer text');
        
        $output = (string) $myCard;
        expect($output)->toContain('card-title');
        expect($output)->toContain('Welcome');
        expect($output)->toContain('This is a card component.');
        expect($output)->toContain('Footer text');
    });

    test('badge component', function () {
        $badge = function(string $text, string $variant = 'primary') {
            return _span("class=\"badge badge-{$variant}\"", [$text]);
        };
        
        $badge1 = $badge('New', 'success');
        expect((string) $badge1)->toContain('badge-success');
        expect((string) $badge1)->toContain('New');
        
        $badge2 = $badge('Hot', 'danger');
        expect((string) $badge2)->toContain('badge-danger');
    });

    test('alert component', function () {
        $alert = function(string $message, string $type = 'info', bool $dismissible = false) {
            return _div("class=\"alert alert-{$type}\"", [
                $dismissible ? _button([
                    'type' => 'button',
                    'class' => 'close',
                    'data-dismiss' => 'alert'
                ], ['×']) : null,
                $message
            ]);
        };
        
        $alert1 = $alert('Operation successful!', 'success', true);
        $output1 = (string) $alert1;
        expect($output1)->toContain('alert-success');
        expect($output1)->toContain('data-dismiss="alert"');
        
        $alert2 = $alert('Please fill all fields.', 'warning');
        expect((string) $alert2)->toContain('alert-warning');
    });

    test('form field component', function () {
        $formField = function(string $label, string $name, string $type = 'text', array $attrs = []) {
            return _div('class="form-group"', [
                _label(['for' => $name], [$label]),
                _input(array_merge([
                    'type' => $type,
                    'id' => $name,
                    'name' => $name,
                    'class' => 'form-control'
                ], $attrs))
            ]);
        };
        
        $field = $formField('Email', 'email', 'email', ['required' => true, 'placeholder' => 'you@example.com']);
        
        $output = (string) $field;
        expect($output)->toContain('form-group');
        expect($output)->toContain('for="email"');
        expect($output)->toContain('type="email"');
        expect($output)->toContain('required');
        expect($output)->toContain('placeholder="you@example.com"');
    });
});

describe('Helper Functions', function () {
    test('_safe marks HTML as safe', function () {
        $result = _div([_safe('<strong>Bold</strong>')]);
        expect((string) $result)->toBe('<div><strong>Bold</strong></div>');
    });

    test('_raw creates raw node', function () {
        $result = _raw('<b>Bold</b>', ' ', '<i>Italic</i>');
        expect((string) $result)->toBe('<b>Bold</b> <i>Italic</i>');
    });

    test('_h creates custom elements', function () {
        $result = _h('custom-element', ['data-value' => '123']);
        expect((string) $result)->toBe('<custom-element data-value="123"></custom-element>');
    });

    test('clsf conditional class formatter', function () {
        $result1 = clsf('btn-%s btn-%s', 'primary', 'large');
        expect($result1)->toBe('btn-primary btn-large');
        
        $result2 = clsf('btn-%s btn-%s', null, null);
        expect($result2)->toBe('');
        
        $result3 = clsf('btn-%s', 'primary');
        expect($result3)->toBe('btn-primary');
    });

    test('_echo captures output buffer', function () {
        $result = _echo(function() {
            echo "Hello";
            echo " World";
        });
        
        expect((string) $result)->toBe('Hello World');
    });

    test('_slot with function', function () {
        $slot = _slot(fn() => 'Dynamic content');
        expect((string) $slot)->toBe('Dynamic content');
    });

    test('_slot with fallback', function () {
        $slot = _slot();
        $slot->appendChild('Fallback content');
        expect((string) $slot)->toBe('Fallback content');
    });
});

describe('Complete Examples', function () {
    test('full HTML page structure', function () {
        $page = _html('lang="en"', [
            _head([
                _meta(['charset' => 'UTF-8']),
                _meta(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0']),
                _title(['My Website - Home']),
                _link(['rel' => 'stylesheet', 'href' => '/css/style.css'])
            ]),
            
            _body([
                _header('class="site-header"', [
                    _nav('class="navbar container"', [
                        _a('href="/" class="logo"', ['MySite']),
                        _ul('class="nav-menu"', [
                            _li([_a(['href' => '/'], ['Home'])]),
                            _li([_a(['href' => '/about'], ['About'])])
                        ])
                    ])
                ]),
                
                _main('class="content container"', [
                    _h1(['Welcome to My Website']),
                    _p(['This is a complete HTML page built with IceDOM.'])
                ]),
                
                _footer('class="site-footer"', [
                    _p(['© 2024 MySite. All rights reserved.'])
                ]),
                
                _script(['src' => '/js/app.js'])
            ])
        ]);
        
        $output = (string) $page;
        expect($output)->toStartWith('<!DOCTYPE html>');
        expect($output)->toContain('lang="en"');
        expect($output)->toContain('charset="UTF-8"');
        expect($output)->toContain('Welcome to My Website');
        expect($output)->toContain('© 2024 MySite');
    });

    test('product grid with dynamic data', function () {
        $products = [
            ['name' => 'Laptop', 'price' => 999, 'image' => 'laptop.jpg', 'featured' => true],
            ['name' => 'Mouse', 'price' => 29, 'image' => 'mouse.jpg', 'featured' => false],
        ];
        
        $grid = _div('class="product-grid"', array_map(function($product) {
            return _div([
                'class' => 'product-card',
                'data-product-id' => $product['name']
            ], [
                $product['featured'] ? _span('class="badge-featured"', ['Featured']) : null,
                    
                _img([
                    'src' => "/images/{$product['image']}", 
                    'alt' => $product['name'],
                    'class' => 'product-image'
                ]),
                
                _div('class="product-info"', [
                    _h3('class="product-name"', [$product['name']]),
                    _p('class="product-price"', ['$' . number_format($product['price'], 2)])
                ])
            ]);
        }, $products));
        
        $output = (string) $grid;
        expect($output)->toContain('product-grid');
        expect($output)->toContain('data-product-id="Laptop"');
        expect($output)->toContain('badge-featured');
        expect($output)->toContain('$999.00');
        expect($output)->not->toContain('Featured">Mouse');
    });

    test('data table with actions', function () {
        $users = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'Admin', 'active' => true],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'User', 'active' => false],
        ];
        
        $badge = fn($text, $variant) => _span("class=\"badge badge-{$variant}\"", [$text]);
        
        $table = _div('class="table-responsive"', [
            _table('class="table table-striped"', [
                _thead([
                    _tr([
                        _th(['ID']),
                        _th(['Name']),
                        _th(['Email']),
                        _th(['Role']),
                        _th(['Status'])
                    ])
                ]),
                _tbody(array_map(function($user) use ($badge) {
                    $roleClass = $user['role'] === 'Admin' ? 'danger' : 'primary';
                    $statusClass = $user['active'] ? 'success' : 'secondary';
                    
                    return _tr([
                        _td([$user['id']]),
                        _td([$user['name']]),
                        _td([$user['email']]),
                        _td([$badge($user['role'], $roleClass)]),
                        _td([$badge($user['active'] ? 'Active' : 'Inactive', $statusClass)])
                    ]);
                }, $users))
            ])
        ]);
        
        $output = (string) $table;
        expect($output)->toContain('table-responsive');
        expect($output)->toContain('John Doe');
        expect($output)->toContain('badge-danger');
        expect($output)->toContain('badge-success');
        expect($output)->toContain('Inactive');
    });

    test('registration form with validation', function () {
        $errors = [
            'email' => 'Email is already registered',
            'password' => null
        ];
        
        $form = _form(['action' => '/register', 'method' => 'post', 'class' => 'registration-form'], [
            _h2(['Create Account']),
            
            // Email field
            _div([
                'class' => 'form-group' . (isset($errors['email']) ? ' has-error' : '')
            ], [
                _label(['for' => 'email'], ['Email Address']),
                _input([
                    'type' => 'email',
                    'id' => 'email',
                    'name' => 'email',
                    'class' => 'form-control',
                    'required' => true
                ]),
                isset($errors['email']) ? _span('class="error-message"', [$errors['email']]) : null
            ]),
            
            // Password field
            _div('class="form-group"', [
                _label(['for' => 'password'], ['Password']),
                _input([
                    'type' => 'password',
                    'id' => 'password',
                    'name' => 'password',
                    'class' => 'form-control',
                    'minlength' => 8,
                    'required' => true
                ]),
                _small('class="form-text"', ['Must be at least 8 characters'])
            ])
        ]);
        
        $output = (string) $form;
        expect($output)->toContain('registration-form');
        expect($output)->toContain('Create Account');
        expect($output)->toContain('has-error');
        expect($output)->toContain('Email is already registered');
        expect($output)->toContain('minlength="8"');
        expect($output)->toContain('Must be at least 8 characters');
    });
});

describe('Method Chaining', function () {
    test('fluent method chaining', function () {
        $result = _div()
            ->id('container')
            ->classes('active', 'shadow')
            ('Content here')
            ->setAttribute('data-role', 'main');
        
        $output = (string) $result;
        expect($output)->toContain('id="container"');
        expect($output)->toContain('active');
        expect($output)->toContain('shadow');
        expect($output)->toContain('Content here');
        expect($output)->toContain('data-role="main"');
    });
});

describe('Best Practices Validation', function () {
    test('avoid using null as first argument', function () {
        // Correct way - empty args then invoke
        $correct1 = _div()('child1', 'child2', 'child3');
        expect((string) $correct1)->toBe('<div>child1child2child3</div>');
        
        // Correct way - list array
        $correct2 = _div(['child1', 'child2', 'child3']);
        expect((string) $correct2)->toBe('<div>child1child2child3</div>');
        
        // Correct way - string + children array
        $correct3 = _div('class="box"', ['child1', 'child2']);
        $output = (string) $correct3;
        expect($output)->toContain('class="box"');
        expect($output)->toContain('child1');
    });

    test('avoid mixed key array for attributes', function () {
        // Correct way - string for raw attributes + methods
        $correct1 = _div('class="container" data-show="1"')
            ->setAttribute('align', 'left');
        
        $output = (string) $correct1;
        expect($output)->toContain('class="container"');
        expect($output)->toContain('data-show="1"');
        expect($output)->toContain('align="left"');
        
        // Correct way - associative array only
        $correct2 = _div(['class' => 'container', 'data-show' => '1', 'align' => 'left']);
        $output2 = (string) $correct2;
        expect($output2)->toContain('class="container"');
        expect($output2)->toContain('data-show="1"');
        expect($output2)->toContain('align="left"');
    });
});

