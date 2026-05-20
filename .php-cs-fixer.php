<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,

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

        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'arguments',
                'parameters',
            ],
        ],

        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],

        'braces_position' => [
            'control_structures_opening_brace' => 'same_line',
            'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],

        'control_structure_braces' => true,

        'no_multiple_statements_per_line' => true,
    ])
    ->setFinder($finder);