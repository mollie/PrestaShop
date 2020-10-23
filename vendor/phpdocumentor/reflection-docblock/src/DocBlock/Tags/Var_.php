<?php

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2015 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
namespace MolliePrefix\phpDocumentor\Reflection\DocBlock\Tags;

use MolliePrefix\phpDocumentor\Reflection\DocBlock\Description;
use MolliePrefix\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use MolliePrefix\phpDocumentor\Reflection\Type;
use MolliePrefix\phpDocumentor\Reflection\TypeResolver;
use MolliePrefix\phpDocumentor\Reflection\Types\Context as TypeContext;
use MolliePrefix\Webmozart\Assert\Assert;
/**
 * Reflection class for a {@}var tag in a Docblock.
 */
class Var_ extends \MolliePrefix\phpDocumentor\Reflection\DocBlock\Tags\BaseTag implements \MolliePrefix\phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod
{
    /** @var string */
    protected $name = 'var';
    /** @var Type */
    private $type;
    /** @var string */
    protected $variableName = '';
    /**
     * @param string      $variableName
     * @param Type        $type
     * @param Description $description
     */
    public function __construct($variableName, \MolliePrefix\phpDocumentor\Reflection\Type $type = null, \MolliePrefix\phpDocumentor\Reflection\DocBlock\Description $description = null)
    {
        \MolliePrefix\Webmozart\Assert\Assert::string($variableName);
        $this->variableName = $variableName;
        $this->type = $type;
        $this->description = $description;
    }
    /**
     * {@inheritdoc}
     */
    public static function create($body, \MolliePrefix\phpDocumentor\Reflection\TypeResolver $typeResolver = null, \MolliePrefix\phpDocumentor\Reflection\DocBlock\DescriptionFactory $descriptionFactory = null, \MolliePrefix\phpDocumentor\Reflection\Types\Context $context = null)
    {
        \MolliePrefix\Webmozart\Assert\Assert::stringNotEmpty($body);
        \MolliePrefix\Webmozart\Assert\Assert::allNotNull([$typeResolver, $descriptionFactory]);
        $parts = \preg_split('/(\\s+)/Su', $body, 3, \PREG_SPLIT_DELIM_CAPTURE);
        $type = null;
        $variableName = '';
        // if the first item that is encountered is not a variable; it is a type
        if (isset($parts[0]) && \strlen($parts[0]) > 0 && $parts[0][0] !== '$') {
            $type = $typeResolver->resolve(\array_shift($parts), $context);
            \array_shift($parts);
        }
        // if the next item starts with a $ or ...$ it must be the variable name
        if (isset($parts[0]) && \strlen($parts[0]) > 0 && $parts[0][0] == '$') {
            $variableName = \array_shift($parts);
            \array_shift($parts);
            if (\substr($variableName, 0, 1) === '$') {
                $variableName = \substr($variableName, 1);
            }
        }
        $description = $descriptionFactory->create(\implode('', $parts), $context);
        return new static($variableName, $type, $description);
    }
    /**
     * Returns the variable's name.
     *
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }
    /**
     * Returns the variable's type or null if unknown.
     *
     * @return Type|null
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * Returns a string representation for this tag.
     *
     * @return string
     */
    public function __toString()
    {
        return ($this->type ? $this->type . ' ' : '') . (empty($this->variableName) ? null : '$' . $this->variableName) . ($this->description ? ' ' . $this->description : '');
    }
}
