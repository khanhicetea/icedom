# IceDOM Usage Guide

**IceDOM** is a pure PHP library for building HTML documents using a Virtual DOM-like approach. Write HTML in PHP with a fluent, type-safe API—no templates required.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Basic Patterns](#basic-patterns)
- [Core Concepts](#core-concepts)
- [HTML Elements](#html-elements)
- [Working with Attributes](#working-with-attributes)
- [Dynamic Content](#dynamic-content)
- [Conditional Rendering](#conditional-rendering)
- [Component Patterns](#component-patterns)
- [Complete Examples](#complete-examples)
- [API Reference](#api-reference)
- [Best Practices](#best-practices)

## Installation

```bash
composer require icetea/icedom
```

## Quick Start

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/icetea/icedom/src/generated_html_tags.php';

use function IceTea\IceDOM\{_div, _h1, _p, _button};

// Create a simple card
$card = _div(['class' => 'card'], [
    _h1(['Welcome to IceDOM']),
    _p(['Build HTML with PHP, fluently and safely.']),
    _button(['class' => 'btn-primary'], ['Get Started']),
]);

echo $card;
```

## Basic Patterns

### Element Creation

```php
// Empty element
_div();

// With children
_div(['Hello World']);

// With safe string (won't be HTML-escaped)
_div([_safe('Hello World')]);

// With attributes and children
_div(['class' => 'box'], ['Hello']);
```

### First argument is important

```php
// First argument is scalar string, it is raw multi attributes string, won't be escaped
_div('class="container" data-show="1"');

// First argument is list array , numbered key => it becomes array of children
_div(['hello', $name, ', how are you ?']);

// First argument is mixed key array, first array item become raw multi attributes string, and the rest is array of attributes. (DON'T RECOMMEND THIS WAY)
_div(['class="container" data-show="1"', 'align' => 'left']);

// NO RECOMMEND null as first arg, try empty args then use invoke or magic call
_div(null, ['child1', 'child2'])('child3');
```

### Invoke Syntax

```php
// String shorthand for attributes
_div('class="box"')('Hello');
_div('class="box"')('Hello ', $name);

// Multiple children with __invoke
_ul('class="menu"')(
    _li(['Item 1']),
    _li(['Item 2'])
);

// Prefer array initialization for children (faster)
_ul('class="menu"', [
    _li(['Item 1']),
    _li(['Item 2'])
]);

// Invoke is preferred when chaining methods (more readable, looks like HTML)
_ul('class="menu"')
    ->title('List title')
    ->data_status('collapsed')
    ->(
        _li(['Item 1']),
        _li(['Item 2'])
    );
```

## Core Concepts

### Automatic HTML Escaping

**All string content is automatically escaped** to prevent XSS attacks:

```php
$userInput = '<script>alert("xss")</script>';

echo _div([$userInput]);
// Output: <div>&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;</div>
```

**To output trusted HTML**, use `_safe()`:

```php
$trustedHtml = '<strong>Important</strong>';

echo _div([_safe($trustedHtml)]);
// Output: <div><strong>Important</strong></div>
```

⚠️ **Security Warning**: Only use `_safe()` with content you trust or have manually sanitized!

### Node Tree Structure

Every element is a node in a tree:

```php
$parent = _div(['Parent content']);
$child = _span(['Child content']);

$parent->appendChild($child);

// Nodes know their parent
var_dump($child->getParent() === $parent); // true
```

### Method Chaining

All methods return `$this` for fluent chaining:

```php
_div()
    ->id('container')
    ->classes('card', 'shadow')
    ('Content here')
    ->setAttribute('data-role', 'main');
```

## HTML Elements

### Common Elements

```php
// Containers
_div(['class' => 'container'], [...]);
_span(['class' => 'badge'], ['New']);
_p(['Lead paragraph text']);

// Headings
_h1(['Page Title']);
_h2(['Section Title']);
_h3(['Subsection']);

// Links and Buttons
_a(['href' => '/about', 'target' => '_blank'], ['About Us']);
_button(['type' => 'submit', 'class' => 'btn'], ['Submit']);
```

### Lists

```php
// Using array syntax (recommended for static lists)
_ul('class="menu"', [
    _li(['Home']),
    _li(['About']),
    _li(['Contact'])
]);

// Using invoke syntax (recommended when chaining methods)
_ol('class="steps"')(
    _li(['First step']),
    _li(['Second step']),
    _li(['Third step'])
);
```

### Forms

```php
_form(['action' => '/login', 'method' => 'post'], [
    _div(['class' => 'form-group'], [
        _label(['for' => 'email'], ['Email:']),
        _input(['type' => 'email', 'id' => 'email', 'name' => 'email', 'required' => true])
    ]),
    
    _div(['class' => 'form-group'], [
        _label(['for' => 'password'], ['Password:']),
        _input(['type' => 'password', 'id' => 'password', 'name' => 'password', 'required' => true])
    ]),
    
    _button(['type' => 'submit', 'class' => 'btn-primary'], ['Login'])
]);
```

### Tables

```php
// Static table
_table('class="data-table"', [
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
```

### Void Elements

```php
// Self-closing tags (cannot have children)
_br();
_hr();
_img(['src' => 'photo.jpg', 'alt' => 'Description']);
_input(['type' => 'text', 'name' => 'username', 'placeholder' => 'Enter username']);
_link(['rel' => 'stylesheet', 'href' => 'style.css']);
_meta(['name' => 'viewport', 'content' => 'width=device-width']);
```

### Semantic HTML

```php
_header(['class' => 'site-header'], [
    _nav(['class' => 'main-nav'], [...])
]);

_main(['class' => 'content'], [
    _article(['class' => 'post'], [...]),
    _aside(['class' => 'sidebar'], [...])
]);

_footer(['class' => 'site-footer'], [...]);
```

## Working with Attributes

### Setting Attributes

```php
// Via array (at creation)
$div = _div(['id' => 'main', 'class' => 'container'], ['Content']);

// Via setAttribute()
$div = _div(['Content']);
$div->setAttribute('id', 'main');
$div->setAttribute('class', 'container');

// Via magic methods (underscores become hyphens)
$div = _div(['Content']);
$div->id('main');
$div->data_role('primary');  // Sets data-role="primary"
$div->aria_label('Content'); // Sets aria-label="Content"
```

### Boolean Attributes

```php
// Checkbox input
_input(['type' => 'checkbox', 'checked' => true]);
// Output: <input type="checkbox" checked>

_input(['type' => 'checkbox', 'checked' => false]);
// Output: <input type="checkbox">

// Disabled button
_button(['disabled' => true], ['Save']);
// Output: <button disabled>Save</button>

// Using magic methods (defaults to true)
$input = _input(['type' => 'checkbox']);
$input->checked();     // Same as ->checked(true)
$input->required();    // Same as ->required(true)
```

### CSS Classes

```php
// Single class
_div()->classes('active');

// Multiple classes
_div()->classes('btn', 'btn-primary', 'btn-lg');

// Array of classes
_div()->classes(['btn', 'btn-primary', 'btn-lg']);

// Conditional classes
$div = _div();
$div->classes([
    'active' => $isActive,
    'disabled' => $isDisabled,
    'primary' => true,
    'secondary' => false  // Won't be added
]);

// Combining approaches
_div()->classes('btn', ['active' => $isActive], 'shadow');
```

### Shorthand Attributes

The special `_` key or string first argument allows raw attribute strings:

```php
// Using _ key
_div([
    'class' => 'box',
    '_' => 'data-custom="value" data-count="5"'
]);
// Output: <div class="box" data-custom="value" data-count="5"></div>

// Using string as first argument (recommended)
_div('data-custom="value" data-count="5"');
// Output: <div data-custom="value" data-count="5"></div>
```

### Dynamic Attributes with Closures

```php
// Attribute value from closure
_div([
    'class' => 'box',
    'data-count' => fn($node) => count($node->getChildren())
], [
    'Child 1',
    'Child 2',
    'Child 3'
]);
// Output: <div class="box" data-count="3">...</div>
```

## Dynamic Content

### Using map()

The `map()` method transforms arrays into HTML nodes:

```php
$fruits = ['Apple', 'Banana', 'Cherry'];

// Simple list
$list = _ul('class="fruit-list"')->map($fruits, fn($fruit) => 
    _li([$fruit])
);

// With index
$list = _ol()->map($fruits, fn($fruit, $index) => 
    _li()(
        _strong([($index + 1) . '. ']),
        $fruit
    )
);

// Without mapping function (direct output)
$div = _div()->map(['Item 1', 'Item 2', 'Item 3']);
// Output: <div>Item 1Item 2Item 3</div>
```

### Complex Mapping

```php
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
    _tbody()->map($users, fn($user) => 
        _tr([
            _td([$user['name']]),
            _td([$user['email']]),
            _td([_span("class=\"badge badge-{$user['role']}\"", [$user['role']])])
        ])
    )
]);
```

### Mapping Associative Arrays

```php
$data = ['name' => 'John', 'age' => 30, 'city' => 'NYC'];

$dl = _dl()->map($data, fn($value, $key) => [
    _dt([ucfirst($key) . ':']),
    _dd([$value])
]);
// Output: <dl><dt>Name:</dt><dd>John</dd><dt>Age:</dt><dd>30</dd>...</dl>
```

### Dynamic Children with Closures

```php
// Closure receives parent node
_div(['class' => 'card'], [
    'Item 1',
    'Item 2',
    fn($node) => 'Total items: ' . (count($node->getChildren()) - 1)
]);
// Output: <div class="card">Item 1Item 2Total items: 2</div>
```

## Conditional Rendering

### Basic if/else

```php
_if($isLoggedIn)
    ('Welcome back!')
->else()
    ('Please login');
```

### Multiple Conditions

```php
_if($user->role === 'admin')
    ('Admin Dashboard')
->elseIf($user->role === 'moderator')
    ('Moderator Panel')
->elseIf($user->role === 'user')
    ('User Dashboard')
->else()
    ('Guest View');
```

### Conditional with HTML Elements

```php
$statusBadge = _if($order->status === 'completed')
    (_span(['class' => 'badge-success'], ['Completed']))
->elseIf($order->status === 'pending')
    (_span(['class' => 'badge-warning'], ['Pending']))
->elseIf($order->status === 'cancelled')
    (_span(['class' => 'badge-danger'], ['Cancelled']))
->else()
    (_span(['class' => 'badge-secondary'], ['Unknown']));
```

### Lazy Evaluation with Closures

```php
// Conditions and content are evaluated on each render
_if(fn() => $user->isLoggedIn())
    (fn() => "Welcome, {$user->name}")
->else()
    ('Please login');
```

### Nested Conditionals

```php
$userMenu = _div('class="user-menu"')(
    _if($user->isLoggedIn())
        (_div('class="user-info"', [
            _img(['src' => $user->avatar, 'alt' => $user->name, 'class' => 'avatar']),
            _span('class="username"', [$user->name]),
            _if($user->isAdmin())
                (_a(['href' => '/admin', 'class' => 'admin-link'], ['Admin Panel']))
            ->else()
                (null),
            _a(['href' => '/logout'], ['Logout'])
        ]))
    ->else()
        (_div('class="auth-links"', [
            _a(['href' => '/login', 'class' => 'btn'], ['Login']),
            _a(['href' => '/register', 'class' => 'btn-primary'], ['Register'])
        ]))
);
```

## Component Patterns

### Reusable Button Component

```php
function btn(string $text, string $variant = 'primary', array $attrs = []) {
    $defaultAttrs = [
        'type' => 'button',
        'class' => "btn btn-{$variant}"
    ];
    
    return _button(array_merge($defaultAttrs, $attrs), [$text]);
}

// Usage
echo btn('Click Me');
echo btn('Delete', 'danger', ['onclick' => 'confirmDelete()']);
```

### Card Component

```php
function card(string $title, $content, $footer = null) {
    return _div('class="card"', [
        _div('class="card-header"', [
            _h3('class="card-title"', [$title])
        ]),
        _div('class="card-body"', [$content]),
        _if($footer !== null)
            (_div('class="card-footer"', [$footer]))
        ->else()
            (null)
    ]);
}

// Usage
echo card(
    'Welcome',
    _p(['This is a card component.']),
    btn('Learn More', 'secondary')
);
```

### Badge Component

```php
function badge(string $text, string $variant = 'primary') {
    return _span("class=\"badge badge-{$variant}\"", [$text]);
}

// Usage
echo badge('New', 'success');
echo badge('Hot', 'danger');
```

### Alert Component

```php
function alert(string $message, string $type = 'info', bool $dismissible = false) {
    return _div("class=\"alert alert-{$type}\"", [
        $dismissible ? _button([
            'type' => 'button',
            'class' => 'close',
            'data-dismiss' => 'alert'
        ], ['×']) : null,
        $message
    ]);
}

// Usage
echo alert('Operation successful!', 'success', true);
echo alert('Please fill all fields.', 'warning');
```

### Form Field Component

```php
function formField(string $label, string $name, string $type = 'text', array $attrs = []) {
    return _div('class="form-group"', [
        _label(['for' => $name], [$label]),
        _input(array_merge([
            'type' => $type,
            'id' => $name,
            'name' => $name,
            'class' => 'form-control'
        ], $attrs))
    ]);
}

// Usage
echo formField('Email', 'email', 'email', ['required' => true, 'placeholder' => 'you@example.com']);
echo formField('Password', 'password', 'password', ['required' => true]);
```

### Icon Component

```php
function icon(string $name, int $size = 24) {
    $icons = [
        'check' => 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z',
        'close' => 'M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z',
        'menu' => 'M3 18h18v-2H3v2zm0-5h18v-2H3v13zm0-7v2h18V6H3z',
    ];
    
    return _svg([
        'width' => $size,
        'height' => $size,
        'viewBox' => '0 0 24 24',
        'class' => "icon icon-{$name}",
        'fill' => 'currentColor'
    ], [
        _path(['d' => $icons[$name] ?? ''])
    ]);
}

// Usage
echo _button('class="btn-icon"', [
    icon('close', 20),
    _span(['Close'])
]);
```

## Complete Examples

### Full HTML Page

```php
use function IceTea\IceDOM\{
    _html, _head, _meta, _title, _link, _body, 
    _header, _nav, _a, _ul, _li, _main, _h1, _p, _footer, _script
};

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
                    _li([_a(['href' => '/about'], ['About'])]),
                    _li([_a(['href' => '/services'], ['Services'])]),
                    _li([_a(['href' => '/contact'], ['Contact'])])
                ])
            ])
        ]),
        
        _main('class="content container"', [
            _h1(['Welcome to My Website']),
            _p(['This is a complete HTML page built with IceDOM.']),
            _p(['All content is type-safe and automatically escaped.'])
        ]),
        
        _footer('class="site-footer"', [
            _p(['© 2024 MySite. All rights reserved.'])
        ]),
        
        _script(['src' => '/js/app.js'])
    ])
]);

