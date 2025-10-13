# VDom - Virtual DOM System Design

## Overview

**IceTea VDom** is a PHP-based Virtual DOM library that provides an object-oriented, functional approach to building HTML documents. It enables developers to construct HTML programmatically with type safety, XSS protection, and a fluent API interface.

**Version**: 1.0  
**PHP Requirement**: >= 8.1  
**License**: MIT  
**Namespace**: `IceTea\IceDOM`

---

## Core Principles

1. **Type Safety**: Leverage PHP 8.1+ type system for compile-time checks
2. **XSS Protection**: Automatic HTML escaping by default, with explicit opt-out
3. **Fluent API**: Method chaining for intuitive HTML construction
4. **Composability**: Tree-based structure allows component composition
5. **Flexibility**: Support for closures, conditional rendering, and dynamic content
6. **Performance**: String-based rendering with optional buffering (planned)

---

## System Architecture

### Architecture Diagram

```
┌─────────────────────────────────────────────────────┐
│                   VDom System                       │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌──────────────┐                                  │
│  │     Node     │ (Abstract Base)                  │
│  │  (Abstract)  │                                  │
│  └──────┬───────┘                                  │
│         │                                           │
│    ┌────┴────────────────────────────┐             │
│    │                                 │             │
│ ┌──▼──────┐                  ┌──────▼────────┐    │
│ │HtmlNode │                  │ Specialized   │    │
│ │         │                  │    Nodes      │    │
│ └──┬──────┘                  └───────┬───────┘    │
│    │                                 │             │
│    │                         ┌───────┴─────────┐  │
│ ┌──▼────────┐               │  - ArrayMapNode │  │
│ │HtmlDocument│               │  - IfElseNode   │  │
│ └───────────┘                │  - SlotNode     │  │
│                               │  - RawNode      │  │
│                               │  - EchoNode     │  │
│                               └─────────────────┘  │
│                                                     │
│  ┌──────────────┐  ┌──────────────┐               │
│  │  SafeString  │  │   HtmlRef    │               │
│  │   (Utility)  │  │   (Utility)  │               │
│  └──────────────┘  └──────────────┘               │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Node Hierarchy

```
Node (abstract)
├── HtmlNode
│   └── HtmlDocument
├── ArrayMapNode
├── IfElseNode
├── SlotNode
├── RawNode
└── EchoNode
```

---

## Core Data Structures

### 1. Node (Abstract Base Class)

**Purpose**: Foundation for all node types in the VDom tree.

**Key Properties**:
```php
protected ?Node $parent;        // Reference to parent node
protected array $children;      // Array of child nodes
const ENT_FLAGS;               // HTML entity encoding flags
```

**Key Methods**:
- `appendChild($child)`: Add a single child node
- `appendChildren(array $children)`: Add multiple children
- `use(Closure $hook)`: Execute a closure on the node itself
- `childrenUse(Closure $hook)`: Execute a closure on all children
- `map(iterable $arr, $mapFunction)`: Map over an array to create child nodes
- `childrenToString()`: Convert all children to HTML string
- `tryEvalClosure($value)`: Evaluate closures with context
- `__invoke(...$children)`: Fluent interface for adding children

**Rendering Logic**:
```php
protected function childrenToString(): string
{
    // 1. Filter null children
    // 2. Evaluate closures
    // 3. Process Node instances recursively
    // 4. HTML-escape strings
    // 5. Pass through SafeString and numeric values
    // 6. Handle Stringable objects
    // 7. Join with spaces
}
```

---

### 2. HtmlNode

**Purpose**: Represents standard HTML elements with tags, attributes, and children.

**Key Properties**:
```php
protected string $tagName;      // HTML tag name (div, span, etc.)
protected array $attrs;         // Associative array of attributes
protected bool $isVoid;         // Is this a self-closing tag?

const BOOLEAN_ATTRS;            // [checked, disabled, required, ...]
const VOID_TAGS;                // [br, hr, img, input, ...]
```

**Constructor Pattern**:
```php
// Static factory method with flexible arguments
public static function tag(
    string $tagName,
    mixed $firstArgument,    // Can be: string (classes), array (attrs/children)
    ?array $children,
    bool $isVoid = false
): static
```

**Attribute Handling**:
```php
// Special attribute: '_' allows raw attribute strings
$attrs['_'] = 'data-custom="value" x-data="{ open: false }"';

// Boolean attributes: rendered without value if true
['disabled' => true]  → disabled
['disabled' => false] → (not rendered)

