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
 * Reflection class for a {@}return tag in a Docblock.
 */
final class Return_ extends \MolliePrefix\phpDocumentor\Reflection\DocBlock\Tags\BaseTag implements \MolliePrefix\phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod
{
    protected $name = 'return';
    /** @var Type */
    private $type;
    public function __construct(\MolliePrefix\phpDocumentor\Reflection\Type $type, \MolliePrefix\phpDocumentor\Reflection\DocBlock\Description $description = null)
    {
        $this->type = $type;
        $this->description = $description;
    }
    /**
     * {@inheritdoc}
     */
    public static function create($body, \MolliePrefix\phpDocumentor\Reflection\TypeResolver $typeResolver = null, \MolliePrefix\phpDocumentor\Reflection\DocBlock\DescriptionFactory $descriptionFactory = null, \MolliePrefix\phpDocumentor\Reflection\Types\Context $context = null)
    {
        \MolliePrefix\Webmozart\Assert\Assert::string($body);
        \MolliePrefix\Webmozart\Assert\Assert::allNotNull([$typeResolver, $descriptionFactory]);
        $parts = \preg_split('/\\s+/Su', $body, 2);
        $type = $typeResolver->resolve(isset($parts[0]) ? $parts[0] : '', $context);
        $description = $descriptionFactory->create(isset($parts[1]) ? $parts[1] : '', $context);
        return new static($type, $description);
    }
    /**
     * Returns the type section of the variable.
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }
    public function __toString()
    {
        return $this->type . ' ' . $this->description;
    }
}
