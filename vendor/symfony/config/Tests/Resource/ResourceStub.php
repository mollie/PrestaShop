<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Resource;

use _PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
class ResourceStub implements \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\SelfCheckingResourceInterface
{
    private $fresh = \true;
    public function setFresh($isFresh)
    {
        $this->fresh = $isFresh;
    }
    public function __toString()
    {
        return 'stub';
    }
    public function isFresh($timestamp)
    {
        return $this->fresh;
    }
}