echo $page;
```

### Product Grid

```php
$products = [
    ['name' => 'Laptop', 'price' => 999, 'image' => 'laptop.jpg', 'featured' => true],
    ['name' => 'Mouse', 'price' => 29, 'image' => 'mouse.jpg', 'featured' => false],
    ['name' => 'Keyboard', 'price' => 79, 'image' => 'keyboard.jpg', 'featured' => true],
];

$grid = _div('class="product-grid"')->map($products, function($product) {
    return _div([
        'class' => 'product-card',
        'data-product-id' => $product['name']
    ], [
        _if($product['featured'])
            (_span('class="badge-featured"', ['Featured']))
        ->else()
            (null),
            
        _img([
            'src' => "/images/{$product['image']}", 
            'alt' => $product['name'],
            'class' => 'product-image'
        ]),
        
        _div('class="product-info"', [
            _h3('class="product-name"', [$product['name']]),
            _p('class="product-price"', ['$' . number_format($product['price'], 2)]),
            btn('Add to Cart', 'primary', ['data-product' => $product['name']])
        ])
    ]);
});

echo $grid;
```

### Registration Form with Validation

```php
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
        _if(isset($errors['email']))
            (_span('class="error-message"', [$errors['email']]))
        ->else()
            (null)
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
    ]),
    
    // Confirm password
    _div('class="form-group"', [
        _label(['for' => 'password_confirm'], ['Confirm Password']),
        _input([
            'type' => 'password',
            'id' => 'password_confirm',
            'name' => 'password_confirm',
            'class' => 'form-control',
            'required' => true
        ])
    ]),
    
    // Terms checkbox
    _div('class="form-check"', [
        _input([
            'type' => 'checkbox',
            'id' => 'terms',
            'name' => 'terms',
            'class' => 'form-check-input',
            'required' => true
        ]),
        _label('class="form-check-label" for="terms"', [
            'I agree to the ',
            _a(['href' => '/terms'], ['Terms of Service'])
        ])
    ]),
    
    _div('class="form-actions"', [
        btn('Create Account', 'primary', ['type' => 'submit']),
        ' ',
        _a(['href' => '/login', 'class' => 'btn-link'], ['Already have an account?'])
    ])
]);

