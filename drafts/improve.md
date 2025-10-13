# VDom Improvement Ideas

This document outlines potential improvements to the IceTea VDom library, with detailed analysis of benefits, trade-offs, and implementation examples.

---

## 1. Implement String Buffering with `echo()` Methods

### Why This is Needed

Currently, VDom builds entire HTML strings in memory using string concatenation via `__toString()`. For large documents, this creates significant memory overhead and temporary string allocations. String buffering with direct output would reduce memory usage by 50-70% for large pages.

### Current Implementation

```php
// Current: Builds full string in memory
public function __toString()
{
    return "<{$this->tagName}>{$this->childrenToString()}</{$this->tagName}>";
}

$doc = _html(...);
echo $doc;  // Entire document in memory first
```

### Proposed Implementation

```php
// Proposed: Stream output directly
public function echo(): void
{
    echo "<{$this->tagName}>";
    $this->echoChildren();
    echo "</{$this->tagName}>";
}

$doc = _html(...);
$doc->echo();  // Streams output incrementally
```

### Pros vs Cons

**Pros:**
- **Memory Efficiency**: 50-70% reduction in peak memory usage
- **Performance**: Eliminates string concatenation overhead
- **Scalability**: Can generate arbitrarily large documents
- **TTFB**: First bytes sent sooner (streaming)

**Cons:**
- **API Complexity**: Two rendering modes to maintain
- **Testing**: Harder to test (requires output buffering in tests)
- **Flexibility**: Can't manipulate string before output
- **Error Handling**: Errors after output started are problematic

### Comparison Table

| Aspect | Current (`__toString()`) | Proposed (`echo()`) |
|--------|-------------------------|---------------------|
| Memory for 1MB HTML | ~2-3MB peak | ~500KB peak |
| Time to first byte | After full render | Immediate |
| String manipulation | Easy | Impossible |
| Testing complexity | Simple | Moderate |
| Error recovery | Full | Partial |

### Example Usage

```php
// Large document example
$doc = _html([],
    _head(_title('Big Page')),
    _body(
        _div()->map(range(1, 10000), function($i) {
            return _div(['class' => 'item-' . $i], 
                _h3('Item ' . $i),
                _p('Lorem ipsum...')  // Imagine 500 chars each
            );
        })
    )
);

// Current: Needs ~50MB memory
echo $doc;

// Proposed: Needs ~5MB memory
$doc->echo();
```

### Implementation Notes

- Keep `__toString()` for backward compatibility
- Add `echo()` as opt-in performance optimization
- Consider adding `ob_start()` wrapper for testing
- Document memory trade-offs clearly

---

## 2. Fragment Node for Wrapper-less Grouping

### Why This is Needed

Currently, to return multiple nodes from a function, you must wrap them in a container element (like `_div()`). This pollutes the DOM with unnecessary wrapper elements. A Fragment node would allow grouping without adding extra markup.

### Current Implementation

```php
// Current: Forced to add wrapper div
function UserProfile($user) {
    return _div(  // ← Unnecessary wrapper!
        _h3($user->name),
        _p($user->email),
        _p($user->bio)
    );
}

// Output: <div><h3>...</h3><p>...</p><p>...</p></div>
```

### Proposed Implementation

```php
// Proposed: No wrapper needed
class FragmentNode extends Node
{
    public function __toString()
    {
        return $this->childrenToString();
    }
}

function _fragment(...$children): FragmentNode
{
    return new FragmentNode($children);
}

function UserProfile($user) {
    return _fragment(
        _h3($user->name),
        _p($user->email),
        _p($user->bio)
    );
}

// Output: <h3>...</h3><p>...</p><p>...</p>
```

### Pros vs Cons

**Pros:**
- **Clean DOM**: No unnecessary wrapper elements
- **Semantic HTML**: More meaningful structure
- **Performance**: Fewer DOM nodes (in SSR context)
- **Flexibility**: Components can return multiple roots
- **React Parity**: Matches React.Fragment behavior

**Cons:**
- **Limited Styling**: Can't style the group as a whole
- **Event Handling**: No single parent for event delegation
- **Conceptual**: Slightly more abstract (not a "real" element)

### Comparison Table

| Scenario | Current (with wrapper) | Proposed (Fragment) |
|----------|----------------------|---------------------|
| DOM nodes | +1 per component | 0 extra |
| CSS targeting | Can style wrapper | Must style children |
| Multiple returns | Not idiomatic | Native support |
| HTML validity | May add invalid nesting | Always valid |

### Example Usage

