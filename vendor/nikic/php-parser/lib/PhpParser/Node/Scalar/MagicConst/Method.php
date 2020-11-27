<?php

namespace MolliePrefix\PhpParser\Node\Scalar\MagicConst;

use MolliePrefix\PhpParser\Node\Scalar\MagicConst;
class Method extends \MolliePrefix\PhpParser\Node\Scalar\MagicConst
{
    public function getName()
    {
        return '__METHOD__';
    }
}