echo $form;
```

### Data Table with Actions

```php
$users = [
    ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'Admin', 'active' => true],
    ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'User', 'active' => true],
    ['id' => 3, 'name' => 'Bob Wilson', 'email' => 'bob@example.com', 'role' => 'User', 'active' => false],
];

$table = _div('class="table-responsive"', [
    _table('class="table table-striped"', [
        _thead([
            _tr([
                _th(['ID']),
                _th(['Name']),
                _th(['Email']),
                _th(['Role']),
                _th(['Status']),
                _th(['Actions'])
            ])
        ]),
        _tbody()->map($users, function($user) {
            $roleClass = $user['role'] === 'Admin' ? 'danger' : 'primary';
            $statusClass = $user['active'] ? 'success' : 'secondary';
            
            return _tr([
                _td([$user['id']]),
                _td([$user['name']]),
                _td([$user['email']]),
                _td([badge($user['role'], $roleClass)]),
                _td([badge($user['active'] ? 'Active' : 'Inactive', $statusClass)]),
                _td('class="actions"', [
                    _a([
                        'href' => "/users/{$user['id']}/edit",
                        'class' => 'btn-sm btn-outline'
                    ], ['Edit']),
                    ' ',
                    _button([
                        'type' => 'button',
                        'class' => 'btn-sm btn-danger',
                        'onclick' => "deleteUser({$user['id']})"
                    ], ['Delete'])
                ])
            ]);
        })
    ])
]);

