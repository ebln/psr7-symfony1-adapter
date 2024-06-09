<?php

declare(strict_types=1);

/*
 * @see https://mlocati.github.io/php-cs-fixer-configurator/#version:3.58
 * @see https://cs.symfony.com/doc/rules/index.html
 * @see https://cs.symfony.com/doc/ruleSets/index.html
 */

$finder = \PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('mock')
    ->exclude('DNC')
    ->notPath('phpstan-baseline.php')
    ->in(__DIR__);

$config = new \PhpCsFixer\Config();
$config->setRiskyAllowed(true)
    ->setRules(
        [
            '@PER-CS2.0'                                       => true,
            '@Symfony'                                         => true,
            '@PhpCsFixer'                                      => true,
            '@PhpCsFixer:risky'                                => true,
            '@PHPUnit100Migration:risky'                       => true,
            // '@PHP80Migration:risky'                            => true,
            '@PHP82Migration'                                  => true,
            'no_superfluous_phpdoc_tags'                       => true,
            'native_function_invocation'                       => false,
            'concat_space'                                     => ['spacing' => 'one'],
            'phpdoc_types_order'                               => ['null_adjustment' => 'always_first', 'sort_algorithm' => 'alpha'],
            'single_line_comment_style'                        => ['comment_types' => [ /* 'hash' */],],
            'phpdoc_summary'                                   => false,
            'cast_spaces'                                      => ['space' => 'none'],
            'binary_operator_spaces'                           => ['default' => 'at_least_single_space', 'operators' => ['=' => 'align_single_space_minimal', '=>' => 'align_single_space_minimal_by_scope']],
            'no_unused_imports'                                => true,
            'ordered_imports'                                  => ['sort_algorithm' => 'alpha', 'imports_order' => ['const', 'class', 'function']],
            'control_structure_braces'                         => true,
            'control_structure_continuation_position'          => true,
            'date_time_create_from_format_call'                => true,
            'date_time_immutable'                              => true,
            'nullable_type_declaration_for_default_null_value' => true,
            'phpdoc_line_span'                                 => ['const' => 'single', 'method' => 'single', 'property' => 'single'],
            'simplified_null_return'                           => true,
            'statement_indentation'                            => true,
            'blank_line_before_statement'                      => ['statements' => ['continue', 'declare', 'default', 'exit', 'goto', 'include', 'include_once', 'require', 'require_once', 'return', 'switch']],
            'yoda_style'                                       => ['less_and_greater' => false],
            'type_declaration_spaces'                          => ['elements' => ['function']],
            'fully_qualified_strict_types'                     => false,
            'single_space_around_construct'                    => [
                'constructs_followed_by_a_single_space' => [ // default, minus const
                                                             'abstract',
                                                             'as',
                                                             'attribute',
                                                             'break',
                                                             'case',
                                                             'catch',
                                                             'class',
                                                             'clone',
                                                             'comment',
                                                             'const_import',
                                                             'continue',
                                                             'do',
                                                             'echo',
                                                             'else',
                                                             'elseif',
                                                             'enum',
                                                             'extends',
                                                             'final',
                                                             'finally',
                                                             'for',
                                                             'foreach',
                                                             'function',
                                                             'function_import',
                                                             'global',
                                                             'goto',
                                                             'if',
                                                             'implements',
                                                             'include',
                                                             'include_once',
                                                             'instanceof',
                                                             'insteadof',
                                                             'interface',
                                                             'match',
                                                             'named_argument',
                                                             'namespace',
                                                             'new',
                                                             'open_tag_with_echo',
                                                             'php_doc',
                                                             'php_open',
                                                             'print',
                                                             'private',
                                                             'protected',
                                                             'public',
                                                             'readonly',
                                                             'require',
                                                             'require_once',
                                                             'return',
                                                             'static',
                                                             'switch',
                                                             'throw',
                                                             'trait',
                                                             'try',
                                                             'type_colon',
                                                             'use',
                                                             'use_lambda',
                                                             'use_trait',
                                                             'var',
                                                             'while',
                                                             'yield',
                                                             'yield_from',
                ],
            ],
            'void_return'                                      => true,
        ]
    )
    ->setFinder($finder)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());

if (false) {
    $resolver = new \PhpCsFixer\Console\ConfigurationResolver($config, [], '', new \PhpCsFixer\ToolInfo());
    echo "\n\n# DUMPING EFFECTIVE RULES #################\n";
    $rules = $resolver->getRules();
    ksort($rules);
    /** @noinspection ForgottenDebugOutputInspection */
    var_export($rules);
    echo "\n\n###########################################\n";

    die();
}

return $config;
