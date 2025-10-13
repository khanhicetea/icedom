# VDom Data Structure & API Analysis

## Part 1: Data Structure Analysis

### Current Data Structure Assessment

#### Node Class Structure

```php
abstract class Node
{
    protected ?Node $parent;      // Single parent reference
    protected array $children;     // Untyped array of mixed children
}
```

### Issues & Improvements

#### ❌ Issue 1: Untyped Children Array

**Current:**
```php
protected array $children = [];  // Can contain: Node, string, int, Closure, Stringable, null
```

**Problems:**
- No type safety at compile time
- PHPStan/Psalm can't validate children types
- Runtime type checking scattered throughout code
- Harder to refactor

**Improvement Option A: Union Types (PHP 8.0+)**
```php
/** @var array<Node|string|int|float|Closure|Stringable|null> */
protected array $children = [];
```

**Improvement Option B: Dedicated Child Wrapper**
```php
class NodeChild
{
    public function __construct(
        private Node|string|int|float|Closure|Stringable|null $value
    ) {}
    
    public function resolve(Node $parent): Node|string|int|float
    {
        if ($this->value instanceof Closure) {
            return ($this->value)($parent);
        }
        return $this->value;
    }
    
    public function getValue(): mixed
    {
        return $this->value;
    }
}

// Usage in Node class:
/** @var array<NodeChild> */
protected array $children = [];
```

**Pros/Cons:**

| Approach | Pros | Cons |
|----------|------|------|
| Current (untyped) | ✅ Simple<br>✅ Flexible | ❌ No type safety<br>❌ Runtime errors |
| Union type annotation | ✅ Documentation<br>✅ Static analysis | ⚠️ Still runtime checks<br>⚠️ Verbose |
| Wrapper class | ✅ Full type safety<br>✅ Centralized logic | ❌ More complex<br>❌ Performance overhead |

**Recommendation:** Use union type annotations (Option A) for now, consider wrapper if static analysis becomes critical.

---

#### ❌ Issue 2: No Child Manipulation Methods

**Current:**
```php
// Can add children
public function appendChild($child): static
public function appendChildren(array $children): static

// ❌ Can't do:
// - Remove child
// - Replace child
// - Insert at position
// - Reorder children
```

**Improvement: Add Manipulation Methods**
```php
abstract class Node
{
    // Existing
    public function appendChild($child): static { /* ... */ }
    
    // NEW: Child manipulation
    public function removeChild(Node $child): static
    {
        $this->children = array_filter(
            $this->children,
            fn($c) => $c !== $child
        );
        return $this;
    }
    
    public function replaceChild(Node $old, Node $new): static
    {
        $this->children = array_map(
            fn($c) => $c === $old ? $new : $c,
            $this->children
        );
        $new->setParent($this);
        return $this;
    }
    
    public function insertBefore(Node $newChild, Node $refChild): static
    {
        $index = array_search($refChild, $this->children, true);
        if ($index !== false) {
            array_splice($this->children, $index, 0, [$newChild]);
            $newChild->setParent($this);
        }
        return $this;
    }
    
    public function getChildAt(int $index): mixed
    {
        return $this->children[$index] ?? null;
    }
    
    public function getChildCount(): int
    {
        return count($this->children);
    }
}
```

**Use Cases:**
```php
// Dynamic form building
$form = _form();
$form->appendChild(_input()->name('email'));

if ($showPassword) {
    $form->insertBefore(
        _input()->type('password')->name('password'),
        $form->getChildAt(0)  // Insert before email
    );
}

// Component modification
$card = Card('Title', 'Content');
$oldButton = $card->getChildAt(2);
$card->replaceChild($oldButton, _button('New Action'));
```

---

#### ❌ Issue 3: No Sibling Navigation

**Current:**
```php
// Can only navigate: parent -> children
$parent = $node->getParent();
$children = $node->getChildren();

// ❌ Can't navigate: previous/next sibling
```

