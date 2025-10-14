<?php

use IceTea\IceDOM\StyleRegistry;

beforeEach(function () {
    // Reset the registry before each test for isolation
    StyleRegistry::getInstance()->reset();
});

test('StyleRegistry is a singleton', function () {
    $instance1 = StyleRegistry::getInstance();
    $instance2 = StyleRegistry::getInstance();

    expect($instance1)->toBe($instance2);
});

test('can register simple CSS rules', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'color' => 'red',
        'padding' => '1rem',
    ];

    $scopeClass = $registry->register('test-hash', $rules);

    expect($scopeClass)->toBeString()
        ->and($scopeClass)->toStartWith('c-')
        ->and(strlen($scopeClass))->toBe(10); // 'c-' + 8 chars
});

test('deduplicates identical styles', function () {
    $registry = StyleRegistry::getInstance();

    $rules = ['color' => 'blue'];

    $scope1 = $registry->register('hash-1', $rules);
    $scope2 = $registry->register('hash-1', $rules);

    expect($scope1)->toBe($scope2);
});

test('generates different scope classes for different rules', function () {
    $registry = StyleRegistry::getInstance();

    $rules1 = ['color' => 'red'];
    $rules2 = ['color' => 'blue'];

    $scope1 = $registry->register('hash-1', $rules1);
    $scope2 = $registry->register('hash-2', $rules2);

    expect($scope1)->not->toBe($scope2);
});

test('compiles simple CSS rules', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'color' => 'red',
        'padding' => '1rem',
    ];

    $scopeClass = $registry->register('test-hash', $rules);
    $css = $registry->compile();

    expect($css)->toContain(".$scopeClass")
        ->and($css)->toContain('color: red')
        ->and($css)->toContain('padding: 1rem');
});

test('compiles nested CSS rules', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'color' => 'red',
        '& .child' => [
            'color' => 'blue',
        ],
    ];

    $scopeClass = $registry->register('test-hash', $rules);
    $css = $registry->compile();

    expect($css)->toContain(".$scopeClass { color: red")
        ->and($css)->toContain(".$scopeClass .child { color: blue");
});

test('handles parent selector (&) in pseudo-classes', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'color' => 'red',
        '&:hover' => [
            'color' => 'blue',
        ],
    ];

    $scopeClass = $registry->register('test-hash', $rules);
    $css = $registry->compile();

    expect($css)->toContain(".$scopeClass:hover { color: blue");
});

test('handles complex nested selectors', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'padding' => '1rem',
        '& .title' => [
            'font-size' => '1.5rem',
            '&:hover' => [
                'color' => 'blue',
            ],
        ],
        '&:active' => [
            'transform' => 'scale(0.95)',
        ],
    ];

    $scopeClass = $registry->register('test-hash', $rules);
    $css = $registry->compile();

    expect($css)->toContain(".$scopeClass { padding: 1rem")
        ->and($css)->toContain(".$scopeClass .title { font-size: 1.5rem")
        ->and($css)->toContain(".$scopeClass .title:hover { color: blue")
        ->and($css)->toContain(".$scopeClass:active { transform: scale(0.95)");
});

test('minifies CSS when requested', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'color' => 'red',
        'padding' => '1rem',
    ];

    $registry->register('test-hash', $rules);
    
    $normalCss = $registry->compile(false);
    $minifiedCss = $registry->compile(true);

    expect(strlen($minifiedCss))->toBeLessThan(strlen($normalCss))
        ->and($minifiedCss)->not->toContain("\n")
        ->and($minifiedCss)->toContain('color:red')
        ->and($minifiedCss)->toContain('padding:1rem');
});

test('can reset the registry', function () {
    $registry = StyleRegistry::getInstance();

    $registry->register('test-hash', ['color' => 'red']);
    expect($registry->getScopes())->toHaveCount(1);

    $registry->reset();
    expect($registry->getScopes())->toHaveCount(0);
});

