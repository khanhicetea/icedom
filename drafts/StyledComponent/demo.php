<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../src/generated_html_tags.php';
require_once __DIR__.'/styled_helpers.php';
require_once __DIR__.'/StyleRegistry.php';

use function IceTea\IceDOM\_styles;
use function IceTea\IceDOM\styled;

// ============================================================================
// COMPLEX STYLED COMPONENTS DEMO
// Featuring: Media Queries, Deep Nesting, Advanced Selectors
// ============================================================================

// 1. Responsive Navigation Component with Multi-level Nesting
$StyledNav = styled('nav', [
    'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'padding' => '1rem 2rem',
    'box-shadow' => '0 2px 4px rgba(0,0,0,0.1)',
    'position' => 'sticky',
    'top' => '0',
    'z-index' => '1000',

    '& .nav-container' => [
        'max-width' => '1200px',
        'margin' => '0 auto',
        'display' => 'flex',
        'justify-content' => 'space-between',
        'align-items' => 'center',

        '& .logo' => [
            'font-size' => '1.5rem',
            'font-weight' => 'bold',
            'color' => 'white',
            'text-decoration' => 'none',

            '&:hover' => [
                'opacity' => '0.8',
                'transform' => 'scale(1.05)',
                'transition' => 'all 0.3s ease',
            ],
        ],

        '& .nav-links' => [
            'display' => 'flex',
            'gap' => '2rem',
            'list-style' => 'none',
            'margin' => '0',
            'padding' => '0',

            '& li' => [
                'position' => 'relative',

                '& a' => [
                    'color' => 'white',
                    'text-decoration' => 'none',
                    'padding' => '0.5rem 1rem',
                    'border-radius' => '4px',
                    'transition' => 'all 0.3s ease',

                    '&:hover' => [
                        'background' => 'rgba(255, 255, 255, 0.1)',
                        'transform' => 'translateY(-2px)',
                    ],

                    '&:active' => [
                        'transform' => 'translateY(0)',
                    ],

                    '&::after' => [
                        'content' => '""',
                        'position' => 'absolute',
                        'bottom' => '0',
                        'left' => '0',
                        'width' => '0',
                        'height' => '2px',
                        'background' => 'white',
                        'transition' => 'width 0.3s ease',
                    ],
                ],

                '&:hover a::after' => [
                    'width' => '100%',
                ],
            ],

            // Mobile responsive
            '@media (max-width: 768px)' => [
                'flex-direction' => 'column',
                'gap' => '1rem',
            ],
        ],
    ],
]);

// 2. Hero Section with Advanced Gradients and Animations
$StyledHero = styled('section', [
    'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'padding' => '6rem 2rem',
    'text-align' => 'center',
    'position' => 'relative',
    'overflow' => 'hidden',

    '&::before' => [
        'content' => '""',
        'position' => 'absolute',
        'top' => '-50%',
        'right' => '-50%',
        'width' => '100%',
        'height' => '100%',
        'background' => 'radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)',
        'animation' => 'pulse 4s ease-in-out infinite',
    ],

    '& .hero-content' => [
        'position' => 'relative',
        'z-index' => '1',
        'max-width' => '800px',
        'margin' => '0 auto',

        '& h1' => [
            'font-size' => '3.5rem',
            'color' => 'white',
            'margin-bottom' => '1rem',
            'font-weight' => 'bold',
            'text-shadow' => '2px 2px 4px rgba(0,0,0,0.2)',

            '@media (max-width: 768px)' => [
                'font-size' => '2rem',
            ],

            '@media (max-width: 480px)' => [
                'font-size' => '1.5rem',
            ],
        ],

        '& p' => [
            'font-size' => '1.25rem',
            'color' => 'rgba(255, 255, 255, 0.9)',
            'margin-bottom' => '2rem',
            'line-height' => '1.8',

            '@media (max-width: 768px)' => [
                'font-size' => '1rem',
            ],
        ],

        '& .cta-button' => [
            'background' => 'white',
            'color' => '#667eea',
            'padding' => '1rem 2.5rem',
            'font-size' => '1.1rem',
            'border' => 'none',
            'border-radius' => '50px',
            'cursor' => 'pointer',
            'font-weight' => 'bold',
            'box-shadow' => '0 4px 15px rgba(0,0,0,0.2)',
            'transition' => 'all 0.3s ease',

            '&:hover' => [
                'transform' => 'translateY(-3px)',
                'box-shadow' => '0 6px 20px rgba(0,0,0,0.3)',
            ],

            '&:active' => [
                'transform' => 'translateY(-1px)',
            ],
        ],
    ],
]);

