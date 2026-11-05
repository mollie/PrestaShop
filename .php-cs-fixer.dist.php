<?php

// Trailing commas in parameter lists are PHP 8.0 syntax and the module runs on PHP 7.2.
// The Addons validator license check rejects a blank line between <?php and the license header.
$config = new class() extends PrestaShop\CodingStandards\CsFixer\Config {
    public function getRules(): array
    {
        return array_merge(parent::getRules(), [
            'trailing_comma_in_multiline' => ['elements' => ['arrays', 'array_destructuring']],
            'blank_line_after_opening_tag' => false,
        ]);
    }
};

$config
    ->getFinder()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('node_modules')
    ->exclude('tools');

return $config;
