<?php

$finder = PhpCsFixer\Finder::create()
	->exclude(array('.git', 'deploy', 'bin', 'mails', 'translations', 'vendor', 'views', 'vendorBuilder'))
	->in(__DIR__);

$rules = [
        '@Symfony' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'cast_spaces' => [
            'space' => 'single',
        ],
        'error_suppression' => [
            'mute_deprecation_error' => false,
            'noise_remaining_usages' => false,
            'noise_remaining_usages_exclude' => [],
        ],
        'function_to_constant' => false,
        'no_alias_functions' => false,
        'non_printable_character' => false,
        'phpdoc_summary' => false,
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'protected_to_private' => false,
        'psr4' => false,
        'self_accessor' => false,
        'yoda_style' => null,
        'non_printable_character' => true,
        'no_superfluous_phpdoc_tags' => false,
    ];

return PhpCsFixer\Config::create()
	->setIndent("\t")
	->setRules($rules)
	->setFinder($finder);