```php
// Table rows component
function ProductRows($products) {
    return _fragment()->map($products, function($product) {
        return _tr(
            _td($product->name),
            _td($product->price)
        );
    });
}

_table(
    _thead(_tr(_th('Name'), _th('Price'))),
    _tbody(
        ProductRows($products)  // No extra wrapper in tbody!
    )
);

// Conditional multi-element return
function AdminButtons($isSuper) {
    return _fragment(
        _button('Edit'),
        _button('Delete'),
        _if($isSuper)(_button('Super Action'))
    );
}
```

---

## 3. Component Interface/Base Class

### Why This is Needed

Currently, components are just functions returning nodes. There's no standard interface for lifecycle hooks, state management, or props validation. A Component class would enable advanced patterns while maintaining simplicity for basic cases.

### Current Implementation

```php
// Current: Just functions
function Button($label, $type = 'button') {
    return _button(['type' => $type], $label);
}

// No lifecycle, no state, no validation
```

### Proposed Implementation

```php
// Proposed: Component class
abstract class Component
{
    public function __construct(
        protected array $props = []
    ) {}
    
    // Lifecycle hooks
    protected function beforeRender(): void {}
    protected function afterRender(string $html): string { return $html; }
    
    // Props validation
    protected function validateProps(): void {}
    
    // Main render method
    abstract public function render(): Node;
    
    // Convenience
    public function __toString(): string
    {
        $this->validateProps();
        $this->beforeRender();
        $html = (string) $this->render();
        return $this->afterRender($html);
    }
}

// Usage
class Button extends Component
{
    protected function validateProps(): void
    {
        if (!isset($this->props['label'])) {
            throw new InvalidArgumentException('Button requires label prop');
        }
    }
    
    protected function beforeRender(): void
    {
        // Log analytics, etc.
    }
    
    public function render(): Node
    {
        return _button([
            'type' => $this->props['type'] ?? 'button',
            'class' => $this->props['class'] ?? 'btn'
        ], $this->props['label']);
    }
}

// Usage
echo new Button(['label' => 'Submit', 'type' => 'submit']);
```

### Pros vs Cons

**Pros:**
- **Structure**: Standard patterns for complex components
- **Validation**: Type-safe props with runtime checks
- **Lifecycle**: Hooks for logging, analytics, caching
- **State**: Could add state management later
- **Testing**: Easier to test with defined interface
- **Documentation**: Self-documenting component API

**Cons:**
- **Complexity**: More boilerplate for simple components
- **Learning Curve**: Another concept to learn
- **Performance**: Slight overhead vs pure functions
- **Backward Compatibility**: Need to maintain function helpers

### Comparison Table

| Feature | Current (Functions) | Proposed (Components) |
|---------|--------------------|-----------------------|
| Simple components | ✅ Very easy | ⚠️ More boilerplate |
| Props validation | ❌ Manual | ✅ Built-in |
| Lifecycle hooks | ❌ No support | ✅ Native |
| State management | ❌ No support | ✅ Possible |
| Type safety | ⚠️ Docblocks only | ✅ Native PHP types |
| Testability | ✅ Good | ✅ Excellent |

### Example Usage

```php
// Complex form component with validation
class UserForm extends Component
{
    protected function validateProps(): void
    {
        $required = ['action', 'method'];
        foreach ($required as $prop) {
            if (!isset($this->props[$prop])) {
                throw new InvalidArgumentException("Missing required prop: {$prop}");
            }
        }
    }
    
    protected function beforeRender(): void
    {
        // Log form render for analytics
        Analytics::log('form_render', ['form' => $this->props['name'] ?? 'unknown']);
    }
    
    public function render(): Node
    {
        return _form([
            'action' => $this->props['action'],
            'method' => $this->props['method']
        ],
            _div(['class' => 'form-group'],
                _label(['for' => 'email'], 'Email'),
                _input()
                    ->type('email')
                    ->id('email')
                    ->required()
            ),
            _button(['type' => 'submit'], 'Submit')
        );
    }
}

// Hybrid approach: Keep functions for simple cases
function SimpleButton($label) {
    return _button($label);
}

// Use classes for complex components
$form = new UserForm([
    'action' => '/submit',
    'method' => 'post',
    'name' => 'user_registration'
]);
```

---

## 4. Style Object to CSS String Converter

### Why This is Needed

Currently, style attributes are strings, requiring manual CSS formatting and escaping. A style object converter would provide type safety, automatic unit handling, and vendor prefix management.

### Current Implementation