**Improvement: Add Sibling Methods**
```php
abstract class Node
{
    public function getNextSibling(): ?Node
    {
        if (!$this->parent) {
            return null;
        }
        
        $siblings = $this->parent->getChildren();
        $index = array_search($this, $siblings, true);
        
        if ($index === false || $index >= count($siblings) - 1) {
            return null;
        }
        
        $next = $siblings[$index + 1];
        return $next instanceof Node ? $next : null;
    }
    
    public function getPreviousSibling(): ?Node
    {
        if (!$this->parent) {
            return null;
        }
        
        $siblings = $this->parent->getChildren();
        $index = array_search($this, $siblings, true);
        
        if ($index === false || $index <= 0) {
            return null;
        }
        
        $prev = $siblings[$index - 1];
        return $prev instanceof Node ? $prev : null;
    }
    
    public function getSiblings(): array
    {
        if (!$this->parent) {
            return [];
        }
        
        return array_filter(
            $this->parent->getChildren(),
            fn($c) => $c instanceof Node && $c !== $this
        );
    }
}
```

**Use Cases:**
```php
// Add separator between siblings
$items = _ul(
    _li('Item 1'),
    _li('Item 2'),
    _li('Item 3')
);

$items->use(function($ul) {
    foreach ($ul->getChildren() as $li) {
        if ($li instanceof Node && $li->getNextSibling()) {
            $li->classes('mb-2');  // Add margin if not last
        }
    }
});
```

---

#### ⚠️ Issue 4: Parent Tracking Inconsistency

**Current:**
```php
// Parent is set in appendChild()
public function appendChild($child): static
{
    if ($child instanceof Node) {
        $child->setParent($this);  // ✅ Parent set
    }
    $this->children[] = $child;
    return $this;
}

// But also set in childrenToString()
protected function childrenToString(): string
{
    foreach ($this->children as $child) {
        if ($child instanceof Node) {
            $child->setParent($this);  // ⚠️ Set again!
        }
    }
}
```

**Problem:** Parent is set in two places, which is redundant and could cause issues if node is moved.

**Improvement: Set Parent Only Once**
```php
public function appendChild($child): static
{
    if ($child === null) {
        return $this;
    }
    
    // Ensure child is removed from old parent
    if ($child instanceof Node) {
        if ($child->parent && $child->parent !== $this) {
            $child->parent->removeChild($child);
        }
        $child->setParent($this);
    }
    
    $this->children[] = $child;
    return $this;
}

// Remove redundant parent setting from childrenToString()
protected function childrenToString(): string
{
    $strArr = [];
    foreach ($this->children as $child) {
        if ($child instanceof Closure) {
            $child = $child($this);
        }

        if ($child instanceof Node) {
            // Don't set parent here - already set in appendChild
            $strArr[] = $child->__toString();
        } elseif (is_string($child)) {
            $strArr[] = htmlspecialchars($child, static::ENT_FLAGS);
        }
        // ... rest
    }
    return implode(" ", $strArr);
}
```

---

#### ❌ Issue 5: HtmlNode Attributes Structure

**Current:**
```php
protected array $attrs = [];  // Untyped associative array

// Can contain:
// - Regular attrs: ['id' => 'foo']
// - Special '_' key: ['_' => 'raw string']
// - Closures: ['class' => fn() => 'dynamic']
// - Booleans: ['disabled' => true]
```

**Problems:**
- No type safety for attribute values
- Special `_` key is magic (undocumented in type)
- Closure attributes are undiscoverable