// Regular attributes: key="value" format
['id' => 'myId'] → id="myId"
```

**Key Methods**:

1. **Attribute Management**:
   ```php
   setAttribute($key, $value)           // Set any attribute
   getAttribute($key, $default = null)  // Get attribute value
   __call($key, $args)                  // Magic method: dash-to-camelCase
   id($id)                              // Set ID attribute
   classes(...$args)                    // Flexible class management
   ```

2. **Class Management**:
   ```php
   // Multiple patterns supported:
   ->classes('foo', 'bar')
   ->classes(['foo' => true, 'bar' => false])
   ->classes(['foo', 'bar'], ['baz' => true])
   ```

3. **Generated Methods** (100+ attribute helpers):
   ```php
   ->href($url)
   ->src($path)
   ->type($type)
   ->disabled()        // Boolean attr, defaults to true
   ->checked($bool)
   // ... etc
   ```

**Rendering**:
```php
public function __toString(): string
{
    if ($this->isVoid) {
        return "<{$tagName}{$attributes}>";
    }
    return "<{$tagName}{$attributes}>{$children}</{$tagName}>";
}
```

**Export to Array**:
```php
public function toArray(): array
{
    return [
        'tagName' => $this->tagName,
        'attrs' => $this->attrs,
        'children' => $this->children,
        'isVoid' => $this->isVoid,
    ];
}
```

---

### 3. HtmlDocument

**Purpose**: Root document node that adds DOCTYPE declaration.

**Inheritance**: Extends `HtmlNode`

**Rendering**:
```php
public function __toString(): string
{
    return "<!DOCTYPE html>\n" . parent::__toString();
}
```

**Usage**:
```php
_html(['lang' => 'en'],
    _head(...),
    _body(...)
)
// Outputs: <!DOCTYPE html><html lang="en">...</html>
```

---

### 4. ArrayMapNode

**Purpose**: Iterate over arrays/iterables to generate dynamic content.

**Key Properties**:
```php
protected ?iterable $arr;           // Data source
protected $mapFunction;              // Transformation function
```

**Behavior**:
1. If no map function: children are the array items directly
2. If map function exists: each item is transformed via callback
3. Parent context is bound to the map function
4. After rendering, children are cleared (one-time use)

**Usage Pattern**:
```php
$users = [/* ... */];

_ul()->map($users, function($user) {
    return _li($user->name);
})

// Alternative via parent node:
_ul(
    (new Node)->map($users, fn($u) => _li($u->name))
)
```

**Immutability**:
- Throws exception if you try to `appendChild()` directly
- Enforces map-only child management

---

### 5. IfElseNode

**Purpose**: Conditional rendering with if/elseif/else support.

**Key Properties**:
```php
protected array $conditions;      // Stack of conditions
protected array $elseChildren;    // Fallback children
protected int $conditionIdx;      // Current condition index
```

**API Pattern**:
```php
_if($condition)
    (...children for if...)
->elseif($anotherCondition)
    (...children for elseif...)
->else(
    ...fallback children...
)
```

**Rendering Logic**:
1. Evaluate conditions in order
2. Return first truthy branch's children
3. If all false, return else children
4. Uses `SlotNode` internally for rendering

**Example**:
```php
_if($isLoggedIn)
    (_div('Welcome back, ' . $username))
->else(
    _div('Please log in')
)
```

---

### 6. SlotNode

**Purpose**: Deferred content rendering or placeholder for child injection.

**Key Properties**:
```php
protected Closure $slotFunction;  // Optional render callback
protected array $children;         // Static children
```

**Dual Mode**:
1. **Function Mode**: If closure provided, call it to get content
2. **Children Mode**: Render children normally

**Usage Scenarios**:
```php
// Component with slot
function Card($title, $slotContent) {
    return _div(['class' => 'card'],
        _h3($title),
        _slot($slotContent)  // Inject dynamic content
    );
}

// Static children
_slot([
    _p('First paragraph'),
    _p('Second paragraph')
])
```

---

### 7. RawNode

**Purpose**: Output unescaped HTML content (dangerous, use with caution).

**Behavior**:
- No HTML escaping
- Joins children with spaces
- Used for trusted HTML strings

**Example**:
```php
_raw('<svg>...</svg>')  // Outputs SVG without escaping
```

---

### 8. EchoNode

**Purpose**: Capture output from echo statements via output buffering.

**Behavior**:
```php
public function __toString(): string
{
    ob_start();
    foreach ($this->children as $child) {
        $this->tryEvalClosure($child);
    }
    return ob_get_clean() ?: '';
}
```

**Use Case**:
```php
_echo(function() {
    echo "<p>Some legacy code</p>";
    legacy_function_that_echoes();
})
```

---

### 9. SafeString (Utility)

**Purpose**: Mark a string as safe from HTML escaping.

**Properties**:
```php
protected string $value;
```

**Usage**:
```php
// Normal string: escaped
_div('<script>alert("XSS")</script>')
// Output: &lt;script&gt;...