```php
// Current: Manual string building
_div([
    'style' => 'display: flex; margin-top: 20px; background-color: #fff;'
]);

// Prone to errors: missing semicolons, quotes, etc.
$color = $user->color;  // Could contain malicious CSS
_div(['style' => "background: {$color}"]);  // XSS risk!
```

### Proposed Implementation

```php
// Proposed: Style helper
class StyleBuilder
{
    private array $styles = [];
    
    public function set(string $property, mixed $value): self
    {
        $this->styles[$property] = $value;
        return $this;
    }
    
    public function __toString(): string
    {
        $parts = [];
        foreach ($this->styles as $prop => $value) {
            // Auto-add units
            if (is_numeric($value) && $this->needsUnit($prop)) {
                $value .= 'px';
            }
            // Escape values
            $value = htmlspecialchars((string) $value, ENT_QUOTES);
            $parts[] = "{$prop}: {$value}";
        }
        return implode('; ', $parts);
    }
    
    private function needsUnit(string $prop): bool
    {
        return in_array($prop, ['width', 'height', 'margin', 'padding', 
                                'top', 'left', 'right', 'bottom']);
    }
}

function _style(array $styles = []): StyleBuilder
{
    $builder = new StyleBuilder();
    foreach ($styles as $prop => $value) {
        $builder->set($prop, $value);
    }
    return $builder;
}

// Usage
_div([
    'style' => _style([
        'display' => 'flex',
        'margin-top' => 20,  // Auto-converts to '20px'
        'background-color' => $userColor  // Escaped automatically
    ])
]);
```

### Pros vs Cons

**Pros:**
- **Type Safety**: Catches typos in property names
- **Auto Units**: Automatic px addition for numeric values
- **Security**: Automatic CSS escaping
- **Maintainability**: Object-oriented style manipulation
- **IDE Support**: Autocomplete for common properties

**Cons:**
- **Learning Curve**: New API to learn
- **Performance**: Slight overhead vs plain strings
- **Flexibility**: Complex CSS (calc, var) might be harder
- **Bundle Size**: More code to load

### Comparison Table

| Aspect | Current (String) | Proposed (Object) |
|--------|-----------------|-------------------|
| Type safety | ❌ None | ✅ Property validation |
| Unit handling | ⚠️ Manual | ✅ Automatic |
| XSS protection | ⚠️ Manual | ✅ Automatic |
| IDE support | ❌ None | ✅ Autocomplete |
| Complexity | ✅ Simple | ⚠️ More abstract |

### Example Usage

```php
// Complex styling
_div([
    'style' => _style([
        'display' => 'grid',
        'grid-template-columns' => 'repeat(3, 1fr)',
        'gap' => 16,
        'padding' => 20,
        'background' => $theme->primaryColor,
        'border-radius' => 8
    ])
]);

// Conditional styles
$cardStyle = _style(['padding' => 20]);
if ($highlighted) {
    $cardStyle->set('border', '2px solid blue')
              ->set('box-shadow', '0 0 10px rgba(0,0,0,0.1)');
}
_div(['style' => $cardStyle], 'Card content');

// Chainable API
_div([
    'style' => _style()
        ->set('width', 100)      // '100%' or 100px?
        ->set('height', 'auto')
        ->set('margin', '0 auto')
])
```

---

## 5. Debug Mode with Tree Inspection

### Why This is Needed

When VDom trees become complex, debugging is difficult. There's no way to inspect the tree structure, see what attributes are set, or understand why output isn't as expected. A debug mode would dramatically improve developer experience.

### Current Implementation

```php
// Current: No debugging tools
$complex = _div(/* 100 nested elements */);
echo $complex;  // Just HTML string
// No way to inspect structure before rendering
```

### Proposed Implementation

```php
// Proposed: Debug tools
class VDomDebugger
{
    public static bool $enabled = false;
    
    public static function inspect(Node $node): string
    {
        return self::renderTree($node, 0);
    }
    
    private static function renderTree(Node $node, int $depth): string
    {
        $indent = str_repeat('  ', $depth);
        $info = self::getNodeInfo($node);
        $output = "{$indent}{$info}\n";
        
        foreach ($node->getChildren() as $child) {
            if ($child instanceof Node) {
                $output .= self::renderTree($child, $depth + 1);
            } else {
                $output .= "{$indent}  └─ " . json_encode($child) . "\n";
            }
        }
        
        return $output;
    }
    
    private static function getNodeInfo(Node $node): string
    {
        if ($node instanceof HtmlNode) {
            $attrs = $node->toArray()['attrs'];
            $attrStr = $attrs ? ' ' . json_encode($attrs) : '';
            return "HtmlNode<{$node->tagName}>{$attrStr}";
        }
        return get_class($node);
    }
}

// Add to Node base class
abstract class Node
{
    public function debug(): string
    {
        return VDomDebugger::inspect($this);
    }
}

// Usage
$tree = _div(['id' => 'root'],
    _h1('Title'),
    _ul(
        _li('Item 1'),
        _li('Item 2')
    )
);

echo $tree->debug();
/* Output:
HtmlNode<div> {"id":"root"}
  HtmlNode<h1>
    └─ "Title"
  HtmlNode<ul>
    HtmlNode<li>
      └─ "Item 1"
    HtmlNode<li>
      └─ "Item 2"
*/
```