**Improvement Option A: Dedicated Attribute Class**
```php
class AttributeCollection implements ArrayAccess, IteratorAggregate
{
    private array $attrs = [];
    
    public function set(string $key, mixed $value): self
    {
        // Validate attribute name
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9\-_]*$/', $key) && $key !== '_') {
            throw new InvalidArgumentException("Invalid attribute name: {$key}");
        }
        
        $this->attrs[$key] = $value;
        return $this;
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attrs[$key] ?? $default;
    }
    
    public function has(string $key): bool
    {
        return isset($this->attrs[$key]);
    }
    
    public function remove(string $key): self
    {
        unset($this->attrs[$key]);
        return $this;
    }
    
    public function merge(array $attrs): self
    {
        foreach ($attrs as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }
    
    public function toArray(): array
    {
        return $this->attrs;
    }
    
    // ArrayAccess implementation
    public function offsetExists($offset): bool { return $this->has($offset); }
    public function offsetGet($offset): mixed { return $this->get($offset); }
    public function offsetSet($offset, $value): void { $this->set($offset, $value); }
    public function offsetUnset($offset): void { $this->remove($offset); }
    
    // IteratorAggregate
    public function getIterator(): Traversable { return new ArrayIterator($this->attrs); }
}

// In HtmlNode:
protected AttributeCollection $attrs;

public function __construct(/* ... */)
{
    $this->attrs = new AttributeCollection();
    $this->attrs->merge($attrs);
}
```

**Improvement Option B: Typed Array with Enum**
```php
enum AttributeType
{
    case String;
    case Boolean;
    case Closure;
    case Raw;  // The '_' key
}

class TypedAttribute
{
    public function __construct(
        public readonly string $name,
        public readonly mixed $value,
        public readonly AttributeType $type
    ) {}
}

// In HtmlNode:
/** @var array<string, TypedAttribute> */
protected array $attrs = [];
```

**Recommendation:** Keep current simple array for performance, but add getter/setter methods with validation:

```php
class HtmlNode extends Node
{
    protected array $attrs = [];
    
    public function setAttribute(string $key, mixed $value): static
    {
        // Validate
        if ($value !== null && !is_scalar($value) && !($value instanceof Closure)) {
            throw new InvalidArgumentException(
                "Attribute value must be scalar, Closure, or null"
            );
        }
        
        $this->attrs[$key] = $value;
        return $this;
    }
    
    public function removeAttribute(string $key): static
    {
        unset($this->attrs[$key]);
        return $this;
    }
    
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attrs);
    }
}
```

---

#### ⚠️ Issue 6: ArrayMapNode One-Time Use Pattern

**Current:**
```php
public function __toString()
{
    // ... populate children ...
    $result = $this->childrenToString();
    
    // Clear children after rendering!
    unset($this->children);
    $this->children = [];
    
    return $result;
}
```

**Problems:**
- Can't render twice (second render is empty)
- Violates immutability principle
- Confusing behavior

**Improvement: Make Idempotent**
```php
class ArrayMapNode extends Node
{
    private ?string $cachedResult = null;
    
    public function __toString()
    {
        // Return cached result if already rendered
        if ($this->cachedResult !== null) {
            return $this->cachedResult;
        }
        
        if ($this->arr === null) {
            return "";
        }

        $parent = $this->getParent();
        $mapFunc = $this->mapFunction;
        
        $tempChildren = [];
        
        if ($this->mapFunction === null) {
            foreach ($this->arr as $child) {
                if ($child instanceof Node) {
                    $child->setParent($parent);
                }
                $tempChildren[] = $child;
            }
        } elseif (is_callable($this->mapFunction)) {
            $mapFunc = Closure::fromCallable($this->mapFunction)->bindTo($parent);
            foreach ($this->arr as $key => $current) {
                $child = $mapFunc($current, $key);
                if ($child instanceof Node) {
                    $child->setParent($parent);
                }
                $tempChildren[] = $child;
            }
        }
        
        // Temporarily set children
        $oldChildren = $this->children;
        $this->children = $tempChildren;
        
        // Render and cache
        $this->cachedResult = $this->childrenToString();
        
        // Restore (optional - or keep for inspection)
        // $this->children = $oldChildren;
        
        return $this->cachedResult;
    }
}
```

