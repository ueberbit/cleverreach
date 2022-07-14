<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

$finder = Finder::create()
    ->name('/\\.php$/')
    ->in(__DIR__ . '/Classes')
    ->in(__DIR__ . '/Configuration')
    ->in(__DIR__ . '/Tests');

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@DoctrineAnnotation'       => true,
        '@PSR12'                    => true,
        '@PHP81Migration'           => true,
        '@PHP80Migration:risky'     => true,
        '@PHPUnit84Migration:risky' => true,
        'align_multiline_comment'   => [
            'comment_type' => 'phpdocs_like',
        ],
        'binary_operator_spaces' => [
            'default'   => 'single_space',
            'operators' => [
                '=>' => 'align_single_space_minimal',
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => ['if', 'try', 'return'],
        ],
        'declare_strict_types'                 => true,
        'doctrine_annotation_array_assignment' => [
            'operator' => '=',
        ],
        'ereg_to_preg'                => true,
        'escape_implicit_backslashes' => [
            'double_quoted'  => true,
            'heredoc_syntax' => true,
            'single_quoted'  => true,
        ],
        'explicit_indirect_variable'  => true,
        'explicit_string_variable'    => true,
        'linebreak_after_opening_tag' => true,
        'magic_constant_casing'       => true,
        'no_empty_comment'            => true,
        'no_mixed_echo_print'         => [
            'use' => 'echo',
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_php4_constructor'                         => true,
        'no_spaces_after_function_name'               => true,
        'no_spaces_around_offset'                     => [
            'positions' => ['inside', 'outside'],
        ],
        'no_spaces_inside_parenthesis'               => true,
        'no_useless_return'                          => true,
        'non_printable_character'                    => false,
        'ordered_class_elements'                     => true,
        'phpdoc_indent'                              => true,
        'short_scalar_cast'                          => true,
        'standardize_not_equals'                     => true,
        'visibility_required'                        => true,
        'ternary_operator_spaces'                    => true,
        'array_syntax'                               => ['syntax' => 'short'],
        'blank_line_after_opening_tag'               => true,
        'braces'                                     => ['allow_single_line_closure' => true],
        'cast_spaces'                                => ['space' => 'none'],
        'compact_nullable_typehint'                  => true,
        'concat_space'                               => ['spacing' => 'one'],
        'declare_equal_normalize'                    => ['space' => 'none'],
        'dir_constant'                               => true,
        'function_to_constant'                       => ['functions' => ['get_called_class', 'get_class', 'get_class_this', 'php_sapi_name', 'phpversion', 'pi']],
        'function_typehint_space'                    => true,
        'lowercase_cast'                             => true,
        'method_argument_space'                      => ['on_multiline' => 'ensure_fully_multiline'],
        'modernize_strpos'                           => true,
        'modernize_types_casting'                    => true,
        'native_function_casing'                     => true,
        'new_with_braces'                            => true,
        'no_alias_functions'                         => true,
        'no_blank_lines_after_phpdoc'                => true,
        'no_empty_phpdoc'                            => true,
        'no_empty_statement'                         => true,
        'no_extra_blank_lines'                       => true,
        'no_leading_import_slash'                    => true,
        'no_leading_namespace_whitespace'            => true,
        'no_null_property_initialization'            => true,
        'no_short_bool_cast'                         => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_superfluous_elseif'                      => true,
        'no_trailing_comma_in_singleline_array'      => true,
        'no_unneeded_control_parentheses'            => true,
        'no_unused_imports'                          => true,
        'no_useless_else'                            => true,
        'no_whitespace_in_blank_line'                => true,
        'ordered_imports'                            => true,
        'php_unit_construct'                         => ['assertions' => ['assertEquals', 'assertSame', 'assertNotEquals', 'assertNotSame']],
        'php_unit_mock_short_will_return'            => true,
        'php_unit_test_case_static_method_calls'     => ['call_type' => 'self'],
        'phpdoc_no_access'                           => true,
        'phpdoc_no_empty_return'                     => true,
        'phpdoc_no_package'                          => true,
        'phpdoc_scalar'                              => true,
        'phpdoc_trim'                                => true,
        'phpdoc_types'                               => true,
        'phpdoc_types_order'                         => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'return_type_declaration'                    => ['space_before' => 'none'],
        'single_quote'                               => true,
        'single_line_comment_style'                  => ['comment_types' => ['hash']],
        'single_trait_insert_per_statement'          => true,
        'trailing_comma_in_multiline'                => ['elements' => ['arrays']],
        'whitespace_after_comma_in_array'            => true,
        'yoda_style'                                 => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
    ])
    ->setFinder($finder);
