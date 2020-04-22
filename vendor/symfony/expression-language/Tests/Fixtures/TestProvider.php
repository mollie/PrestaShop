<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Tests\Fixtures;

use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ExpressionFunction;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ExpressionPhpFunction;
class TestProvider implements \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ExpressionFunction('identity', function ($input) {
            return $input;
        }, function (array $values, $input) {
            return $input;
        }), \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ExpressionFunction::fromPhp('strtoupper'), \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ExpressionFunction::fromPhp('\\strtolower'), \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ExpressionFunction::fromPhp('_PhpScoper5ea00cc67502b\\Symfony\\Component\\ExpressionLanguage\\Tests\\Fixtures\\fn_namespaced', 'fn_namespaced')];
    }
}
function fn_namespaced()
{
    return \true;
}