---

### Recommended Data Structure Improvements

#### Priority 1: High Impact, Low Complexity

```php
abstract class Node
{
    /** @var array<Node|string|int|float|Closure|Stringable|null> */
    protected array $children = [];
    
    // Add child manipulation
    public function removeChild(Node $child): static { /* ... */ }
    public function replaceChild(Node $old, Node $new): static { /* ... */ }
    public function insertBefore(Node $new, Node $ref): static { /* ... */ }
    public function getChildAt(int $index): mixed { /* ... */ }
    
    // Add sibling navigation
    public function getNextSibling(): ?Node { /* ... */ }
    public function getPreviousSibling(): ?Node { /* ... */ }
}

class HtmlNode extends Node
{
    /** @var array<string, mixed> */
    protected array $attrs = [];
    
    // Add attribute manipulation
    public function removeAttribute(string $key): static { /* ... */ }
    public function hasAttribute(string $key): bool { /* ... */ }
}
```

#### Priority 2: Consider for v2.0

- Full AttributeCollection class
- NodeChild wrapper class
- Immutable node variants
- Tree query/traversal system (XPath-like)

---

## Part 2: Short Function Syntax Analysis

### Current Syntax

```php
_div()
_p()
_html()
_if()
_raw()
```

### Analysis

#### ✅ Pros of Current Approach

1. **Short & Concise**
   ```php
   // Current (clean)
   _div(_p('Hello'))
   
   // vs alternatives
   div(p('Hello'))           // Collision risk
   html()->div()->p('Hello') // Verbose
   Html::div(Html::p('Hello')) // Very verbose
   ```

2. **Namespace Safety**
   - Underscore prefix reduces collision risk
   - Won't conflict with PHP built-ins
   - Won't conflict with user functions

3. **Visual Grouping**
   - Easy to identify VDom functions at a glance
   - Consistent prefix creates visual pattern

