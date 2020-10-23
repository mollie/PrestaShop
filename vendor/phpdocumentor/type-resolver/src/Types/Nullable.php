<?php

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2017 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
namespace MolliePrefix\phpDocumentor\Reflection\Types;

use MolliePrefix\phpDocumentor\Reflection\Type;
/**
 * Value Object representing a nullable type. The real type is wrapped.
 */
final class Nullable implements \MolliePrefix\phpDocumentor\Reflection\Type
{
    /**
     * @var Type
     */
    private $realType;
    /**
     * Initialises this nullable type using the real type embedded
     *
     * @param Type $realType
     */
    public function __construct(\MolliePrefix\phpDocumentor\Reflection\Type $realType)
    {
        $this->realType = $realType;
    }
    /**
     * Provide access to the actual type directly, if needed.
     *
     * @return Type
     */
    public function getActualType()
    {
        return $this->realType;
    }
    /**
     * Returns a rendered output of the Type as it would be used in a DocBlock.
     *
     * @return string
     */
    public function __toString()
    {
        return '?' . $this->realType->__toString();
    }
}
