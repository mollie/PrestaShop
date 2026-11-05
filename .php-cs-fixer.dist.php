<?php

// Trailing commas in parameter lists are PHP 8.0 syntax and the module runs on PHP 7.2.
$config = new class() extends PrestaShop\CodingStandards\CsFixer\Config {
    public function getRules(): array
    {
        return array_merge(parent::getRules(), [
            'trailing_comma_in_multiline' => ['elements' => ['arrays', 'array_destructuring']],
        ]);
    }
};

$config
    ->setUsingCache(true)
    ->getFinder()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('node_modules')
    ->exclude('tools');

return $config;