4. **IDE Support**
   - Type `_` and IDE shows all VDom functions
   - Autocomplete works well
   - Go-to-definition works (they're functions)

#### ⚠️ Cons of Current Approach

1. **Underscore Prefix Convention**
   - PHP convention: leading underscore means "private"
   - These are public functions, so it's counter-intuitive
   - PSR-12 discourages leading underscores (for methods)

2. **Formatter Compatibility**
   ```php
   // Some formatters treat these specially:
   _div(
       _p('Paragraph'),
       _span('Text')
   );
   
   // vs formatted as:
   _div(
       _p('Paragraph'), _span('Text')
   ); // Single line (not ideal)
   ```

3. **Discoverability**
   - New developers might not know to type `_`
   - Not obvious these are VDom functions

4. **Function Pollution**
   - 150+ functions in global namespace
   - Can't tree-shake if using only subset

---

### Alternative Approaches

#### Option 1: No Prefix (Risky)

```php
// Without underscore
div($content)
p($content)
html($content)

// Pros:
// ✅ Cleanest syntax
// ✅ Most readable
// ✅ Matches JSX/React mental model

// Cons:
// ❌ Collision with user functions
// ❌ Could conflict with future PHP functions
// ❌ Less clear these are special
```

**Verdict:** Too risky. PHP might add `div()` or `span()` functions in future.

---

#### Option 2: Different Prefix (h, v, tag)

```php
// h prefix (h = html)
h_div($content)
h_p($content)
h_html($content)

// v prefix (v = vdom)
v_div($content)
v_p($content)

// tag prefix
tag_div($content)
tag_p($content)

// Pros:
// ✅ More descriptive
// ✅ Still namespace safe
// ✅ Follows PSR underscore convention better

// Cons:
// ⚠️ More verbose
// ⚠️ Migration needed
```

**Verdict:** Slight improvement but not worth migration cost.

---

#### Option 3: Builder Pattern

```php
// Static builder
Html::div($content)
Html::p($content)
Html::html($content)

// Chained builder
html()->div()->p($content)

// Pros:
// ✅ Explicit namespace (Html class)
// ✅ IDE autocomplete on Html::
// ✅ No function pollution
// ✅ PSR compliant

// Cons:
// ❌ Much more verbose
// ❌ Less readable for nested trees
// ❌ Chaining can be confusing

// Example comparison:
// Current:
_div(['class' => 'card'],
    _h3('Title'),
    _p('Content')
)

// Builder:
Html::div(['class' => 'card'],
    Html::h3('Title'),
    Html::p('Content')
)
```

**Verdict:** More explicit but much more verbose.

---

#### Option 4: Hybrid Approach

```php
// Namespace for organization
namespace IceTea\IceDOM\Tags;

function div(...$args): HtmlNode { /* ... */ }
function p(...$args): HtmlNode { /* ... */ }

// Usage with import
use function IceTea\IceDOM\Tags\{div, p, span};

div(
    p('Content'),
    span('More')
);

// Or alias
use function IceTea\IceDOM\Tags\div as _div;

// Pros:
// ✅ Clean at call site (with imports)
// ✅ Explicit namespace
// ✅ Optional underscore via alias
// ✅ Can import only needed functions

// Cons:
// ❌ Requires use statements (verbose)
// ❌ Different per file
// ⚠️ Still collision risk within namespace
```

**Verdict:** Best for libraries, but requires setup.

---

#### Option 5: Single Entry Point

```php
// All through one function
function tag(string $name, ...$args): HtmlNode
{
    return HtmlNode::tag($name, $args[0] ?? null, $args[1] ?? null);
}

// Usage
tag('div', ['class' => 'card'],
    tag('h3', 'Title'),
    tag('p', 'Content')
)

// Pros:
// ✅ Single function (no pollution)
// ✅ No collision risk
// ✅ Dynamic tag names possible

// Cons:
// ❌ Lose type safety
// ❌ No IDE autocomplete for tag names
// ❌ Very verbose
// ❌ Typos not caught
```

**Verdict:** Too much lost for little gain.

---

### Formatter & IDE Compatibility

#### PHP-CS-Fixer / PHP_CodeSniffer

```php
// Current code
_div(
    _p('Paragraph'),
    _span('Text'),
    _if($condition)(
        _strong('Bold')
    )
);

// Formatted well by:
// ✅ PHP-CS-Fixer (with proper config)
// ✅ php-cs-fixer/shim
// ✅ PHP_CodeSniffer (PSR-12)

// Config for PHP-CS-Fixer:
// .php-cs-fixer.php
return (new PhpCsFixer\Config())
    ->setRules([
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
    ]);
```

**Result:** Works well with proper formatter config.

---

#### PhpStorm / IntelliJ

```php
// PhpStorm features that work:
// ✅ Go to definition (_div -> vdom_tags file)
// ✅ Autocomplete (type _ and see all)
// ✅ Parameter hints
// ✅ Type inference (knows it returns HtmlNode)
// ✅ Refactoring (rename, move, etc.)

// To improve PhpStorm support:
// Add @return annotations to generated functions
function _div(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null): HtmlNode
{
    return HtmlNode::tag('div', $arg, $children, false);
}
```

**Result:** Excellent IDE support already.

---

#### VSCode + Intelephense

```php
// VSCode features that work:
// ✅ Go to definition
// ✅ Autocomplete
// ✅ Parameter hints
// ⚠️ Type inference (depends on PHPDoc)

// To improve VSCode support:
// Ensure functions have proper docblocks
/**
 * Create a div element
 * 
 * @param null|string|array|Closure|Node|SafeString $arg
 * @param array|null $children
 * @return HtmlNode
 */
function _div($arg = null, ?array $children = null): HtmlNode
```

**Result:** Good support with proper docs.

---

### Recommendations

#### Keep Current Syntax ✅

**Reasons:**
1. **Battle-tested**: Current syntax works well
2. **Migration Cost**: Changing would break existing code
3. **Good Enough**: Formatter and IDE support is good
4. **Readable**: Short syntax is key benefit

**Minor Improvements:**

```php
// 1. Add comprehensive PHPDoc to generated functions
/**
 * Create a <div> HTML element
 * 
 * @param null|string|array{_?: string, class?: string, id?: string}|Closure|Node|SafeString $arg
 *   - string: CSS class names
 *   - array: HTML attributes
 *   - Node: Single child
 *   - SafeString: Raw HTML content
 * @param array<Node|string|int|Closure|null>|null $children Child nodes
 * @return HtmlNode The created div element
 * 
 * @example
 *   _div('Hello')  // <div>Hello</div>
 *   _div(['class' => 'card'], _p('Content'))
 */
function _div($arg = null, ?array $children = null): HtmlNode

// 2. Provide .phpstorm.meta.php for better IDE support
// .phpstorm.meta.php
namespace PHPSTORM_META {
    override(\IceTea\IceDOM\HtmlNode::tag(0), type(0));
}

// 3. Add psalm/phpstan config
// psalm.xml
<issueHandlers>
    <UnusedFunctionCall>
        <errorLevel type="suppress">
            <file name="src/vdom_tags.php"/>
        </errorLevel>
    </UnusedFunctionCall>
</issueHandlers>
```

---

### PHP Formatter Configuration

#### Recommended .php-cs-fixer.php

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/packages/vdom');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        
        // Ensure multiline arrays stay multiline
        'array_syntax' => ['syntax' => 'short'],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        
        // Don't mess with function call formatting
        'no_spaces_inside_parenthesis' => true,
        'function_declaration' => [
            'closure_function_spacing' => 'one',
        ],
        
        // Allow underscore prefix functions
        'php_unit_method_casing' => ['case' => 'snake_case'],
    ])
    ->setFinder($finder);