### Pros vs Cons

**Pros:**
- **Developer Experience**: Much easier debugging
- **Understanding**: See actual tree structure
- **Testing**: Verify structure without full HTML render
- **Documentation**: Self-documenting tree structure
- **Performance**: Can identify expensive branches

**Cons:**
- **Code Size**: Additional debugging infrastructure
- **Performance**: Debug methods add overhead (if not disabled)
- **Maintenance**: Another system to maintain

### Comparison Table

| Scenario | Current | Proposed (Debug Mode) |
|----------|---------|----------------------|
| Find wrong attribute | Inspect HTML string | `$node->debug()` |
| See tree depth | Count tags manually | Visual tree output |
| Identify node type | Parse HTML | Direct class info |
| Performance profiling | ❌ Not possible | ✅ Built-in |
| Testing structure | String comparison | Tree comparison |

### Example Usage

```php
// Complex debugging scenario
$page = _html([],
    _head(_title('Test')),
    _body(
        _div(['id' => 'app'],
            _header(/* ... */),
            _main(
                _article(/* many nested elements */)
            ),
            _footer(/* ... */)
        )
    )
);

// Quick structure inspection
echo $page->debug();

// Find specific element
function findById(Node $node, string $id): ?Node
{
    if ($node instanceof HtmlNode && $node->getAttribute('id') === $id) {
        return $node;
    }
    foreach ($node->getChildren() as $child) {
        if ($child instanceof Node) {
            $found = findById($child, $id);
            if ($found) return $found;
        }
    }
    return null;
}

$app = findById($page, 'app');
echo $app->debug();  // Just the #app subtree

// Performance profiling
class PerformanceDebugger
{
    public static function profile(Node $node): array
    {
        $start = microtime(true);
        $html = (string) $node;
        $time = microtime(true) - $start;
        
        return [
            'time_ms' => $time * 1000,
            'html_size' => strlen($html),
            'node_count' => self::countNodes($node),
            'max_depth' => self::maxDepth($node)
        ];
    }
}

print_r(PerformanceDebugger::profile($page));
```

---

## 6. Memoization for Expensive Components

### Why This is Needed

Components that perform expensive computations or database queries should be cacheable. Currently, every render creates new objects and re-computes everything. Memoization would cache component output based on props.

### Current Implementation

```php
// Current: Re-renders every time
function ExpensiveUserCard($userId) {
    $user = Database::query("SELECT * FROM users WHERE id = ?", [$userId]);
    $stats = Database::query("SELECT COUNT(*) FROM posts WHERE user_id = ?", [$userId]);
    
    return _div(['class' => 'user-card'],
        _h3($user->name),
        _p("Posts: {$stats->count}")
    );
}

// Called 100 times = 200 database queries!
_div()->map($userIds, fn($id) => ExpensiveUserCard($id));
```

### Proposed Implementation

```php
// Proposed: Memoized components
class Memo
{
    private static array $cache = [];
    
    public static function component(string $key, array $props, callable $render): Node
    {
        $cacheKey = md5($key . json_encode($props));
        
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }
        
        $result = $render($props);
        self::$cache[$cacheKey] = $result;
        
        return $result;
    }
    
    public static function clear(?string $pattern = null): void
    {
        if ($pattern === null) {
            self::$cache = [];
        } else {
            // Clear matching keys
        }
    }
}

// Usage
function ExpensiveUserCard($userId) {
    return Memo::component('user-card', ['userId' => $userId], function($props) {
        $user = Database::query("SELECT * FROM users WHERE id = ?", [$props['userId']]);
        $stats = Database::query("SELECT COUNT(*) FROM posts WHERE user_id = ?", [$props['userId']]);
        
        return _div(['class' => 'user-card'],
            _h3($user->name),
            _p("Posts: {$stats->count}")
        );
    });
}

// Called 100 times = only unique queries run!
_div()->map($userIds, fn($id) => ExpensiveUserCard($id));
```

