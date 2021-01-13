<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace MolliePrefix\Composer\Semver;

use MolliePrefix\Composer\Semver\Constraint\Constraint;
use MolliePrefix\Composer\Semver\Constraint\ConstraintInterface;
/**
 * Helper class to evaluate constraint by compiling and reusing the code to evaluate
 */
class CompilingMatcher
{
    private static $compiledCheckerCache = array();
    private static $enabled;
    /**
     * @phpstan-var array<Constraint::OP_*, string>
     */
    private static $transOpInt = array(\MolliePrefix\Composer\Semver\Constraint\Constraint::OP_EQ => '==', \MolliePrefix\Composer\Semver\Constraint\Constraint::OP_LT => '<', \MolliePrefix\Composer\Semver\Constraint\Constraint::OP_LE => '<=', \MolliePrefix\Composer\Semver\Constraint\Constraint::OP_GT => '>', \MolliePrefix\Composer\Semver\Constraint\Constraint::OP_GE => '>=', \MolliePrefix\Composer\Semver\Constraint\Constraint::OP_NE => '!=');
    /**
     * Evaluates the expression: $constraint match $operator $version
     *
     * @param ConstraintInterface $constraint
     * @param int                 $operator
     * @phpstan-param Constraint::OP_*  $operator
     * @param string              $version
     *
     * @return mixed
     */
    public static function match(\MolliePrefix\Composer\Semver\Constraint\ConstraintInterface $constraint, $operator, $version)
    {
        if (self::$enabled === null) {
            self::$enabled = !\in_array('eval', \explode(',', \ini_get('disable_functions')), \true);
        }
        if (!self::$enabled) {
            return $constraint->matches(new \MolliePrefix\Composer\Semver\Constraint\Constraint(self::$transOpInt[$operator], $version));
        }
        $cacheKey = $operator . $constraint;
        if (!isset(self::$compiledCheckerCache[$cacheKey])) {
            $code = $constraint->compile($operator);
            self::$compiledCheckerCache[$cacheKey] = $function = eval('return function($v, $b){return ' . $code . ';};');
        } else {
            $function = self::$compiledCheckerCache[$cacheKey];
        }
        return $function($version, \strpos($version, 'dev-') === 0);
    }
}