```

---

## Summary & Recommendations

### Data Structure: Improvement Priority

| Improvement | Effort | Value | Priority |
|-------------|--------|-------|----------|
| Add child manipulation methods | Low | High | ✅ Do now |
| Add type annotations | Low | High | ✅ Do now |
| Fix parent tracking | Low | Medium | ✅ Do now |
| Add sibling navigation | Medium | Medium | ⚠️ Consider |
| AttributeCollection class | High | Low | ⏳ Future |
| Fix ArrayMapNode caching | Medium | Medium | ⚠️ Consider |

### Syntax: Keep Current with Enhancements

| Aspect | Current Grade | Recommendation |
|--------|--------------|----------------|
| Syntax choice | A | ✅ Keep `_div()` pattern |
| PHPDoc | C | 📝 Add comprehensive docs |
| IDE support | B+ | 📝 Add .phpstorm.meta.php |
| Formatter | B | 📝 Add .php-cs-fixer.php |

### Action Items

**High Priority:**
1. Add type annotations to all arrays in classes
2. Add child manipulation methods (remove, replace, insert)
3. Fix parent tracking (only set in appendChild)
4. Add comprehensive PHPDoc to generated functions

**Medium Priority:**
5. Add sibling navigation methods
6. Fix ArrayMapNode caching behavior
7. Create .phpstorm.meta.php for IDE
8. Document formatter config

**Low Priority:**
9. Consider AttributeCollection for v2.0
10. Consider NodeChild wrapper for v2.0

---

**Conclusion:** 

Your current data structure is **good and functional**, but has room for improvement in:
- Type safety (add annotations)
- Node manipulation (add methods)
- Consistency (fix parent tracking)

Your short function syntax (`_div()`) is **good for DX** and works well with:
- ✅ PHP formatters (with proper config)
- ✅ PhpStorm IDE (excellent support)
- ✅ VSCode/Intelephense (good support with docs)

The underscore prefix is unconventional but practical - **keep it**.
