<?php

use IceTea\IceDOM\StyleRegistry;

use function IceTea\IceDOM\_styled;
use function IceTea\IceDOM\_styles;
use function IceTea\IceDOM\styled;

beforeEach(function () {
    // Reset the registry before each test
    StyleRegistry::getInstance()->reset();
});

test('styled() creates a component factory', function () {
    $StyledDiv = styled('div', [
        'color' => 'red',
    ]);

    expect($StyledDiv)->toBeCallable();
});

test('styled component renders with scope class', function () {
    $StyledDiv = styled('div', [
        'color' => 'red',
        'padding' => '1rem',
    ]);

    $component = $StyledDiv(['Hello']);
    $html = (string) $component;

    expect($html)->toContain('class="c-')
        ->and($html)->toContain('>Hello</div>');
});

test('styled component accepts attributes', function () {
    $StyledButton = styled('button', [
        'background' => 'blue',
    ]);

    $component = $StyledButton(['type' => 'submit'], ['Click Me']);
    $html = (string) $component;

    expect($html)->toContain('type="submit"')
        ->and($html)->toContain('class="c-')
        ->and($html)->toContain('>Click Me</button>');
});

test('identical styles produce same scope class', function () {
    $rules = ['color' => 'red'];

    $StyledDiv1 = styled('div', $rules);
    $StyledDiv2 = styled('div', $rules);

    $comp1 = $StyledDiv1(['Test']);
    $comp2 = $StyledDiv2(['Test']);

    // Extract class from HTML
    preg_match('/class="([^"]+)"/', (string) $comp1, $matches1);
    preg_match('/class="([^"]+)"/', (string) $comp2, $matches2);

    expect($matches1[1])->toBe($matches2[1]);
});

test('different styles produce different scope classes', function () {
    $StyledRed = styled('div', ['color' => 'red']);
    $StyledBlue = styled('div', ['color' => 'blue']);

    $comp1 = $StyledRed(['Test']);
    $comp2 = $StyledBlue(['Test']);

    preg_match('/class="([^"]+)"/', (string) $comp1, $matches1);
    preg_match('/class="([^"]+)"/', (string) $comp2, $matches2);

    expect($matches1[1])->not->toBe($matches2[1]);
});

test('_styles() outputs collected styles', function () {
    $StyledDiv = styled('div', [
        'color' => 'red',
        'padding' => '1rem',
    ]);

    $component = $StyledDiv(['Content']);
    $styles = _styles();

    $stylesHtml = (string) $styles;

    expect($stylesHtml)->toContain('<style>')
        ->and($stylesHtml)->toContain('</style>')
        ->and($stylesHtml)->toContain('color: red')
        ->and($stylesHtml)->toContain('padding: 1rem');
});

test('_styles() with minify option', function () {
    styled('div', ['color' => 'red', 'padding' => '1rem']);

    $normalStyles = (string) _styles(false);
    
    // Reset to test minified
    StyleRegistry::getInstance()->reset();
    styled('div', ['color' => 'red', 'padding' => '1rem']);
    $minifiedStyles = (string) _styles(true);

    expect(strlen($minifiedStyles))->toBeLessThan(strlen($normalStyles))
        ->and($minifiedStyles)->not->toContain("\n")
        ->and($minifiedStyles)->toContain('color:red');
});

test('nested CSS in styled components', function () {
    $StyledCard = styled('div', [
        'padding' => '1rem',
        '& .title' => [
            'font-size' => '1.5rem',
            'color' => 'blue',
        ],
        '&:hover' => [
            'background' => 'gray',
        ],
    ]);

    $component = $StyledCard(['class' => 'card'], [
        _div(['class' => 'title'], ['Card Title']),
    ]);

    $styles = (string) _styles();

    expect($styles)->toContain('.title { font-size: 1.5rem')
        ->and($styles)->toContain(':hover { background: gray');
});