// 3. Feature Card Grid with Complex Hover Effects
$StyledCard = styled('div', [
    'background' => 'white',
    'border-radius' => '12px',
    'padding' => '2rem',
    'box-shadow' => '0 2px 8px rgba(0,0,0,0.1)',
    'transition' => 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
    'position' => 'relative',
    'overflow' => 'hidden',

    '&::before' => [
        'content' => '""',
        'position' => 'absolute',
        'top' => '0',
        'left' => '0',
        'width' => '100%',
        'height' => '4px',
        'background' => 'linear-gradient(90deg, #667eea 0%, #764ba2 100%)',
        'transform' => 'scaleX(0)',
        'transition' => 'transform 0.3s ease',
    ],

    '&:hover' => [
        'transform' => 'translateY(-8px)',
        'box-shadow' => '0 12px 24px rgba(0,0,0,0.15)',

        '&::before' => [
            'transform' => 'scaleX(1)',
        ],
    ],

    '& .card-icon' => [
        'width' => '60px',
        'height' => '60px',
        'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'border-radius' => '12px',
        'display' => 'flex',
        'align-items' => 'center',
        'justify-content' => 'center',
        'margin-bottom' => '1.5rem',
        'font-size' => '2rem',
        'transition' => 'transform 0.3s ease',

        '&:hover' => [
            'transform' => 'rotate(10deg) scale(1.1)',
        ],
    ],

    '& .card-title' => [
        'font-size' => '1.5rem',
        'font-weight' => 'bold',
        'margin-bottom' => '1rem',
        'color' => '#333',

        '& span' => [
            'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'background-clip' => 'text',
            '-webkit-background-clip' => 'text',
            '-webkit-text-fill-color' => 'transparent',
        ],
    ],

    '& .card-description' => [
        'color' => '#666',
        'line-height' => '1.8',
        'margin-bottom' => '1.5rem',

        '& strong' => [
            'color' => '#333',
            'font-weight' => '600',
        ],
    ],

    '& .card-footer' => [
        'display' => 'flex',
        'align-items' => 'center',
        'gap' => '1rem',
        'padding-top' => '1rem',
        'border-top' => '1px solid #eee',

        '& .badge' => [
            'padding' => '0.25rem 0.75rem',
            'border-radius' => '20px',
            'font-size' => '0.875rem',
            'font-weight' => '500',

            '&.badge-new' => [
                'background' => '#e8f5e9',
                'color' => '#2e7d32',
            ],

            '&.badge-hot' => [
                'background' => '#ffebee',
                'color' => '#c62828',
            ],

            '&.badge-pro' => [
                'background' => '#e3f2fd',
                'color' => '#1565c0',
            ],
        ],
    ],
]);

// 4. Card Grid Container with Responsive Layout
$StyledGrid = styled('div', [
    'max-width' => '1200px',
    'margin' => '4rem auto',
    'padding' => '0 2rem',

    '& .section-title' => [
        'text-align' => 'center',
        'font-size' => '2.5rem',
        'font-weight' => 'bold',
        'margin-bottom' => '3rem',
        'color' => '#333',

        '&::after' => [
            'content' => '""',
            'display' => 'block',
            'width' => '60px',
            'height' => '4px',
            'background' => 'linear-gradient(90deg, #667eea 0%, #764ba2 100%)',
            'margin' => '1rem auto 0',
            'border-radius' => '2px',
        ],
    ],

    '& .grid' => [
        'display' => 'grid',
        'grid-template-columns' => 'repeat(3, 1fr)',
        'gap' => '2rem',

        '@media (max-width: 1024px)' => [
            'grid-template-columns' => 'repeat(2, 1fr)',
        ],

        '@media (max-width: 768px)' => [
            'grid-template-columns' => '1fr',
            'gap' => '1.5rem',
        ],
    ],
]);

// 5. Stats Counter Component with Animations
$StyledStats = styled('section', [
    'background' => '#f8f9fa',
    'padding' => '4rem 2rem',

    '& .stats-container' => [
        'max-width' => '1200px',
        'margin' => '0 auto',
        'display' => 'grid',
        'grid-template-columns' => 'repeat(4, 1fr)',
        'gap' => '2rem',

        '@media (max-width: 768px)' => [
            'grid-template-columns' => 'repeat(2, 1fr)',
        ],

        '@media (max-width: 480px)' => [
            'grid-template-columns' => '1fr',
        ],

        '& .stat-item' => [
            'text-align' => 'center',
            'padding' => '2rem',
            'background' => 'white',
            'border-radius' => '12px',
            'box-shadow' => '0 2px 8px rgba(0,0,0,0.05)',
            'transition' => 'transform 0.3s ease',

            '&:hover' => [
                'transform' => 'scale(1.05)',

                '& .stat-number' => [
                    'transform' => 'scale(1.1)',
                ],
            ],

            '& .stat-number' => [
                'font-size' => '3rem',
                'font-weight' => 'bold',
                'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'background-clip' => 'text',
                '-webkit-background-clip' => 'text',
                '-webkit-text-fill-color' => 'transparent',
                'margin-bottom' => '0.5rem',
                'transition' => 'transform 0.3s ease',
            ],

            '& .stat-label' => [
                'color' => '#666',
                'font-size' => '1rem',
                'text-transform' => 'uppercase',
                'letter-spacing' => '1px',
            ],
        ],
    ],
]);

