<?php

namespace Mollie\Utility;

class AssortUtility
{
    /**
     * 2D array sort by key
     *
     * @param mixed $array
     * @param mixed $key
     *
     * @since 3.3.0
     */
    public static function aasort(&$array, $key)
    {
        $sorter = [];
        $ret = [];
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
    }
}