test('_styled() creates component directly', function () {
    $component = _styled('div', [
        'color' => 'green',
        'margin' => '0.5rem',
    ], ['Content']);

    $html = (string) $component;

    expect($html)->toContain('class="c-')
        ->and($html)->toContain('>Content</div>');
});

test('_styled() with attributes and children', function () {
    $component = _styled('div', [
        'padding' => '1rem',
    ], ['id' => 'main', 'class' => 'container'], [
        _h1(['Title']),
        _p(['Paragraph']),
    ]);

    $html = (string) $component;

    expect($html)->toContain('id="main"')
        ->and($html)->toContain('class="container c-') // Both classes present
        ->and($html)->toContain('<h1>Title</h1>')
        ->and($html)->toContain('<p>Paragraph</p>');
});

test('complete example with card component', function () {
    $StyledCard = styled('div', [
        'background' => 'white',
        'padding' => '1.5rem',
        'border-radius' => '8px',
        'box-shadow' => '0 2px 8px rgba(0,0,0,0.1)',
        '& .card-title' => [
            'font-size' => '1.5rem',
            'font-weight' => 'bold',
            'margin-bottom' => '1rem',
        ],
        '& .card-body' => [
            'line-height' => '1.6',
            'color' => '#555',
        ],
    ]);

    $card = $StyledCard(['class' => 'card'], [
        _div(['class' => 'card-title'], ['Welcome']),
        _div(['class' => 'card-body'], ['This is a styled card component.']),
    ]);

    $html = (string) $card;
    $styles = (string) _styles();

    // Check HTML structure
    expect($html)->toContain('class="card c-')
        ->and($html)->toContain('class="card-title"')
        ->and($html)->toContain('class="card-body"')
        ->and($html)->toContain('Welcome')
        ->and($html)->toContain('This is a styled card component.');

    // Check compiled styles
    expect($styles)->toContain('background: white')
        ->and($styles)->toContain('padding: 1.5rem')
        ->and($styles)->toContain('border-radius: 8px')
        ->and($styles)->toContain('.card-title { font-size: 1.5rem')
        ->and($styles)->toContain('.card-body { line-height: 1.6');
});

test('multiple styled components in one page', function () {
    $StyledButton = styled('button', [
        'background' => 'blue',
        'color' => 'white',
        'padding' => '0.5rem 1rem',
        '&:hover' => [
            'background' => 'darkblue',
        ],
    ]);

    $StyledAlert = styled('div', [
        'background' => '#f0f0f0',
        'padding' => '1rem',
        'border-left' => '4px solid orange',
    ]);

    $button = $StyledButton(['Submit']);
    $alert = $StyledAlert(['Warning message']);

    $buttonHtml = (string) $button;
    $alertHtml = (string) $alert;
    $styles = (string) _styles();

    // Check both components have scope classes
    expect($buttonHtml)->toContain('class="c-')
        ->and($alertHtml)->toContain('class="c-');

    // Check styles contain both components
    expect($styles)->toContain('background: blue')
        ->and($styles)->toContain('background: #f0f0f0')
        ->and($styles)->toContain('border-left: 4px solid orange')
        ->and($styles)->toContain(':hover { background: darkblue');
});

test('styled component preserves existing classes', function () {
    $StyledDiv = styled('div', [
        'padding' => '1rem',
    ]);

    $component = $StyledDiv(['class' => 'existing-class another-class'], ['Content']);
    $html = (string) $component;

    expect($html)->toContain('class="existing-class another-class c-')
        ->and($html)->toContain('>Content</div>');
});

test('styled component works with string attributes shorthand', function () {
    $StyledSpan = styled('span', [
        'color' => 'red',
    ]);

    $component = $StyledSpan('data-value="123"', ['Text']);
    $html = (string) $component;

    expect($html)->toContain('data-value="123"')
        ->and($html)->toContain('class="c-')
        ->and($html)->toContain('>Text</span>');
});