// 6. Footer Component with Multi-column Layout
$StyledFooter = styled('footer', [
    'background' => '#2c3e50',
    'color' => 'white',
    'padding' => '3rem 2rem 1rem',

    '& .footer-content' => [
        'max-width' => '1200px',
        'margin' => '0 auto',
        'display' => 'grid',
        'grid-template-columns' => 'repeat(4, 1fr)',
        'gap' => '2rem',
        'margin-bottom' => '2rem',

        '@media (max-width: 768px)' => [
            'grid-template-columns' => 'repeat(2, 1fr)',
        ],

        '@media (max-width: 480px)' => [
            'grid-template-columns' => '1fr',
        ],

        '& .footer-section' => [
            '& h3' => [
                'margin-bottom' => '1rem',
                'font-size' => '1.2rem',
                'border-bottom' => '2px solid #667eea',
                'padding-bottom' => '0.5rem',
                'display' => 'inline-block',
            ],

            '& ul' => [
                'list-style' => 'none',
                'padding' => '0',

                '& li' => [
                    'margin-bottom' => '0.5rem',

                    '& a' => [
                        'color' => '#bdc3c7',
                        'text-decoration' => 'none',
                        'transition' => 'color 0.3s ease',

                        '&:hover' => [
                            'color' => '#667eea',
                            'padding-left' => '5px',
                        ],
                    ],
                ],
            ],
        ],
    ],

    '& .footer-bottom' => [
        'text-align' => 'center',
        'padding-top' => '2rem',
        'border-top' => '1px solid rgba(255,255,255,0.1)',
        'color' => '#95a5a6',
    ],
]);

// ============================================================================
// BUILD THE PAGE
// ============================================================================

