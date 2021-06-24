<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('mock')
    ->exclude('DNC')
    ->exclude('tests')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
$config->setRiskyAllowed(true)
    ->setRules(
        [
            '@PSR2'                                  => true,
            '@Symfony'                               => true,
            '@PhpCsFixer'                            => true,
            '@PhpCsFixer:risky'                      => true,
            '@PHPUnit75Migration:risky'              => true,
            //'@PHP73Migration'                        => true,
            '@PHP71Migration:risky'                  => true,
            'php_unit_dedicate_assert'               => ['target' => '5.6'],
            'array_syntax'                           => ['syntax' => 'short'],
            'no_superfluous_phpdoc_tags'             => true,
            'native_function_invocation'             => false,
            'concat_space'                           => ['spacing' => 'one'],
            'phpdoc_types_order'                     => ['null_adjustment' => 'always_first', 'sort_algorithm' => 'alpha'],
            'single_line_comment_style'              => [
                'comment_types' => ['hash'],
            ],
            'binary_operator_spaces'                 => false,
            'phpdoc_summary'                         => false,
            'multiline_whitespace_before_semicolons' => false,
            'cast_spaces'                            => ['space' => 'none'],
            'comment_to_phpdoc'                      => false,
        ]
    )
    ->setFinder($finder);

return $config;