// SafeString: not escaped
_div(_safe('<strong>Bold</strong>'))
// Output: <strong>Bold</strong>
```

---

### 10. HtmlRef (Utility)

**Purpose**: Generate unique HTML IDs/references.

**Algorithm**:
```php
static int $base = 0;

public function __construct(string $ref = '')
{
    if (!$ref) {
        // Increment base (starts from current timestamp in ms)
        HtmlRef::$base = (HtmlRef::$base ?: intval(microtime(true) * 1000)) + 1;
        // Convert to base-36 for shorter IDs
        $this->ref = '_' . base_convert(HtmlRef::$base, 10, 36);
    }
}
```

**Usage**:
```php
$ref = new HtmlRef();
_input(['id' => $ref]);
_label(['for' => $ref], 'Label');
// Output: <input id="_abc123"> <label for="_abc123">Label</label>
```

---

## Helper Function Generation

### Tag Generation System

**Location**: `bin/vdom_tags`

**Purpose**: Generate helper functions for all HTML/SVG tags.

**Generated Functions**:
```php
// For each tag:
function _{tagName}(
    null | string | array | Closure | Node | SafeString $arg = null,
    ?array $children = null
): HtmlNode {
    return HtmlNode::tag('{tagName}', $arg, $children, {isVoid});
}
```

**Supported Tags**:
- **HTML**: a, div, span, p, button, input, form, etc. (100+ tags)
- **SVG**: svg, circle, path, g, defs, etc. (50+ tags)

**Special Cases**:
```php
_html() → HtmlDocument (not HtmlNode)
_br()   → isVoid = true (self-closing)
```

**Utility Functions**:
```php
_raw(...$children)              → RawNode
_safe($string)                  → SafeString
_slot($slotFunction = null)     → SlotNode
_if($condition)                 → IfElseNode
_echo(...$children)             → EchoNode
_h($tagName, $arg = null)       → Custom tag HtmlNode
clsf($format, ...$args)         → Conditional sprintf for classes
```

---

## Design Patterns Used

### 1. **Composite Pattern**
- Tree structure with `Node` as base
- Uniform treatment of leaf and composite nodes
- Parent-child relationships

### 2. **Builder Pattern**
- Fluent interface for construction
- Method chaining: `_div()->id('x')->classes('foo')->(...)`

### 3. **Factory Pattern**
- `HtmlNode::tag()` static factory
- Helper functions abstract construction

### 4. **Strategy Pattern**
- Different rendering strategies per node type
- `__toString()` implementation varies by class

### 5. **Template Method Pattern**
- `Node::childrenToString()` provides template
- Subclasses customize via `__toString()`

### 6. **Closure Binding**
- Closures bound to parent context
- Enables dynamic attribute/content generation

---

## Security Model

### XSS Protection Strategy

**Default Behavior**: All strings are HTML-escaped

```php
_div($userInput)
// Automatically escapes < > & " ' characters
```

**Escape Points**:
1. **Text Content**: In `childrenToString()`
   ```php
   htmlspecialchars($child, ENT_QUOTES | ENT_HTML5)
   ```

2. **Attribute Keys**:
   ```php
   htmlspecialchars($key, ENT_QUOTES | ENT_HTML5)
   ```

3. **Attribute Values**:
   ```php
   htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5)
   ```

**Exceptions (No Escaping)**:
- `SafeString` instances
- Numeric values (int, float)
- Inside `RawNode`
- Special `_` attribute (raw attribute string)

**Safe Opt-Out Mechanisms**:
```php
// 1. SafeString wrapper
_div(_safe($trustedHtml))

// 2. RawNode
_raw($trustedSvg)

