<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
/**
 * A pass that might be run repeatedly.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class RepeatedPass implements \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @var bool
     */
    private $repeat = \false;
    private $passes;
    /**
     * @param RepeatablePassInterface[] $passes An array of RepeatablePassInterface objects
     *
     * @throws InvalidArgumentException when the passes don't implement RepeatablePassInterface
     */
    public function __construct(array $passes)
    {
        foreach ($passes as $pass) {
            if (!$pass instanceof \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\RepeatablePassInterface) {
                throw new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException('$passes must be an array of RepeatablePassInterface.');
            }
            $pass->setRepeatedPass($this);
        }
        $this->passes = $passes;
    }
    /**
     * Process the repeatable passes that run more than once.
     */
    public function process(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        do {
            $this->repeat = \false;
            foreach ($this->passes as $pass) {
                $pass->process($container);
            }
        } while ($this->repeat);
    }
    /**
     * Sets if the pass should repeat.
     */
    public function setRepeat()
    {
        $this->repeat = \true;
    }
    /**
     * Returns the passes.
     *
     * @return RepeatablePassInterface[] An array of RepeatablePassInterface objects
     */
    public function getPasses()
    {
        return $this->passes;
    }
}
