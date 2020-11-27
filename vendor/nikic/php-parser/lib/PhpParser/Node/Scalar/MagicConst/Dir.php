<?php

namespace MolliePrefix\PhpParser\Node\Scalar\MagicConst;

use MolliePrefix\PhpParser\Node\Scalar\MagicConst;
class Dir extends \MolliePrefix\PhpParser\Node\Scalar\MagicConst
{
    public function getName()
    {
        return '__DIR__';
    }
}
