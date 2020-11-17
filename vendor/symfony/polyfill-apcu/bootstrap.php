<?php

namespace MolliePrefix;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use MolliePrefix\Symfony\Polyfill\Apcu as p;
if (!\extension_loaded('apc') && !\extension_loaded('apcu')) {
    return;
}
if (\extension_loaded('Zend Data Cache')) {
    if (!\function_exists('MolliePrefix\\apcu_add')) {
        function apcu_add($key, $var = null, $ttl = 0)
        {
            return \MolliePrefix\Symfony\Polyfill\Apcu\Apcu::apcu_add($key, $var, $ttl);
        }
    }
    if (!\function_exists('MolliePrefix\\apcu_delete')) {
        function apcu_delete($key)
        {
            return \MolliePrefix\Symfony\Polyfill\Apcu\Apcu::apcu_delete($key);
        }
    }
    if (!\function_exists('MolliePrefix\\apcu_exists')) {
        function apcu_exists($keys)
        {
            return \MolliePrefix\Symfony\Polyfill\Apcu\Apcu::apcu_exists($keys);
        }
    }
    if (!\function_exists('MolliePrefix\\apcu_fetch')) {
        function apcu_fetch($key, &$success = null)
        {
            return \MolliePrefix\Symfony\Polyfill\Apcu\Apcu::apcu_fetch($key, $success);
        }
    }
    if (!\function_exists('MolliePrefix\\apcu_store')) {
        function apcu_store($key, $var = null, $ttl = 0)
        {
            return \MolliePrefix\Symfony\Polyfill\Apcu\Apcu::apcu_store($key, $var, $ttl);
        }
    }
} else {
    if (!\function_exists('MolliePrefix\\apcu_add')) {
        function apcu_add($key, $var = null, $ttl = 0)
        {
            return \MolliePrefix\apc_add($key, $var, $ttl);
        }
    }
    if (!\function_exists('MolliePrefix\\apcu_delete')) {
        function apcu_delete($key)
        {
            return \MolliePrefix\apc_delete($key);
        }
    }
    if (!\function_exists('MolliePrefix\\apcu_exists')) {
        function apcu_exists($keys)
        {
            return \MolliePrefix\apc_exists($keys);
        }
    }
    if (!\function_exists('MolliePrefix\\apcu_fetch')) {
        function apcu_fetch($key, &$success = null)
        {
            return \MolliePrefix\apc_fetch($key, $success);
        }
    }
    if (!\function_exists('MolliePrefix\\apcu_store')) {
        function apcu_store($key, $var = null, $ttl = 0)
        {
            return \MolliePrefix\apc_store($key, $var, $ttl);
        }
    }
}
if (!\function_exists('MolliePrefix\\apcu_cache_info')) {
    function apcu_cache_info($limited = \false)
    {
        return \MolliePrefix\apc_cache_info('user', $limited);
    }
}
if (!\function_exists('MolliePrefix\\apcu_cas')) {
    function apcu_cas($key, $old, $new)
    {
        return \MolliePrefix\apc_cas($key, $old, $new);
    }
}
if (!\function_exists('MolliePrefix\\apcu_clear_cache')) {
    function apcu_clear_cache()
    {
        return \MolliePrefix\apc_clear_cache('user');
    }
}
if (!\function_exists('MolliePrefix\\apcu_dec')) {
    function apcu_dec($key, $step = 1, &$success = \false)
    {
        return \MolliePrefix\apc_dec($key, $step, $success);
    }
}
if (!\function_exists('MolliePrefix\\apcu_inc')) {
    function apcu_inc($key, $step = 1, &$success = \false)
    {
        return \MolliePrefix\apc_inc($key, $step, $success);
    }
}
if (!\function_exists('MolliePrefix\\apcu_sma_info')) {
    function apcu_sma_info($limited = \false)
    {
        return \MolliePrefix\apc_sma_info($limited);
    }
}
if (!\class_exists('MolliePrefix\\APCuIterator', \false) && \class_exists('MolliePrefix\\APCIterator', \false)) {
    class APCuIterator extends \MolliePrefix\APCIterator
    {
        public function __construct($search = null, $format = \APC_ITER_ALL, $chunk_size = 100, $list = \APC_LIST_ACTIVE)
        {
            parent::__construct('user', $search, $format, $chunk_size, $list);
        }
    }
}