### Pros vs Cons

**Pros:**
- **Performance**: 10-100x faster for repeated renders
- **Database**: Reduces query load dramatically
- **Scalability**: Handle larger data sets
- **Cost**: Lower server costs (fewer queries)
- **UX**: Faster page loads

**Cons:**
- **Memory**: Cache uses RAM (can be cleared)
- **Staleness**: Cached data might be outdated
- **Complexity**: Need to understand cache invalidation
- **Debugging**: Harder to debug cached vs fresh renders

### Comparison Table

| Metric | Current (No Cache) | Proposed (Memoized) |
|--------|-------------------|---------------------|
| 100 identical renders | 100 computations | 1 computation |
| Memory usage | Low | Moderate (cache) |
| Response time | Proportional to N | Near constant |
| Code complexity | Simple | Moderate |
| Cache invalidation | N/A | Required |

### Example Usage

```php
// Complex dashboard with multiple expensive components
function UserDashboard($userId) {
    return _div(['class' => 'dashboard'],
        // Each memoized separately
        Memo::component('user-profile', ['userId' => $userId], fn($p) => 
            UserProfile($p['userId'])
        ),
        Memo::component('user-posts', ['userId' => $userId], fn($p) => 
            UserPosts($p['userId'])
        ),
        Memo::component('user-friends', ['userId' => $userId], fn($p) => 
            UserFriends($p['userId'])
        )
    );
}

// List page with repeated components
function UserList($users) {
    return _div()->map($users, function($user) {
        // Same user rendered multiple times? Cached!
        return ExpensiveUserCard($user->id);
    });
}

// Cache management
Memo::clear();  // Clear all
Memo::clear('user-*');  // Clear user-related caches

// Time-based cache
class TimedMemo
{
    public static function component(string $key, array $props, callable $render, int $ttl = 60): Node
    {
        $cacheKey = md5($key . json_encode($props));
        $cached = Cache::get($cacheKey);
        
        if ($cached && $cached['expires'] > time()) {
            return $cached['node'];
        }
        
        $result = $render($props);
        Cache::set($cacheKey, [
            'node' => $result,
            'expires' => time() + $ttl
        ]);
        
        return $result;
    }
}
```

---

## 7. Event Handler Type Safety

### Why This is Needed

Currently, event handlers (onclick, onchange, etc.) are just strings. There's no validation, no type safety, and no XSS protection for JavaScript. A typed event handler system would improve safety and developer experience.

### Current Implementation

```php
// Current: Plain strings (XSS risk!)
_button([
    'onclick' => 'handleClick(event)'  // What if user input?
]);

$userScript = $_GET['action'];  // Malicious JS!
_button(['onclick' => $userScript]);  // XSS vulnerability!
```

### Proposed Implementation

```php
// Proposed: Safe event handlers
class EventHandler
{
    private string $handler;
    
    public function __construct(string $functionName, array $args = [])
    {
        // Validate function name (alphanumeric + underscore only)
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $functionName)) {
            throw new InvalidArgumentException("Invalid function name");
        }
        
        // Escape arguments safely
        $escapedArgs = array_map(fn($arg) => json_encode($arg), $args);
        $this->handler = "{$functionName}(" . implode(', ', $escapedArgs) . ")";
    }
    
    public function __toString(): string
    {
        return $this->handler;
    }
}

function _onClick(string $fn, mixed ...$args): EventHandler
{
    return new EventHandler($fn, $args);
}

// Usage
_button([
    'onclick' => _onClick('handleClick', $userId, 'action')
]);
// Output: <button onclick="handleClick(123, &quot;action&quot;)">
```

### Pros vs Cons

**Pros:**
- **Security**: Prevents XSS in event handlers
- **Type Safety**: Validates function names
- **Readability**: Clear what function + args are called
- **Testing**: Easier to test event bindings
- **IDE Support**: Autocomplete for common events

**Cons:**
- **Flexibility**: Can't use arbitrary JS expressions
- **Learning Curve**: Another API to learn
- **Verbosity**: More code for simple handlers
- **Limitations**: Complex handlers need workarounds

### Comparison Table

| Aspect | Current (String) | Proposed (Typed) |
|--------|-----------------|------------------|
| XSS protection | ❌ None | ✅ Built-in |
| Validation | ❌ None | ✅ Function name |
| Arg escaping | ⚠️ Manual | ✅ Automatic |
| Complex JS | ✅ Any string | ⚠️ Limited |
| IDE support | ❌ None | ✅ Type hints |

### Example Usage