// 3. Raw attribute
_div(['_' => 'data-json=\'' . json_encode($data) . '\''])
```

---

## Performance Characteristics

### Time Complexity

| Operation | Complexity | Notes |
|-----------|-----------|-------|
| `appendChild()` | O(1) | Array append |
| `appendChildren()` | O(n) | n = number of children |
| `__toString()` | O(n) | Tree traversal, n = total nodes |
| `map()` | O(n) | n = array length |
| Attribute set/get | O(1) | Array access |

### Space Complexity

| Structure | Complexity | Notes |
|-----------|-----------|-------|
| Node tree | O(n) | n = total nodes |
| Rendered string | O(m) | m = final HTML length |
| Temporary buffers | O(1) | Negligible overhead |

### Optimization Opportunities

1. **String Buffering** (Planned):
   ```php
   // Instead of building large strings
   // Use output buffering with echo
   public function echo() { /* ... */ }
   ```

2. **Lazy Rendering**:
   - Closures enable deferred evaluation
   - Only evaluate when `__toString()` is called

3. **Memory Management**:
   - `ArrayMapNode` clears children after rendering
   - No persistent state after render

---

## API Usage Examples

### Basic HTML Construction

```php
use function _div, _p, _strong;

$html = _div(['class' => 'container'],
    _p('Hello, ', _strong('World'))
);

echo $html;
// Output: <div class="container"><p>Hello, <strong>World</strong></p></div>
```

### Fluent Interface

```php
$button = _button()
    ->type('submit')
    ->classes('btn', 'btn-primary')
    ->disabled()
    ->id('submit-btn')
    ('Submit');

// <button type="submit" class="btn btn-primary" disabled id="submit-btn">Submit</button>
```

### Conditional Rendering

```php
$content = _if($isAdmin)
    (_div('Admin Panel'))
->elseif($isUser)
    (_div('User Dashboard'))
->else(
    _div('Guest View')
);
```

### Array Mapping

```php
$products = [
    ['name' => 'Apple', 'price' => 1.20],
    ['name' => 'Banana', 'price' => 0.50],
];

$list = _ul()->map($products, function($product) {
    return _li(
        _strong($product['name']),
        ' - $' . $product['price']
    );
});
```

### Dynamic Attributes

```php
$link = _a()
    ->href('/about')
    ->classes([
        'link' => true,
        'active' => $currentPage === 'about',
        'disabled' => !$isEnabled
    ])
    ('About Us');
```

### Complex Form

```php
$form = _form(['action' => '/submit', 'method' => 'post'],
    _div(['class' => 'form-group'],
        _label(['for' => 'email'], 'Email'),
        _input()
            ->type('email')
            ->id('email')
            ->name('email')
            ->required()
            ->placeholder('Enter your email')
    ),
    _button(['type' => 'submit'], 'Submit')
);
```

### Component Pattern

```php
function Card($title, $content, $footer = null) {
    return _div(['class' => 'card'],
        _div(['class' => 'card-header'], _h3($title)),
        _div(['class' => 'card-body'], $content),
        $footer ? _div(['class' => 'card-footer'], $footer) : null
    );
}

$card = Card(
    'Welcome',
    _p('This is a card component'),
    _button('Action')
);
```

### Full Document

```php
$doc = _html(['lang' => 'en'],
    _head(
        _meta()->charset('UTF-8'),
        _meta()
            ->name('viewport')
            ->content('width=device-width, initial-scale=1.0'),
        _title('My Page')
    ),
    _body(
        _div(['class' => 'container'],
            _h1('Hello World'),
            _p('Welcome to VDom')
        )
    )
);

echo $doc;
// Output: <!DOCTYPE html><html lang="en">...</html>
```

---

## Extension Points

### Custom Node Types

```php
namespace MyApp\Nodes;

use IceTea\IceDOM\Node;

class CustomNode extends Node
{
    public function __toString()
    {
        // Custom rendering logic
        return "<custom>{$this->childrenToString()}</custom>";
    }
}
```

### Custom Components

```php
function DataTable(array $data, array $columns) {
    return _table(['class' => 'data-table'],
        _thead(
            _tr()->map($columns, fn($col) => _th($col['label']))
        ),
        _tbody()->map($data, function($row) use ($columns) {
            return _tr()->map($columns, fn($col) => _td($row[$col['key']]));
        })
    );
}
```

### Hook System

```php
$div = _div('Content')
    ->use(function($node) {
        // Modify node before render
        $node->setAttribute('data-timestamp', time());
    });