echo $table;
```

### Dashboard Layout

```php
function statCard(string $title, $value, string $icon, string $trend = null) {
    return _div('class="stat-card"', [
        _div('class="stat-header"', [
            icon($icon, 32),
            _span('class="stat-title"', [$title])
        ]),
        _div('class="stat-value"', [$value]),
        $trend ? _div("class=\"stat-trend {$trend}\"", [
            $trend === 'up' ? '↑' : '↓',
            ' 12% from last month'
        ]) : null
    ]);
}

$dashboard = _div('class="dashboard"', [
    _h1(['Dashboard']),
    
    // Stats grid
    _div('class="stats-grid"', [
        statCard('Total Users', '1,234', 'users', 'up'),
        statCard('Revenue', '$45,678', 'dollar', 'up'),
        statCard('Orders', '892', 'cart', 'down'),
        statCard('Products', '156', 'box', null)
    ]),
    
    // Recent activity
    _div('class="recent-activity"', [
        _h2(['Recent Activity']),
        _ul('class="activity-list"')->map([
            'John Doe registered',
            'New order #1234 placed',
            'Product "Laptop" updated',
            'Jane Smith logged in'
        ], fn($activity) => 
            _li('class="activity-item"', [$activity])
        )
    ])
]);

echo $dashboard;
```

## API Reference

### Helper Functions

#### `_safe(string $html): SafeString`
Marks HTML as safe (won't be escaped).

```php
_div([_safe('<strong>Bold</strong>')]);
// Output: <div><strong>Bold</strong></div>
```

#### `_raw(...$children): RawNode`
Creates a raw node (no escaping or spacing).

```php
_raw('<b>Bold</b>', ' ', '<i>Italic</i>');
// Output: <b>Bold</b> <i>Italic</i>
```

#### `_slot(?Closure $slotFunction = null): SlotNode`
Creates a slot for dynamic content.

```php
$slot = _slot(fn() => date('Y-m-d H:i:s'));
```

#### `_if($condition): IfElseNode`
Conditional rendering.

```php
_if($loggedIn)('Welcome')->else()('Login');
```

#### `_echo(...$children): EchoNode`
Captures output buffer content.

```php
_echo(function() {
    include 'template.php';
});
```

#### `_h(string $tagName, $arg = null): HtmlNode`
Creates custom HTML elements.

```php
_h('custom-element', ['data-value' => '123']);
// Output: <custom-element data-value="123"></custom-element>
```

#### `clsf(string $format, ...$args): string`
Conditional class formatter (returns empty string if any arg is null).

```php
clsf('btn-%s btn-%s', 'primary', 'large');  // "btn-primary btn-large"
clsf('btn-%s btn-%s', null, 'large');        // ""
```

### Core Classes

#### `Node`
Base class for all nodes.

**Methods:**
- `appendChild($child): static` - Add a child
- `appendChildren(array $children): static` - Add multiple children
- `clearChildren(): static` - Remove all children
- `getChildren(): array` - Get all children
- `setParent(?Node $parent): void` - Set parent node
- `getParent(): ?Node` - Get parent node
- `use(?Closure $hook): static` - Apply function to node
- `map(iterable $arr, $mapFunction = null): static` - Map array to children
- `__invoke(...$children): static` - Add children via function call

#### `HtmlNode extends Node`
Represents an HTML element.

**Methods:**
- `setAttribute(string $key, $value): static` - Set attribute
- `getAttribute(string $key, $default = null): mixed` - Get attribute
- `id(string $id): static` - Set id attribute
- `classes(...$classes): static` - Set CSS classes
- `toArray(): array` - Convert to array representation
- Magic methods for attributes: `$node->href('#')` sets href

#### `HtmlDocument extends HtmlNode`
Complete HTML document with DOCTYPE.

```php
echo _html('lang="en"', [_head(...), _body(...)]);
// Output: <!DOCTYPE html>\n<html lang="en">...</html>
```

#### `SafeString`
Marks string as safe HTML.

```php
new SafeString('<b>Bold</b>')
```

#### `SlotNode extends Node`
Dynamic content slot with fallback.

```php
new SlotNode(['fallback'], fn() => 'dynamic')
```

#### `IfElseNode extends Node`
Conditional rendering node.

```php
new IfElseNode(['true content'], ['false content'], $condition)
```

#### `EchoNode extends Node`
Captures output buffer.

```php
new EchoNode([fn() => include 'template.php'])
```

#### `RawNode extends Node`
Raw content without escaping.

```php
new RawNode(['<b>raw</b>', '<i>html</i>'])
```

#### `ArrayMap`
Maps iterable to nodes.

```php
new ArrayMap($items, fn($item) => _li([$item]))
```

## Best Practices

### ✅ DO: Always Escape User Input

```php
// Good - automatic escaping
_div([$userInput]);

