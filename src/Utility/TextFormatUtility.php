<?php

namespace Mollie\Utility;

class TextFormatUtility
{
    public static function formatNumber($unitPrice, $apiRoundingPrecision, $docPoint = '.', $thousandSep = '')
    {
        return number_format($unitPrice, $apiRoundingPrecision, $docPoint, $thousandSep);
    }

}