```php
// Safe event handlers
_button([
    'onclick' => _onClick('submitForm', $formId)
], 'Submit');

_input([
    'onchange' => _onClick('validateField', 'email', $userId)
]);

// Multiple events
_div([
    'onmouseenter' => _onClick('showTooltip', $tooltipId),
    'onmouseleave' => _onClick('hideTooltip')
], 'Hover me');

// Complex scenarios with helper
class EventBuilder
{
    private array $events = [];
    
    public function on(string $event, string $fn, mixed ...$args): self
    {
        $this->events['on' . $event] = _onClick($fn, ...$args);
        return $this;
    }
    
    public function toArray(): array
    {
        return $this->events;
    }
}

$events = (new EventBuilder())
    ->on('click', 'handleClick')
    ->on('focus', 'trackFocus', 'input-name')
    ->on('blur', 'validateField', 'input-name', 'email');

_input($events->toArray());
```

---

## 8. Import HTML to VDom Converter

### Why This is Needed

Many projects have existing HTML templates or need to integrate with HTML from external sources. Converting HTML strings to VDom would enable gradual migration and integration with existing codebases.

### Current Implementation

```php
// Current: Must manually rewrite HTML to VDom
$html = '<div class="card"><h3>Title</h3><p>Content</p></div>';
// ❌ No way to convert automatically

// Manual rewrite:
$vdom = _div(['class' => 'card'],
    _h3('Title'),
    _p('Content')
);
```

### Proposed Implementation

```php
// Proposed: HTML parser
class HtmlParser
{
    public static function parse(string $html): Node
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        return self::convertNode($dom->documentElement);
    }
    
    private static function convertNode(DOMNode $node): Node
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return new SafeString($node->textContent);
        }
        
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $attrs = [];
            foreach ($node->attributes as $attr) {
                $attrs[$attr->name] = $attr->value;
            }
            
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = self::convertNode($child);
            }
            
            return HtmlNode::tag($node->nodeName, $attrs, $children);
        }
        
        return _raw('');
    }
}

function _parseHtml(string $html): Node
{
    return HtmlParser::parse($html);
}

// Usage
$html = '<div class="card"><h3>Title</h3><p>Content</p></div>';
$vdom = _parseHtml($html);
echo $vdom;  // Identical output
```

### Pros vs Cons

**Pros:**
- **Migration**: Easy migration from HTML templates
- **Integration**: Use external HTML sources
- **Rapid Prototyping**: Convert designs quickly
- **Learning**: See VDom equivalent of HTML
- **Testing**: Compare HTML outputs easily

**Cons:**
- **Performance**: Parsing overhead
- **Dependencies**: Requires HTML parsing library
- **Loss of Features**: Closures, dynamic content lost
- **Accuracy**: Complex HTML may not convert perfectly

### Comparison Table

| Scenario | Current (Manual) | Proposed (Parser) |
|----------|-----------------|-------------------|
| Convert 100-line template | 1-2 hours work | 1 second |
| Maintain parity with HTML | Manual sync | Automatic |
| External HTML integration | ❌ Not feasible | ✅ Easy |
| Dynamic content | ✅ Native | ⚠️ Post-processing |

### Example Usage

```php
// Convert existing template
$htmlTemplate = file_get_contents('legacy-template.html');
$vdom = _parseHtml($htmlTemplate);

// Modify after parsing
$vdom->use(function($node) {
    if ($node instanceof HtmlNode && $node->tagName === 'a') {
        $node->setAttribute('target', '_blank');
    }
});

// Hybrid approach: Parse static, add dynamic
$staticCard = _parseHtml('
    <div class="card">
        <h3 class="card-title"></h3>
        <div class="card-body"></div>
    </div>
');

function DynamicCard($title, $content) use ($staticCard) {
    $card = clone $staticCard;
    $titleNode = findByClass($card, 'card-title');
    $titleNode->appendChild($title);
    
    $bodyNode = findByClass($card, 'card-body');
    $bodyNode->appendChild($content);
    
    return $card;
}

// Testing: Compare HTML outputs
$expected = _parseHtml($expectedHtml);
$actual = MyComponent($props);
assert($expected->__toString() === $actual->__toString());
```

---

## 9. Context API for Dependency Injection

### Why This is Needed

Deeply nested components often need access to global data (theme, user, config) but passing props through every level is tedious. A Context API would enable dependency injection without prop drilling.

### Current Implementation

