# Styled Components in IceDOM

IceDOM now supports scoped CSS styling using the Style Registry pattern! This allows you to write component-specific CSS that won't conflict with other styles on your page.

## Quick Start

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/generated_html_tags.php';
require_once __DIR__ . '/src/styled_helpers.php';

use function IceTea\IceDOM\styled;
use function IceTea\IceDOM\_styles;
use function IceTea\IceDOM\_styled;

// Create a styled button component
$StyledButton = styled('button', [
    'padding' => '0.5rem 1rem',
    'background' => '#007bff',
    'color' => 'white',
    'border' => 'none',
    'border-radius' => '4px',
    'cursor' => 'pointer',
    
    '&:hover' => [
        'background' => '#0056b3',
    ],
    
    '&:active' => [
        'transform' => 'scale(0.98)',
    ]
]);

// Use the styled component
$button = $StyledButton(['Click Me!']);
```

## Features

### ✨ Scoped Styles
All styles are automatically scoped to prevent conflicts. Each component gets a unique class name (e.g., `c-a3f8b9c4`).

### 🎯 Nested CSS Support
Use `&` for parent selector, just like SCSS:

```php
$StyledCard = styled('div', [
    'padding' => '1rem',
    'background' => 'white',
    
    '& .title' => [
        'font-size' => '1.5rem',
        'font-weight' => 'bold',
    ],
    
    '&:hover' => [
        'box-shadow' => '0 2px 8px rgba(0,0,0,0.1)',
    ]
]);
```

### 🔄 Automatic Deduplication
Identical styles are automatically deduplicated - they'll share the same scope class.

### ⚡ CSS Minification
Optionally minify CSS for production:

```php
echo _styles(minify: true);
```

## Complete Example

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/generated_html_tags.php';
require_once __DIR__ . '/src/styled_helpers.php';

use function IceTea\IceDOM\styled;
use function IceTea\IceDOM\_styles;
use function IceTea\IceDOM\_html;
use function IceTea\IceDOM\_head;
use function IceTea\IceDOM\_title;
use function IceTea\IceDOM\_body;
use function IceTea\IceDOM\_div;
use function IceTea\IceDOM\_h1;
use function IceTea\IceDOM\_p;

// Define styled components
$StyledCard = styled('div', [
    'background' => 'white',
    'padding' => '1.5rem',
    'border-radius' => '8px',
    'box-shadow' => '0 2px 8px rgba(0,0,0,0.1)',
    'margin' => '1rem',
    
    '& .card-title' => [
        'font-size' => '1.5rem',
        'font-weight' => 'bold',
        'margin-bottom' => '1rem',
        'color' => '#333',
    ],
    
    '& .card-body' => [
        'line-height' => '1.6',
        'color' => '#555',
    ],
    
    '&:hover' => [
        'box-shadow' => '0 4px 12px rgba(0,0,0,0.15)',
        'transform' => 'translateY(-2px)',
        'transition' => 'all 0.3s ease',
    ]
]);

$StyledButton = styled('button', [
    'padding' => '0.5rem 1rem',
    'background' => '#007bff',
    'color' => 'white',
    'border' => 'none',
    'border-radius' => '4px',
    'cursor' => 'pointer',
    'font-size' => '1rem',
    
    '&:hover' => [
        'background' => '#0056b3',
    ],
    
    '&:active' => [
        'transform' => 'scale(0.98)',
    ]
]);

// Build the page
$page = _html('lang="en"', [
    _head([
        _title(['Styled Components Demo']),
        _styles()  // Output all collected styles
    ]),
    _body([
        _h1(['Styled Components in IceDOM']),
        
        $StyledCard(['class' => 'main-card'], [
            _div(['class' => 'card-title'], ['Welcome to IceDOM']),
            _div(['class' => 'card-body'], [
                'Build beautiful components with scoped CSS, right in PHP!',
            ]),
            $StyledButton(['Learn More'])
        ]),
        
        $StyledCard([
            _div(['class' => 'card-title'], ['Another Card']),
            _div(['class' => 'card-body'], [
                'Styles are automatically scoped and won\'t conflict.',
            ]),
            $StyledButton(['Get Started'])
        ])
    ])
]);

echo $page;
```

## API Reference

