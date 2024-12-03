<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    // The prefix configuration. If a non null value will be used, a random prefix will be generated.
    'prefix' => 'Mollie\\Vendor',

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->exclude([
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
                'console',
            ])
            ->in('vendor/league'),
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->exclude([
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
                'console',
            ])
            ->in('vendor/psr'),
        Finder::create()->append([
            'composer.json',
        ]),
    ],
];
