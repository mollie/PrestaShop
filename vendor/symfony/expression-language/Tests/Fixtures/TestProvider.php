<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Fixtures;

use MolliePrefix\Symfony\Component\ExpressionLanguage\ExpressionFunction;
use MolliePrefix\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use MolliePrefix\Symfony\Component\ExpressionLanguage\ExpressionPhpFunction;
class TestProvider implements \MolliePrefix\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [new \MolliePrefix\Symfony\Component\ExpressionLanguage\ExpressionFunction('identity', function ($input) {
            return $input;
        }, function (array $values, $input) {
            return $input;
        }), \MolliePrefix\Symfony\Component\ExpressionLanguage\ExpressionFunction::fromPhp('strtoupper'), \MolliePrefix\Symfony\Component\ExpressionLanguage\ExpressionFunction::fromPhp('\\strtolower'), \MolliePrefix\Symfony\Component\ExpressionLanguage\ExpressionFunction::fromPhp('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\Tests\\Fixtures\\fn_namespaced', 'fn_namespaced')];
    }
}
function fn_namespaced()
{
    return \true;
}