// Good - for trusted HTML only
_div([_safe($sanitizedHtml)]);
```

### ❌ DON'T: Use _raw() with User Input

```php
// DANGEROUS - XSS vulnerability!
_raw($_POST['content']);

// Safe alternative
_div([$_POST['content']]);
```

### ✅ DO: Use Invoke Syntax for Readability

```php
// Recommended - clean and HTML-like
_div('class="card"', [
    _h3(['Title']),
    _p(['Content'])
]);

// Also works but more verbose
_div(['class' => 'card'], [
    _h3(['Title']),
    _p(['Content'])
]);
```

### ✅ DO: Use map() for Dynamic Lists

```php
// Recommended
_ul('class="menu"')->map($items, fn($item) => 
    _li([$item])
);

// Avoid manual loops
$items = [];
foreach ($data as $item) {
    $items[] = _li([$item]);
}
_ul('class="menu"', $items);
```

### ✅ DO: Extract Reusable Components

```php
function alertBox(string $message, string $type = 'info') {
    return _div("class=\"alert alert-{$type}\"", [$message]);
}

echo alertBox('Success!', 'success');
```

### ✅ DO: Use Type Hints

```php
use IceTea\IceDOM\HtmlNode;

function createCard(string $title, string $content): HtmlNode {
    return _div('class="card"', [
        _h3([$title]),
        _p([$content])
    ]);
}
```

### ✅ DO: Chain Methods Fluently

```php
_div()
    ->id('container')
    ->classes('active', 'shadow')
    ('Content')
    ->setAttribute('data-role', 'main');
