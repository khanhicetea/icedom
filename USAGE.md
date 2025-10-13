# IceDOM Usage Guide

IceDOM is a pure PHP library for building HTML documents using a Virtual DOM-like approach. It provides a fluent, type-safe API for constructing HTML without templates.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Core Concepts](#core-concepts)
- [HTML Tag Helper Functions](#html-tag-helper-functions)
- [Core Classes](#core-classes)
- [Advanced Features](#advanced-features)
- [Examples](#examples)

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

echo $card; // Outputs HTML
```

## Core Concepts

### 1. Everything is a Node

All HTML elements extend from the `Node` class, which provides common functionality like:
- Parent-child relationships
- String conversion with HTML escaping
- Fluent method chaining

### 2. Three Ways to Add Content

IceDOM supports multiple syntaxes for flexibility:

#### A. Using List Arrays (for children only)

```php
_div(['Hello World']);
// <div>Hello World</div>
```

#### B. Using Attributes + Children Arrays

```php
_div(['class' => 'box'], ['Hello World']);
// <div class="box">Hello World</div>
```

#### C. Using Shorthand + Invoke (most concise)

```php
_div('class="box" id="main"')('Hello World');
// <div class="box" id="main">Hello World</div>
```

### 3. Automatic HTML Escaping

All string content is automatically escaped to prevent XSS attacks:

```php
_div(['<script>alert("xss")</script>']);
// <div>&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;</div>
```

To output raw HTML, use `_raw()` or `_safe()`:

```php
_div([_safe('<b>Bold</b>')]);
// <div><b>Bold</b></div>
```

## HTML Tag Helper Functions

### Primitive Helper Functions

#### `_raw(...$children)` - Output raw HTML

```php
_raw('<b>Bold</b>', ' ', '<i>Italic</i>');
// <b>Bold</b> <i>Italic</i>
```

#### `_safe($string)` - Mark string as safe HTML

```php
_div([_safe('<span>Safe HTML</span>')]);
// <div><span>Safe HTML</span></div>
```

#### `_slot($slotFunction = null)` - Create a slot for dynamic content

```php
$slot = _slot(function() {
    return 'Dynamic content';
});
echo $slot; // Dynamic content
```

#### `_if($condition)` - Conditional rendering

```php
_if($isLoggedIn)
    ('Welcome back!')
->else()
    ('Please login');
```

#### `_echo(...$children)` - Capture output

```php
_echo(function() {
    echo "Hello";
    echo " World";
});
// Hello World
```

#### `_h($tagName, $arg = null)` - Create custom HTML tag

```php
_h('custom-element', ['data-value' => '123']);
// <custom-element data-value="123"></custom-element>
```

#### `clsf($format, ...$args)` - Conditional class formatter

```php
clsf('btn-%s btn-%s', 'primary', 'large');
// btn-primary btn-large

clsf('btn-%s btn-%s', null, null);
// (empty string)
```

### Common HTML Tags

All standard HTML5 tags are available as helper functions. Here are the most commonly used:

#### Container Elements

```php
_div(['class' => 'container'], [...]);
_span(['class' => 'badge'], ['New']);
_p(['Lead paragraph text']);
```

#### Headings

```php
_h1(['Page Title']);
_h2(['Section Title']);
_h3(['Subsection']);
// ... _h4, _h5, _h6
```

#### Links and Buttons

```php
_a(['href' => '/about', 'target' => '_blank'], ['About Us']);

_button(['type' => 'submit', 'class' => 'btn'], ['Submit']);
```

#### Forms

```php
_form(['action' => '/login', 'method' => 'post'], [
    _label(['for' => 'email'], ['Email:']),
    _input(['type' => 'email', 'id' => 'email', 'name' => 'email']),
    
    _label(['for' => 'password'], ['Password:']),
    _input(['type' => 'password', 'id' => 'password', 'name' => 'password']),
    
    _button(['type' => 'submit'], ['Login']),
]);
```

#### Lists

```php
_ul(['class' => 'menu'], [
    _li(['Home']),
    _li(['About']),
    _li(['Contact']),
]);

_ol(['class' => 'steps'], [
    _li(['First step']),
    _li(['Second step']),
]);
```

#### Tables

```php
_table(['class' => 'data-table'], [
    _thead(null, [
        _tr(null, [
            _th(['Name']),
            _th(['Age']),
        ]),
    ]),
    _tbody(null, [
        _tr(null, [
            _td(['John']),
            _td(['30']),
        ]),
    ]),
]);
```

#### Semantic HTML

```php
_header(['class' => 'site-header'], [...]);
_nav(['class' => 'main-nav'], [...]);
_main(['class' => 'content'], [...]);
_article(['class' => 'post'], [...]);
_section(['class' => 'features'], [...]);
_aside(['class' => 'sidebar'], [...]);
_footer(['class' => 'site-footer'], [...]);
```

#### Text Formatting

```php
_strong(['Important text']);
_em(['Emphasized text']);
_b(['Bold text']);
_i(['Italic text']);
_u(['Underlined text']);
_small(['Fine print']);
_mark(['Highlighted']);
_code(['console.log()']);
_pre(['Preformatted text']);
```

#### Void Elements (self-closing)

```php
_br();
_hr();
_img(['src' => 'photo.jpg', 'alt' => 'Description']);
_input(['type' => 'text', 'name' => 'username']);
_link(['rel' => 'stylesheet', 'href' => 'style.css']);
_meta(['name' => 'viewport', 'content' => 'width=device-width']);
```

#### SVG Elements

```php
_svg(['width' => '100', 'height' => '100'], [
    _circle(['cx' => '50', 'cy' => '50', 'r' => '40', 'fill' => 'blue']),
    _rect(['x' => '10', 'y' => '10', 'width' => '80', 'height' => '80']),
    _path(['d' => 'M 10 10 L 90 90']),
]);
```

## Core Classes

### Node

The base class for all nodes. Provides core functionality.

```php
use IceTea\IceDOM\Node;

$node = new Node(['Hello', ' World']);
echo $node; // Hello World
```

**Key Methods:**

- `appendChild($child)` - Add a child node
- `getChildren()` - Get all children
- `clearChildren()` - Remove all children
- `setParent($parent)` - Set parent node
- `getParent()` - Get parent node
- `use(Closure $hook)` - Apply a function to modify the node
- `map($iterable, $mapFunction = null)` - Create an ArrayMap

### HtmlNode

Represents an HTML element with tag name, attributes, and children.

```php
use IceTea\IceDOM\HtmlNode;

$div = new HtmlNode(
    children: ['Content'],
    tagName: 'div',
    attrs: ['class' => 'box', 'id' => 'main'],
    isVoid: false
);

echo $div;
// <div class="box" id="main">Content</div>
```

**Creating with Factory Method:**

```php
// Using tag() static method
$div = HtmlNode::tag('div', ['class' => 'box'], ['Content']);
```

**Key Methods:**

- `setAttribute($key, $value)` - Set an attribute
- `getAttribute($key, $default = null)` - Get an attribute
- `id($id)` - Set the id attribute
- `classes(...$classes)` - Set class names
- `appendChild($child)` - Add a child (throws error for void elements)

**Magic Methods:**

```php
// Set attributes using method calls
$link = new HtmlNode([], 'a');
$link->href('/about')->title('About Us')->target('_blank');

// Get attributes using property access
echo $link->href; // /about
```

**Class Management:**

```php
// Multiple classes
$div->classes('active', 'primary');

// Array of classes
$div->classes(['active', 'primary']);

// Conditional classes
$div->classes([
    'active' => true,
    'disabled' => false,
    'primary' => $isPrimary,
]);
```

### HtmlDocument

Creates a complete HTML document with DOCTYPE.

```php
use function IceTea\IceDOM\{_html, _head, _title, _body, _h1};

$doc = _html(['lang' => 'en'], [
    _head(null, [
        _title(['My Website']),
    ]),
    _body(null, [
        _h1(['Welcome']),
    ]),
]);

echo $doc;
/*
<!DOCTYPE html>
<html lang="en"><head><title>My Website</title></head><body><h1>Welcome</h1></body></html>
*/
```

### RawNode

Outputs content without HTML escaping or spacing.

```php
use IceTea\IceDOM\RawNode;

$raw = new RawNode(['<b>Bold</b>', '<i>Italic</i>']);
echo $raw; // <b>Bold</b><i>Italic</i>
```

**Use Cases:**

- Pre-escaped HTML content
- Trusted HTML from database
- HTML from markdown parsers
- SVG content

**Warning:** Never use RawNode with user input - it bypasses XSS protection!

```php
// ❌ DANGEROUS - Never do this!
$raw = new RawNode([$_POST['content']]);

// ✅ SAFE - Use regular Node instead
$node = new Node([$_POST['content']]);
```

### SafeString

Marks a string as safe HTML that shouldn't be escaped.

```php
use IceTea\IceDOM\SafeString;

$safe = new SafeString('<b>Bold</b>');
$div = _div([$safe]);
echo $div; // <div><b>Bold</b></div>
```

**Difference from RawNode:**

- `RawNode`: Container for multiple raw HTML fragments, no spacing
- `SafeString`: Single safe string value, follows normal Node spacing rules

### SlotNode

A slot for dynamic or lazy-evaluated content.

```php
use IceTea\IceDOM\SlotNode;

// With default content
$slot = new SlotNode(['Default content']);

// With slot function (takes priority over children)
$slot = new SlotNode(
    children: ['Default'],
    slotFunction: function() {
        return 'Dynamic content';
    }
);

echo $slot; // Dynamic content
```

**Use Cases:**

```php
// Lazy evaluation
$expensiveSlot = _slot(function() {
    // Only runs when slot is converted to string
    return expensiveOperation();
});

// Conditional slot
$userSlot = _slot(function() use ($user) {
    return $user->isLoggedIn() 
        ? "Welcome {$user->name}"
        : 'Please login';
});

// Template pattern with default
function card($title, $content = null) {
    return _div(['class' => 'card'], [
        _div(['class' => 'card-header'], [$title]),
        _slot($content) // Falls back to empty if null
    ]);
}
```

### IfElseNode

Conditional rendering with if/elseif/else support.

```php
use IceTea\IceDOM\IfElseNode;

$node = new IfElseNode(
    children: ['Content if true'],
    elseChildren: ['Content if false'],
    condition: $isLoggedIn
);
```

**Fluent API:**

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

**With Closures:**

```php
_if(fn() => $user->isLoggedIn())
    (fn() => "Welcome, {$user->name}")
->else()
    ('Please login');
```

**Complex Conditions:**

```php
$statusBadge = _if($order->status === 'completed')
    (_span(['class' => 'badge-success'], ['Completed']))
->else if($order->status === 'pending')
    (_span(['class' => 'badge-warning'], ['Pending']))
->else if($order->status === 'cancelled')
    (_span(['class' => 'badge-danger'], ['Cancelled']))
->else()
    (_span(['class' => 'badge-secondary'], ['Unknown']));
```

**Lazy Evaluation:**

Conditions are re-evaluated on each render, and only the matched branch is executed:

```php
$counter = 0;

$node = _if(true)
    (function() use (&$counter) {
        $counter++;
        return 'True branch';
    })
->else()
    (function() {
        throw new Exception('Never executed!');
    });

echo $node; // True branch (counter = 1)
echo $node; // True branch (counter = 2)
```

### EchoNode

Captures output from closures using output buffering.

```php
use IceTea\IceDOM\EchoNode;

$echo = new EchoNode([
    function() {
        echo "Hello";
        echo " World";
    }
]);

echo $echo; // Hello World
```

**Use Cases:**

```php
// Include PHP template fragments
$content = _echo(function() {
    include 'fragment.php';
});

// Capture legacy code output
$legacy = _echo(function() {
    legacy_function_that_echoes();
});

// Mix echo and return
$mixed = _echo(
    'Direct string',
    function() {
        echo 'Echoed ';
        return 'Returned ';
    },
    function() {
        print 'Printed';
    }
);
// Direct string Echoed Returned Printed
```

**Important:** Content is NOT escaped, so be careful with user input!

### ArrayMap

Maps an iterable to HTML nodes.

```php
use IceTea\IceDOM\ArrayMap;

$users = ['Alice', 'Bob', 'Charlie'];

$list = new ArrayMap($users, function($name) {
    return _li([$name]);
});

$ul = _ul(null, [$list]);
// <ul><li>Alice</li><li>Bob</li><li>Charlie</li></ul>
```

**Using Node::map():**

```php
$ul = _ul(null, [
    (new Node)->map($users, fn($name) => _li([$name]))
]);
```

**With Associative Arrays:**

```php
$data = ['name' => 'John', 'age' => 30, 'city' => 'NYC'];

$dl = _dl(null, [
    (new Node)->map($data, function($value, $key) {
        return [
            _dt([ucfirst($key)]),
            _dd([$value]),
        ];
    })
]);
```

**Without Mapping Function:**

```php
// Direct rendering
$list = new ArrayMap(['Item 1', 'Item 2', 'Item 3']);
echo $list; // Item 1 Item 2 Item 3
```

## Advanced Features

### Method Chaining

All methods return `$this` for fluent chaining:

```php
_div()
    ->id('container')
    ->classes('active', 'primary')
    ->setAttribute('data-role', 'main')
    ->appendChild('Content')
    ->use(function($node) {
        $node->setAttribute('data-count', count($node->getChildren()));
    });
```

### The `use()` Method

Apply transformations to nodes:

```php
$div = _div(['Hello'])
    ->use(function($node) {
        $node->setAttribute('data-length', strlen($node->__toString()));
        return $node;
    });
```

### Closures as Children

Children can be closures that receive the node:

```php
_div(['class' => 'card'], [
    'Item 1',
    'Item 2',
    fn($node) => 'Total items: ' . count($node->getChildren())
]);
```

### Closures as Attributes

Attributes can be closures that receive the node:

```php
_div([
    'class' => 'box',
    'data-count' => fn($node) => count($node->getChildren())
], [
    'Child 1',
    'Child 2',
]);
// <div class="box" data-count="2">Child 1 Child 2</div>
```

### Parent-Child Relationships

```php
$parent = _div(['Parent']);
$child = _span(['Child']);

$parent->appendChild($child);

echo $child->getParent() === $parent; // true
```

### Boolean Attributes

```php
_input(['type' => 'checkbox', 'checked' => true]);
// <input type="checkbox" checked>

_input(['type' => 'checkbox', 'checked' => false]);
// <input type="checkbox">

_button(['disabled' => true], ['Save']);
// <button disabled>Save</button>
```

### Shorthand Attributes with `_` Key

```php
_div([
    'class' => 'box',
    '_' => 'data-custom="value"'
]);
// <div class="box" data-custom="value"></div>

// Or using string first argument:
_div('data-custom="value"');
// <div data-custom="value"></div>
```

## Examples

### Complete Page

```php
$page = _html(['lang' => 'en'], [
    _head(null, [
        _meta(['charset' => 'UTF-8']),
        _meta(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0']),
        _title(['My Website']),
        _link(['rel' => 'stylesheet', 'href' => '/css/style.css']),
    ]),
    _body(null, [
        _header(['class' => 'site-header'], [
            _nav(['class' => 'navbar'], [
                _a(['href' => '/', 'class' => 'logo'], ['MySite']),
                _ul(['class' => 'nav-menu'], [
                    _li(null, [_a(['href' => '/'], ['Home'])]),
                    _li(null, [_a(['href' => '/about'], ['About'])]),
                    _li(null, [_a(['href' => '/contact'], ['Contact'])]),
                ]),
            ]),
        ]),
        _main(['class' => 'content'], [
            _h1(['Welcome to My Website']),
            _p(['This is a demo page built with IceDOM.']),
        ]),
        _footer(['class' => 'site-footer'], [
            _p(['© 2024 MySite. All rights reserved.']),
        ]),
        _script(['src' => '/js/app.js']),
    ]),
]);

echo $page;
```

### Dynamic List

```php
$items = ['Apple', 'Banana', 'Cherry', 'Date'];

$list = _ul(['class' => 'fruit-list'], [
    (new Node)->map($items, function($fruit, $index) {
        return _li(['class' => 'fruit-item'], [
            _strong([($index + 1) . '. ']),
            _span([$fruit]),
        ]);
    })
]);
```

### Card Grid

```php
$products = [
    ['name' => 'Product 1', 'price' => '$99', 'image' => 'p1.jpg'],
    ['name' => 'Product 2', 'price' => '$149', 'image' => 'p2.jpg'],
    ['name' => 'Product 3', 'price' => '$199', 'image' => 'p3.jpg'],
];

$grid = _div(['class' => 'product-grid'], [
    (new Node)->map($products, function($product) {
        return _div(['class' => 'card'], [
            _img(['src' => "/images/{$product['image']}", 'alt' => $product['name']]),
            _div(['class' => 'card-body'], [
                _h3(['class' => 'card-title'], [$product['name']]),
                _p(['class' => 'card-price'], [$product['price']]),
                _button(['class' => 'btn-primary'], ['Add to Cart']),
            ]),
        ]);
    })
]);
```

### Form with Validation

```php
$errors = ['email' => 'Invalid email address'];

$form = _form(['action' => '/register', 'method' => 'post'], [
    _div(['class' => 'form-group'], [
        _label(['for' => 'email'], ['Email Address']),
        _input([
            'type' => 'email',
            'id' => 'email',
            'name' => 'email',
            'class' => isset($errors['email']) ? 'input-error' : ''
        ]),
        _if(isset($errors['email']))
            (_span(['class' => 'error-message'], [$errors['email']]))
        ->else()
            (null),
    ]),
    
    _div(['class' => 'form-group'], [
        _label(['for' => 'password'], ['Password']),
        _input(['type' => 'password', 'id' => 'password', 'name' => 'password']),
    ]),
    
    _button(['type' => 'submit', 'class' => 'btn-primary'], ['Register']),
]);
```

### Conditional User Menu

```php
function userMenu($user) {
    return _div(['class' => 'user-menu'], [
        _if($user->isLoggedIn())
            (_div(['class' => 'user-info'], [
                _img(['src' => $user->avatar, 'alt' => $user->name, 'class' => 'avatar']),
                _span(['class' => 'username'], [$user->name]),
                _if($user->isAdmin())
                    (_a(['href' => '/admin', 'class' => 'admin-link'], ['Admin Panel']))
                ->else()
                    (null),
                _a(['href' => '/logout'], ['Logout']),
            ]))
        ->else()
            (_div(['class' => 'auth-info'], [
                _a(['href' => '/login', 'class' => 'btn'], ['Login']),
                _a(['href' => '/register', 'class' => 'btn-primary'], ['Register']),
            ]))
    ]);
}
```

### Reusable Components

```php
function button($text, $variant = 'primary', $attrs = []) {
    $defaultAttrs = [
        'type' => 'button',
        'class' => "btn btn-{$variant}"
    ];
    
    return _button(array_merge($defaultAttrs, $attrs), [$text]);
}

function card($title, $content, $footer = null) {
    return _div(['class' => 'card'], [
        _div(['class' => 'card-header'], [
            _h3(['class' => 'card-title'], [$title])
        ]),
        _div(['class' => 'card-body'], [
            $content
        ]),
        _if($footer !== null)
            (_div(['class' => 'card-footer'], [$footer]))
        ->else()
            (null)
    ]);
}

// Usage
$myCard = card(
    'Welcome',
    _p(['This is the card content.']),
    button('Learn More', 'secondary')
);
```

### Table with Dynamic Data

```php
$users = [
    ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'Admin'],
    ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'User'],
    ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'role' => 'User'],
];

$table = _table(['class' => 'data-table'], [
    _thead(null, [
        _tr(null, [
            _th(['ID']),
            _th(['Name']),
            _th(['Email']),
            _th(['Role']),
            _th(['Actions']),
        ])
    ]),
    _tbody(null, [
        (new Node)->map($users, function($user) {
            return _tr(null, [
                _td([$user['id']]),
                _td([$user['name']]),
                _td([$user['email']]),
                _td([
                    _span([
                        'class' => clsf(
                            'badge badge-%s', 
                            $user['role'] === 'Admin' ? 'danger' : 'primary'
                        )
                    ], [$user['role']])
                ]),
                _td([
                    _a(['href' => "/users/{$user['id']}/edit", 'class' => 'btn-sm'], ['Edit']),
                    _a(['href' => "/users/{$user['id']}/delete", 'class' => 'btn-sm btn-danger'], ['Delete']),
                ]),
            ]);
        })
    ])
]);
```

### SVG Icon Component

```php
function icon($name, $size = 24) {
    $icons = [
        'check' => 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z',
        'close' => 'M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z',
        'menu' => 'M3 18h18v-2H3v2zm0-5h18v-2H3V13zm0-7v2h18V6H3z',
    ];
    
    return _svg([
        'width' => $size,
        'height' => $size,
        'viewBox' => '0 0 24 24',
        'class' => "icon icon-{$name}"
    ], [
        _path(['d' => $icons[$name] ?? '', 'fill' => 'currentColor'])
    ]);
}

// Usage
$closeButton = _button(['class' => 'btn-icon'], [
    icon('close', 20),
    _span(['Close'])
]);
```

---

## Best Practices

### 1. Always Escape User Input

```php
// ✅ Good - automatic escaping
_div([$userInput]);

// ❌ Bad - bypasses escaping
_raw($userInput);
```

### 2. Use Type Hints

```php
use IceTea\IceDOM\HtmlNode;

function createCard(string $title, string $content): HtmlNode {
    return _div(['class' => 'card'], [
        _h3([$title]),
        _p([$content]),
    ]);
}
```

### 3. Extract Reusable Components

```php
// Instead of repeating the same structure
function badge(string $text, string $variant = 'primary'): HtmlNode {
    return _span(['class' => "badge badge-{$variant}"], [$text]);
}
```

### 4. Use Conditional Rendering

```php
// Instead of ternary in arrays
_if($showButton)
    (button('Click Me'))
->else()
    (null)

// Not this:
[$showButton ? button('Click Me') : null]
```

### 5. Leverage ArrayMap for Lists

```php
// Clean and readable
(new Node)->map($items, fn($item) => _li([$item]))

// Instead of:
array_map(fn($item) => _li([$item]), $items)
```

---

For more examples and updates, visit the [GitHub repository](https://github.com/icetea/icedom).