```

---

## Limitations & Future Work

### Current Limitations

1. **No Event Handling**: Pure string generation, no JS interop
2. **No Hydration**: Not designed for SSR + client hydration
3. **No Diffing**: Not a true virtual DOM with reconciliation
4. **Memory Usage**: Large trees held in memory
5. **No Streaming**: Must build entire tree before render

### Planned Features

1. **String Buffering**:
   ```php
   // Commented out throughout codebase
   public function echo() { /* ... */ }
   ```

2. **Performance Monitoring**:
   - Track render time
   - Memory profiling

3. **Type Definitions**:
   - PHPStan/Psalm type hints
   - Generic type support

4. **Advanced Selectors**:
   - Query nodes by ID/class
   - DOM-like traversal API

5. **Template Integration**:
   - Import from HTML templates
   - Convert existing HTML to VDom

---

## Testing Strategy

### Unit Tests

```php
// Test node creation
$div = _div('Hello');
assert($div->__toString() === '<div>Hello</div>');

// Test XSS protection
$malicious = '<script>alert("XSS")</script>';
$safe = _div($malicious);
assert(strpos($safe, '<script>') === false);

// Test attribute handling
$input = _input()->type('text')->required();
assert(strpos($input, 'required') !== false);
```

### Integration Tests

```php
// Test complex document
$doc = _html([], _body(_div('Content')));
assert(strpos($doc, '<!DOCTYPE html>') === 0);

// Test conditional rendering
$result = _if(true)(_div('Yes'))->else(_div('No'));
assert(strpos($result, 'Yes') !== false);
```

---

## Best Practices

### 1. **Always Escape User Input**
```php
// ✅ Good: Automatic escaping
_div($userInput)

// ❌ Bad: Bypassing escaping without validation
_raw($userInput)
```

### 2. **Use Components for Reusability**
```php
// ✅ Good: Reusable component
function Button($label, $type = 'button') {
    return _button(['type' => $type, 'class' => 'btn'], $label);
}

// ❌ Bad: Repetitive code
_button(['type' => 'button', 'class' => 'btn'], 'Submit')
_button(['type' => 'button', 'class' => 'btn'], 'Cancel')
```

### 3. **Leverage Closures for Dynamic Content**
```php
// ✅ Good: Deferred evaluation
_div(fn() => getCurrentTime())

// ❌ Bad: Eager evaluation
$time = getCurrentTime();
_div($time)
```

### 4. **Use HtmlRef for Linked Elements**
```php
// ✅ Good: Guaranteed unique IDs
$id = new HtmlRef();
_input(['id' => $id])->_label(['for' => $id])

// ❌ Bad: Manual ID management
_input(['id' => 'field1'])->_label(['for' => 'field1'])
```

### 5. **Conditional Classes**
```php
// ✅ Good: Array-based conditional classes
->classes([
    'btn' => true,
    'active' => $isActive,
    'disabled' => !$enabled
])

// ❌ Bad: String concatenation
->class('btn' . ($isActive ? ' active' : '') . (!$enabled ? ' disabled' : ''))
```

---

## Comparison with Other Libraries

### vs React (JavaScript)
- **Similarity**: Component-based, tree structure
- **Difference**: VDom is string-based, React has reconciliation

### vs Blade/Twig (PHP Templates)
- **Similarity**: Both generate HTML from PHP
- **Difference**: VDom is programmatic, Blade is template-based

### vs DomDocument (PHP)
- **Similarity**: Both represent HTML as objects
- **Difference**: VDom is immutable and fluent, DomDocument is mutable DOM API

---

## Conclusion

IceTea VDom provides a modern, type-safe approach to HTML generation in PHP. Its design prioritizes:

1. **Developer Experience**: Fluent API, helper functions
2. **Security**: XSS protection by default
3. **Flexibility**: Closures, conditional rendering, components
4. **Performance**: Efficient string concatenation (with buffering planned)

The library is suitable for:
- Server-side rendering
- Email templates
- Dynamic HTML generation
- Component-based PHP applications

**Not suitable for**:
- Client-side rendering
- Single Page Applications
- Real-time DOM updates

---

## Appendix: Code Generation

### Attribute Method Generation

**File**: `src/htmlnode_attrs.php`

**Process**:
1. Read list of HTML attributes
2. Identify boolean attributes
3. Generate method for each attribute
4. Insert between `// GENERATED` markers in `HtmlNode.php`

### Tag Helper Generation

**File**: `bin/vdom_tags`

**Process**:
1. Define list of HTML/SVG tags
2. Identify void tags
3. Generate function for each tag
4. Support custom namespaces via CLI args

**Usage**:
```bash
# Generate helpers in current namespace
./bin/vdom_tags

# Generate with custom namespace
./bin/vdom_tags 'IceTea\\VDom\\HtmlNode' 'App\\Html'
```

---

**Document Version**: 1.0  
**Last Updated**: 2025-10-12  
**Author**: System Analysis
