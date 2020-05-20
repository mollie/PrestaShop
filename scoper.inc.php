<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

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
            $curlPath = dirname(__FILE__).'/pre-scoper/vendor/php-curl-class/php-curl-class/';
            if (Tools::substr($filePath, 0, strlen($curlPath)) === $curlPath) {
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

            $molliePath = dirname(__FILE__).'/pre-scoper/vendor/firstred/mollie-api-php/';
            if (Tools::substr($filePath, 0, strlen($molliePath)) === $molliePath) {
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
