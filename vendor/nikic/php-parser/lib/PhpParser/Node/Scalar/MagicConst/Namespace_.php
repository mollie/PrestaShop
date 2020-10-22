<?php

namespace MolliePrefix\PhpParser\Node\Scalar\MagicConst;

use MolliePrefix\PhpParser\Node\Scalar\MagicConst;
class Namespace_ extends \MolliePrefix\PhpParser\Node\Scalar\MagicConst
{
    public function getName()
    {
        return '__NAMESPACE__';
    }
}