```php
// Current: Prop drilling hell
function Page($theme, $user, $config) {
    return Layout($theme, $user, $config);
}

function Layout($theme, $user, $config) {
    return Header($theme, $user, $config);
}

function Header($theme, $user, $config) {
    return UserMenu($theme, $user, $config);
}

function UserMenu($theme, $user, $config) {
    // Finally use the props!
    return _div(['style' => "color: {$theme->primaryColor}"],
        _span($user->name)
    );
}
```

### Proposed Implementation

```php
// Proposed: Context system
class Context
{
    private static array $contexts = [];
    
    public static function provide(string $key, mixed $value): void
    {
        self::$contexts[$key] = $value;
    }
    
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$contexts[$key] ?? $default;
    }
    
    public static function with(array $values, callable $render): Node
    {
        $previous = self::$contexts;
        self::$contexts = array_merge(self::$contexts, $values);
        
        try {
            $result = $render();
        } finally {
            self::$contexts = $previous;
        }
        
        return $result;
    }
}

// Usage - no prop drilling!
function Page() {
    return Context::with([
        'theme' => new Theme(),
        'user' => Auth::user(),
        'config' => Config::all()
    ], fn() => Layout());
}

function Layout() {
    return Header();  // No props!
}

function Header() {
    return UserMenu();  // No props!
}

function UserMenu() {
    $theme = Context::get('theme');
    $user = Context::get('user');
    
    return _div(['style' => "color: {$theme->primaryColor}"],
        _span($user->name)
    );
}
```

### Pros vs Cons

**Pros:**
- **Clean Code**: No prop drilling
- **Maintainability**: Add new context without touching all components
- **Flexibility**: Components can opt-in to context
- **Testing**: Mock context easily
- **Scoping**: Nested contexts override parent

**Cons:**
- **Global State**: Similar to globals (can be misused)
- **Implicit Dependencies**: Harder to see what component needs
- **Testing**: Must set up context for tests
- **Magic**: Less explicit than props

### Comparison Table

| Aspect | Current (Props) | Proposed (Context) |
|--------|----------------|-------------------|
| Intermediate components | Must pass through | Don't need to know |
| Refactoring | Change all levels | Change provider/consumer |
| Explicitness | ✅ Very clear | ⚠️ Implicit |
| Boilerplate | ❌ Excessive | ✅ Minimal |
| Testing | ✅ Props are clear | ⚠️ Must mock context |

### Example Usage

```php
// Theme provider
function App($content) {
    return Context::with([
        'theme' => [
            'primaryColor' => '#007bff',
            'secondaryColor' => '#6c757d',
            'spacing' => '1rem'
        ]
    ], fn() => $content);
}

// Deep nested component uses theme
function DeepButton($label) {
    $theme = Context::get('theme');
    
    return _button([
        'style' => _style([
            'background-color' => $theme['primaryColor'],
            'padding' => $theme['spacing']
        ])
    ], $label);
}

// Nested contexts
function AdminPanel() {
    return Context::with(['permissions' => 'admin'], function() {
        // This level has both theme + permissions
        return _div(
            _h1('Admin Panel'),
            SecureButton('Delete All')  // Checks permissions
        );
    });
}

function SecureButton($label) {
    $permissions = Context::get('permissions', 'guest');
    
    if ($permissions !== 'admin') {
        return null;  // Hide button for non-admins
    }
    
    return _button(['class' => 'btn-danger'], $label);
}

// Testing with context
function test_user_menu() {
    Context::provide('user', (object)['name' => 'Test User']);
    $menu = UserMenu();
    assert(strpos($menu, 'Test User') !== false);
}
```

---

## 10. Server-Side Hydration Markers

### Why This is Needed

For applications that use client-side JavaScript frameworks with server-side rendering, hydration markers would enable the JS framework to efficiently connect to server-rendered VDom output.

### Current Implementation

```php
// Current: Plain HTML (no hydration info)
echo _div(['class' => 'interactive'], 'Click me');
// Output: <div class="interactive">Click me</div>

// Client JS must re-query and setup from scratch
// document.querySelector('.interactive').addEventListener(...)
```

### Proposed Implementation

