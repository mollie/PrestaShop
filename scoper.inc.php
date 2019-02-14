<?php
// Not available in Mollie's composer, but part of the php-scoper.phar package, you can safely ignore this missing import
use Isolated\Symfony\Component\Finder\Finder;

return array(
    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => array(
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->exclude(array(
                'doc',
                'vendor-bin',
            ))
            ->in('pre-scoper'),
        Finder::create()->append(array(
            'composer.json',
        )),
    ),
    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper#patchers
    'patchers' => array(
        function ($filePath, $prefix, $content) {
            // Change the content here. vendor = pre-scoper at this time
            $curlPath = __DIR__.'/pre-scoper/vendor/php-curl-class/php-curl-class/';
            if (substr($filePath, 0, strlen($curlPath)) === $curlPath) {
                $content = preg_replace(
                    '~'.preg_quote("'\\\\Curl\\\\", '~').'~',
                    "'\\\\\\\\$prefix\\\\\\\\Curl\\\\\\\\",
                    $content
                );
                $content = preg_replace(
                    '~'.preg_quote("\"\\\\Curl\\\\", '~').'~',
                    "\"\\\\\\\\$prefix\\\\\\\\Curl\\\\\\\\",
                    $content
                );
            }

            $molliePath = __DIR__.'/pre-scoper/vendor/firstred/mollie-api-php/';
            if (substr($filePath, 0, strlen($molliePath )) === $molliePath ) {
                $content = preg_replace(
                    '~'.preg_quote("'Mollie\\\\Api\\\\", '~').'~',
                    "'$prefix\\\\\\\\Mollie\\\\\\\\Api\\\\\\\\",
                    $content
                );
                $content = preg_replace(
                    '~'.preg_quote("\"Mollie\\\\Api\\\\", '~').'~',
                    "\"$prefix\\\\\\\\Mollie\\\\\\\\Api\\\\\\\\",
                    $content
                );
                $content = preg_replace(
                    '~'.preg_quote("'\\\\Mollie\\\\Api\\\\", '~').'~',
                    "'\\\\\\\\$prefix\\\\\\\\Mollie\\\\\\\\Api\\\\\\\\",
                    $content
                );
                $content = preg_replace(
                    '~'.preg_quote("\"\\\\\Mollie\\\\Api\\\\", '~').'~',
                    "\"\\\\\\\\$prefix\\\\\\\\Mollie\\\\\\\\Api\\\\\\\\",
                    $content
                );
            }

            return $content;
        },
    ),
);