```

### ✅ DO: Use Conditional Rendering

```php
// Recommended - explicit
_if($showButton)
    (btn('Click'))
->else()
    (null);

// Avoid ternary in arrays
[$showButton ? btn('Click') : null];
```

### ✅ DO: Prefer Array Initialization for Static Lists

```php
// Faster - array is initialized once
_ul('class="menu"', [
    _li(['Item 1']),
    _li(['Item 2']),
    _li(['Item 3'])
]);

// Slower - multiple method calls
_ul('class="menu"')(
    _li(['Item 1']),
    _li(['Item 2']),
    _li(['Item 3'])
);
```

### ✅ DO: Use Invoke When Chaining Methods

```php
// Recommended - more readable
_ul('class="menu"')
    ->id('main-menu')
    ->data_role('navigation')
    ->(
        _li(['Item 1']),
        _li(['Item 2'])
    );
```

### ❌ DON'T: Use null as First Argument

```php
// NOT RECOMMENDED - avoid null as first arg
_div(null, ['child1', 'child2'])('child3');
_tr(null, [_td(['data'])]);

// RECOMMENDED - use empty args then invoke or magic call
_div()('child1', 'child2', 'child3');
_tr()(_td(['data']));

// RECOMMENDED - or use list array for children
_div(['child1', 'child2', 'child3']);
_tr([_td(['data'])]);

// RECOMMENDED - or string for attributes then children array
_div('class="box"', ['child1', 'child2', 'child3']);
_tr('class="highlight"', [_td(['data'])]);
```

### ❌ DON'T: Use Mixed Key Array for Attributes

```php
// NOT RECOMMENDED - mixed key array (numbered + associative)
_div(['class="container" data-show="1"', 'align' => 'left']);

// RECOMMENDED - use string for raw attributes
_div('class="container" data-show="1"')
    ->setAttribute('align', 'left');

// RECOMMENDED - or use setAttribute/magic methods
_div()
    ->setAttribute('class', 'container')
    ->setAttribute('data-show', '1')
    ->setAttribute('align', 'left');

// RECOMMENDED - or use associative array only
_div(['class' => 'container', 'data-show' => '1', 'align' => 'left']);
```

---

## Additional Resources

- **GitHub Repository**: [github.com/icetea/icedom](https://github.com/icetea/icedom)
- **Issue Tracker**: [github.com/icetea/icedom/issues](https://github.com/icetea/icedom/issues)
- **License**: MIT

---

**Happy coding with IceDOM!** 🎉