### `styled(string $tag, array $cssRules): Closure`

Creates a reusable styled component factory.

**Parameters:**
- `$tag` - HTML tag name (div, button, span, etc.)
- `$cssRules` - Nested array of CSS rules

**Returns:** A callable that creates styled HTML nodes

**Example:**
```php
$StyledDiv = styled('div', [
    'color' => 'red',
    '&:hover' => ['color' => 'blue']
]);

$element = $StyledDiv(['Content']);
```

### `_styled(string $tag, array $cssRules, mixed $firstArg, ?array $children): HtmlNode`

Creates a one-off styled element directly.

**Example:**
```php
$element = _styled('span', [
    'color' => 'green'
], ['Text content']);
```

### `_styles(bool $minify = false): HtmlNode`

Outputs all collected styles as a `<style>` tag.

**Parameters:**
- `$minify` - Whether to minify the CSS (default: false)

**Example:**
```php
// In your <head>
echo _styles();

// Or minified for production
echo _styles(minify: true);
```

## CSS Nested Syntax

### Parent Selector (`&`)

```php
styled('button', [
    'color' => 'blue',
    '&:hover' => ['color' => 'darkblue'],      // button:hover
    '&:active' => ['color' => 'navy'],          // button:active
    '&.primary' => ['background' => 'blue'],    // button.primary
]);
```

### Descendant Selectors

```php
styled('div', [
    'padding' => '1rem',
    '& .child' => ['margin' => '0.5rem'],       // div .child
    '& > .direct' => ['color' => 'red'],        // div > .direct
]);
```

### Deeply Nested

```php
styled('article', [
    'padding' => '2rem',
    '& header' => [
        'margin-bottom' => '1rem',
        '& h1' => [
            'font-size' => '2rem',
            '&:hover' => ['text-decoration' => 'underline']
        ]
    ]
]);
```

## How It Works

1. **Registration**: When you create a styled component, it registers CSS rules with the global `StyleRegistry`
2. **Scope Generation**: A unique scope class (e.g., `c-a3f8b9c4`) is generated based on the CSS rules
3. **Deduplication**: If identical rules already exist, the same scope class is reused
4. **Compilation**: When you call `_styles()`, all registered CSS is compiled into proper CSS syntax
5. **Output**: A single `<style>` tag contains all scoped styles for your components

## Best Practices

### ✅ DO: Define styled components once

```php
$StyledButton = styled('button', [...]);

// Reuse it multiple times
echo $StyledButton(['Button 1']);
echo $StyledButton(['Button 2']);
```

### ✅ DO: Call _styles() once in your <head>

```php
_head([
    _title(['My App']),
    _styles()  // Single call outputs all styles
])
```

### ✅ DO: Use nested CSS for complex components

```php
styled('div', [
    'padding' => '1rem',
    '& .title' => ['font-size' => '1.5rem'],
    '& .body' => ['color' => '#555']
])
```

### ❌ DON'T: Call _styles() multiple times

```php
// Bad - will output duplicate styles
_head([
    _styles(),
    _styles()  // ❌ Don't do this
])
```

### ❌ DON'T: Mix inline styles with styled components

```php
// Prefer one approach
$StyledDiv = styled('div', ['color' => 'red']);
echo $StyledDiv(['Content']);

// Instead of mixing with inline styles
echo _div(['style' => 'color: red'], ['Content']); // Avoid this pattern
```

## Testing

The styled components feature comes with comprehensive tests:

```bash
# Run styled component tests
vendor/bin/pest tests/StyleRegistryTest.php tests/StyledComponentTest.php

# Or run all tests
vendor/bin/pest
```

## Browser Compatibility

✅ All modern browsers (Chrome, Firefox, Safari, Edge)  
✅ IE9+ (attribute selectors fully supported)  
✅ Mobile browsers (iOS Safari, Chrome Mobile, etc.)

## Performance

- **Deduplication**: Identical styles share the same scope class
- **Minification**: Optional CSS minification for production
- **Single stylesheet**: All styles output in one `<style>` tag
- **Server-side**: No JavaScript required, works with SSR

## Contributing

Found a bug or have a feature request? Please open an issue on GitHub!

---

**Happy styling with IceDOM! 🎨**

