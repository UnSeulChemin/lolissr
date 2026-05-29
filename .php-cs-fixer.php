<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        'declare_strict_types' => true,

        'array_syntax' => [
            'syntax' => 'short',
        ],

        'ordered_imports' => true,
        'no_unused_imports' => true,

        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],

        'single_quote' => true,

        'concat_space' => [
            'spacing' => 'one',
        ],

        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'arguments',
                'parameters',
            ],
        ],

        'braces_position' => [
            'control_structures_opening_brace'
                => 'next_line_unless_newline_at_signature_end',

            'functions_opening_brace'
                => 'next_line_unless_newline_at_signature_end',

            'classes_opening_brace'
                => 'next_line_unless_newline_at_signature_end',
        ],

        'control_structure_braces' => true,

        'no_multiple_statements_per_line' => true,
        'no_extra_blank_lines' => true,
    ])
    ->setFinder($finder);