$page = _html('lang="en"', [
    _head([
        _meta(['charset' => 'UTF-8']),
        _meta(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0']),
        _title(['IceDOM - Complex Styled Components Demo']),
        _styles(), // Output all collected CSS
        _style([_safe(<<<'CSS'
            * { box-sizing: border-box; }
            body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
            @keyframes pulse {
                0%, 100% { opacity: 0.5; transform: scale(1); }
                50% { opacity: 0.8; transform: scale(1.05); }
            }
        CSS)]),
    ]),

    _body([
        // Navigation
        $StyledNav(['class' => 'main-nav'], [
            _div(['class' => 'nav-container'], [
                _a(['href' => '#', 'class' => 'logo'], ['IceDOM']),
                _ul(['class' => 'nav-links'], [
                    _li([_a(['href' => '#features'], ['Features'])]),
                    _li([_a(['href' => '#stats'], ['Stats'])]),
                    _li([_a(['href' => '#demo'], ['Demo'])]),
                    _li([_a(['href' => '#contact'], ['Contact'])]),
                ]),
            ]),
        ]),

        // Hero Section
        $StyledHero(['class' => 'hero'], [
            _div(['class' => 'hero-content'], [
                _h1(['Styled Components in IceDOM']),
                _p(['Build beautiful, responsive components with scoped CSS. Features deep nesting, media queries, pseudo-elements, and more!']),
                _button(['class' => 'cta-button'], ['Get Started →']),
            ]),
        ]),

        // Stats Section
        $StyledStats(['id' => 'stats'], [
            _div(['class' => 'stats-container'], [
                _div(['class' => 'stat-item'], [
                    _div(['class' => 'stat-number'], ['560+']),
                    _div(['class' => 'stat-label'], ['Tests Passing']),
                ]),
                _div(['class' => 'stat-item'], [
                    _div(['class' => 'stat-number'], ['100%']),
                    _div(['class' => 'stat-label'], ['Type Safe']),
                ]),
                _div(['class' => 'stat-item'], [
                    _div(['class' => 'stat-number'], ['0']),
                    _div(['class' => 'stat-label'], ['Dependencies']),
                ]),
                _div(['class' => 'stat-item'], [
                    _div(['class' => 'stat-number'], ['∞']),
                    _div(['class' => 'stat-label'], ['Possibilities']),
                ]),
            ]),
        ]),

        // Features Grid
        $StyledGrid(['id' => 'features'], [
            _h2(['class' => 'section-title'], ['Powerful Features']),
            _div(['class' => 'grid'], [
                $StyledCard([
                    _div(['class' => 'card-icon'], ['🎨']),
                    _div(['class' => 'card-title'], [_span(['Scoped CSS'])]),
                    _div(['class' => 'card-description'], [
                        'Automatic scope generation prevents style conflicts. Each component gets a unique class like ',
                        _strong(['.c-a3f8b9c4']),
                        ' - no manual naming needed!',
                    ]),
                    _div(['class' => 'card-footer'], [
                        _span(['class' => 'badge badge-new'], ['New']),
                    ]),
                ]),

                $StyledCard([
                    _div(['class' => 'card-icon'], ['📱']),
                    _div(['class' => 'card-title'], [_span(['Media Queries'])]),
                    _div(['class' => 'card-description'], [
                        'Built-in support for ',
                        _strong(['@media queries']),
                        '. Write responsive components with ease. Mobile-first or desktop-first - your choice!',
                    ]),
                    _div(['class' => 'card-footer'], [
                        _span(['class' => 'badge badge-hot'], ['Hot']),
                    ]),
                ]),

                $StyledCard([
                    _div(['class' => 'card-icon'], ['🎯']),
                    _div(['class' => 'card-title'], [_span(['Deep Nesting'])]),
                    _div(['class' => 'card-description'], [
                        'SCSS-like nesting with ',
                        _strong(['& parent selector']),
                        '. Supports unlimited nesting levels, pseudo-classes, and pseudo-elements!',
                    ]),
                    _div(['class' => 'card-footer'], [
                        _span(['class' => 'badge badge-pro'], ['Pro']),
                    ]),
                ]),

                $StyledCard([
                    _div(['class' => 'card-icon'], ['⚡']),
                    _div(['class' => 'card-title'], [_span(['Performance'])]),
                    _div(['class' => 'card-description'], [
                        'Automatic deduplication and optional minification. Single stylesheet output for optimal loading times!',
                    ]),
                    _div(['class' => 'card-footer'], [
                        _span(['class' => 'badge badge-new'], ['Fast']),
                    ]),
                ]),

                $StyledCard([
                    _div(['class' => 'card-icon'], ['🔧']),
                    _div(['class' => 'card-title'], [_span(['Pure PHP'])]),
                    _div(['class' => 'card-description'], [
                        'Zero dependencies, no build step required. Just PHP 8.3+ and you\'re ready to go!',
                    ]),
                    _div(['class' => 'card-footer'], [
                        _span(['class' => 'badge badge-pro'], ['Simple']),
                    ]),
                ]),

                $StyledCard([
                    _div(['class' => 'card-icon'], ['✅']),
                    _div(['class' => 'card-title'], [_span(['Well Tested'])]),
                    _div(['class' => 'card-description'], [
                        'Over ',
                        _strong(['560 tests']),
                        ' ensure reliability. Every feature is thoroughly tested and documented!',
                    ]),
                    _div(['class' => 'card-footer'], [
                        _span(['class' => 'badge badge-hot'], ['Solid']),
                    ]),
                ]),
            ]),
        ]),

        // Footer
        $StyledFooter([
            _div(['class' => 'footer-content'], [
                _div(['class' => 'footer-section'], [
                    _h3(['Product']),
                    _ul([
                        _li([_a(['href' => '#'], ['Features'])]),
                        _li([_a(['href' => '#'], ['Documentation'])]),
                        _li([_a(['href' => '#'], ['Examples'])]),
                        _li([_a(['href' => '#'], ['Changelog'])]),
                    ]),
                ]),
                _div(['class' => 'footer-section'], [
                    _h3(['Resources']),
                    _ul([
                        _li([_a(['href' => '#'], ['Getting Started'])]),
                        _li([_a(['href' => '#'], ['API Reference'])]),
                        _li([_a(['href' => '#'], ['Best Practices'])]),
                        _li([_a(['href' => '#'], ['Migration Guide'])]),
                    ]),
                ]),
                _div(['class' => 'footer-section'], [
                    _h3(['Community']),
                    _ul([
                        _li([_a(['href' => '#'], ['GitHub'])]),
                        _li([_a(['href' => '#'], ['Discord'])]),
                        _li([_a(['href' => '#'], ['Twitter'])]),
                        _li([_a(['href' => '#'], ['Stack Overflow'])]),
                    ]),
                ]),
                _div(['class' => 'footer-section'], [
                    _h3(['Company']),
                    _ul([
                        _li([_a(['href' => '#'], ['About'])]),
                        _li([_a(['href' => '#'], ['Blog'])]),
                        _li([_a(['href' => '#'], ['Careers'])]),
                        _li([_a(['href' => '#'], ['Contact'])]),
                    ]),
                ]),
            ]),
            _div(['class' => 'footer-bottom'], [
                '© 2024 IceDOM. Made with ❤️ using Pure PHP.',
            ]),
        ]),
    ]),
]);

echo $page;