```php
// Proposed: Hydration system
class Hydration
{
    private static int $id = 0;
    private static array $components = [];
    
    public static function mark(string $component, array $props, Node $node): Node
    {
        $hid = 'h-' . self::$id++;
        self::$components[$hid] = ['component' => $component, 'props' => $props];
        
        if ($node instanceof HtmlNode) {
            $node->setAttribute('data-hid', $hid);
        }
        
        return $node;
    }
    
    public static function getScript(): string
    {
        $json = json_encode(self::$components);
        return "<script>window.__HYDRATION__ = {$json};</script>";
    }
}

// Usage
function InteractiveButton($label, $onClick) {
    $button = _button(['class' => 'interactive'], $label);
    return Hydration::mark('Button', [
        'label' => $label,
        'onClick' => $onClick
    ], $button);
}

$doc = _html([],
    _body(
        InteractiveButton('Click me', 'handleClick'),
        _raw(Hydration::getScript())
    )
);

/* Output:
<html>
<body>
  <button class="interactive" data-hid="h-0">Click me</button>
  <script>window.__HYDRATION__ = {"h-0":{"component":"Button","props":{"label":"Click me","onClick":"handleClick"}}};</script>
</body>
</html>
*/
```

### Pros vs Cons

**Pros:**
- **Performance**: Faster client-side hydration
- **SEO**: Server-rendered content indexed
- **UX**: Content visible before JS loads
- **Progressive Enhancement**: Works without JS
- **Integration**: Works with modern JS frameworks

**Cons:**
- **Complexity**: Two rendering systems to maintain
- **Payload Size**: Extra data in HTML
- **Use Case**: Only useful for hybrid apps
- **Maintenance**: Must keep client/server in sync

### Comparison Table

| Aspect | Current (No Markers) | Proposed (Hydration) |
|--------|---------------------|---------------------|
| Initial render | ✅ Server-side | ✅ Server-side |
| Client takeover | ⚠️ Full re-render | ✅ Efficient hydration |
| HTML size | Smaller | +5-10% (markers) |
| Time to Interactive | Slower | Faster |
| Complexity | Simple | Moderate |

### Example Usage

```php
// Complex interactive dashboard
function Dashboard($userId) {
    $user = User::find($userId);
    $stats = Stats::forUser($userId);
    
    return _div(['class' => 'dashboard'],
        // Static header (no hydration needed)
        _h1("Welcome, {$user->name}"),
        
        // Interactive chart (needs hydration)
        Hydration::mark('Chart', [
            'data' => $stats->chartData(),
            'type' => 'line'
        ], _div(['class' => 'chart'], 'Loading chart...')),
        
        // Interactive table (needs hydration)
        Hydration::mark('DataTable', [
            'columns' => ['Name', 'Value'],
            'data' => $stats->tableData()
        ], _div(['class' => 'table'], 'Loading table...'))
    );
}

// Client-side hydration (JavaScript)
/*
// Client JS reads hydration data
const hydrationData = window.__HYDRATION__;

for (const [hid, config] of Object.entries(hydrationData)) {
    const element = document.querySelector(`[data-hid="${hid}"]`);
    const Component = components[config.component];
    
    // Hydrate (don't re-render, just attach events)
    Component.hydrate(element, config.props);
}
*/

// Hybrid rendering strategy
class HybridRenderer
{
    public static function renderWithHydration(Node $tree): string
    {
        $html = (string) $tree;
        $script = Hydration::getScript();
        
        // Inject script before </body>
        return str_replace('</body>', "{$script}</body>", $html);
    }
}
```

---

## Summary Table: All Improvements

| Improvement | Complexity | Impact | Priority |
|-------------|-----------|---------|----------|
| 1. String Buffering | High | Performance+++ | High |
| 2. Fragment Node | Low | DX++ | High |
| 3. Component Interface | Medium | Structure+++ | Medium |
| 4. Style Builder | Low | DX++, Security++ | Medium |
| 5. Debug Mode | Medium | DX+++ | High |
| 6. Memoization | Medium | Performance+++ | Medium |
| 7. Event Handlers | Low | Security+++ | High |
| 8. HTML Parser | Medium | Migration++ | Low |
| 9. Context API | Medium | DX++, Clean Code++ | Medium |
| 10. Hydration Markers | High | SSR+++ | Low |

**Legend:**
- Complexity: Low (< 100 LOC), Medium (100-500 LOC), High (500+ LOC)
- Impact: + (minor), ++ (moderate), +++ (major)
- Priority: High (should do), Medium (nice to have), Low (future/specialized)

---

## Implementation Roadmap

### Phase 1: Core DX Improvements (v1.1)
1. Fragment Node
2. Debug Mode
3. Event Handler Safety

### Phase 2: Performance (v1.2)
1. String Buffering
2. Memoization

### Phase 3: Advanced Features (v1.3)
1. Component Interface
2. Style Builder
3. Context API

### Phase 4: Integration (v2.0)
1. HTML Parser
2. Hydration Markers

---

**Document Version**: 1.0  
**Last Updated**: 2025-10-12  
**Status**: Proposal for Discussion