test('can get all registered scopes', function () {
    $registry = StyleRegistry::getInstance();

    $scope1 = $registry->register('hash-1', ['color' => 'red']);
    $scope2 = $registry->register('hash-2', ['color' => 'blue']);

    $scopes = $registry->getScopes();

    expect($scopes)->toHaveCount(2)
        ->and($scopes)->toContain($scope1)
        ->and($scopes)->toContain($scope2);
});

test('can get styles for specific scope', function () {
    $registry = StyleRegistry::getInstance();

    $rules = ['color' => 'red', 'padding' => '1rem'];
    $scopeClass = $registry->register('test-hash', $rules);

    $retrievedRules = $registry->getStylesForScope($scopeClass);

    expect($retrievedRules)->toBe($rules);
});

test('returns null for non-existent scope', function () {
    $registry = StyleRegistry::getInstance();

    $rules = $registry->getStylesForScope('non-existent');

    expect($rules)->toBeNull();
});

test('compiles multiple registered styles', function () {
    $registry = StyleRegistry::getInstance();

    $scope1 = $registry->register('hash-1', [
        'color' => 'red',
    ]);

    $scope2 = $registry->register('hash-2', [
        'color' => 'blue',
    ]);

    $css = $registry->compile();

    expect($css)->toContain(".$scope1 { color: red")
        ->and($css)->toContain(".$scope2 { color: blue");
});

test('handles deeply nested selectors', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'color' => 'black',
        '& .level1' => [
            'color' => 'red',
            '& .level2' => [
                'color' => 'blue',
                '& .level3' => [
                    'color' => 'green',
                ],
            ],
        ],
    ];

    $scopeClass = $registry->register('test-hash', $rules);
    $css = $registry->compile();

    expect($css)->toContain(".$scopeClass { color: black")
        ->and($css)->toContain(".$scopeClass .level1 { color: red")
        ->and($css)->toContain(".$scopeClass .level1 .level2 { color: blue")
        ->and($css)->toContain(".$scopeClass .level1 .level2 .level3 { color: green");
});

test('handles media queries', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'padding' => '1rem',
        'color' => 'black',
        '@media (max-width: 768px)' => [
            'padding' => '0.5rem',
            'color' => 'blue',
        ],
    ];

    $scopeClass = $registry->register('test-hash', $rules);
    $css = $registry->compile();

    expect($css)->toContain(".$scopeClass { padding: 1rem")
        ->and($css)->toContain('@media (max-width: 768px)')
        ->and($css)->toContain('padding: 0.5rem')
        ->and($css)->toContain('color: blue');
});

test('handles nested selectors inside media queries', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'padding' => '2rem',
        '& .title' => [
            'font-size' => '2rem',
        ],
        '@media (max-width: 768px)' => [
            'padding' => '1rem',
            '& .title' => [
                'font-size' => '1.5rem',
            ],
        ],
    ];

    $scopeClass = $registry->register('test-hash', $rules);
    $css = $registry->compile();

    expect($css)->toContain(".$scopeClass { padding: 2rem")
        ->and($css)->toContain(".$scopeClass .title { font-size: 2rem")
        ->and($css)->toContain('@media (max-width: 768px)')
        ->and($css)->toContain('padding: 1rem')
        ->and($css)->toContain('font-size: 1.5rem');
});

test('handles multiple media queries', function () {
    $registry = StyleRegistry::getInstance();

    $rules = [
        'font-size' => '16px',
        '@media (max-width: 768px)' => [
            'font-size' => '14px',
        ],
        '@media (max-width: 480px)' => [
            'font-size' => '12px',
        ],
    ];

    $scopeClass = $registry->register('test-hash', $rules);
    $css = $registry->compile();

    expect($css)->toContain(".$scopeClass { font-size: 16px")
        ->and($css)->toContain('@media (max-width: 768px)')
        ->and($css)->toContain('font-size: 14px')
        ->and($css)->toContain('@media (max-width: 480px)')
        ->and($css)->toContain('font-size: 12px');
});

