<?php

namespace MolliePrefix\PhpParser\Node\Scalar\MagicConst;

use MolliePrefix\PhpParser\Node\Scalar\MagicConst;
class Line extends \MolliePrefix\PhpParser\Node\Scalar\MagicConst
{
    public function getName()
    {
        return '__LINE__';
    }
}
