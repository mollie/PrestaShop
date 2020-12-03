<?php

namespace MolliePrefix\PhpParser\Node\Scalar\MagicConst;

use MolliePrefix\PhpParser\Node\Scalar\MagicConst;
class Function_ extends \MolliePrefix\PhpParser\Node\Scalar\MagicConst
{
    public function getName()
    {
        return '__FUNCTION__';
    }
}
